<?php

namespace App\Livewire\Staff;

use Livewire\Component;
use App\Models\Client as ClientModel;
use App\Services\Tplink\ClientDiscoveryService;
use App\Models\AdminLog;
use Illuminate\Support\Facades\Auth;

class Client extends Component
{
    public $clients = []; // array of clients loaded from DB for UI
    public $paginatedClients = [];
    public $currentPage = 1;
    public $perPage = 15;

    public $search = '';
    public $showOnlyBlocked = false;

    public $editingClientId = null;
    public $editNextDueDate = null;
    public $editRepeaterName = null;

    protected $listeners = [
        'refreshClients' => 'loadClientsFromDb',
    ];

    public function mount(ClientDiscoveryService $discoveryService)
    {
        // On mount, fetch new clients from discovery API and merge into DB
        $this->fetchAndStoreClients($discoveryService);
        $this->loadClientsFromDb();
    }

    protected function fetchAndStoreClients(ClientDiscoveryService $discoveryService): void
    {
        $discovered = $discoveryService->fetchClients();

        foreach ($discovered as $client) {
            $mac = $client['mac_address'];
            $hostname = $client['hostname'] ?? 'Unknown';

            $existingClient = ClientModel::where('mac_address', $mac)->first();

            if ($existingClient) {
                $repeaterName = $existingClient->repeater_name;
                // Only update repeater_name if empty or 'Unknown'
                if (empty($repeaterName) || $repeaterName === 'Unknown') {
                    $repeaterName = $hostname;
                }

                $existingClient->update([
                    'ip_address' => $client['ip_address'] ?? 'Unknown',
                    'repeater_name' => $repeaterName,
                    'status' => $client['blocked'] ?? false ? 'blocked' : 'active',
                    // Preserve next_due_date if already set
                    'next_due_date' => $existingClient->next_due_date ?? now()->addDays(30),
                ]);
            } else {
                ClientModel::create([
                    'mac_address' => $mac,
                    'ip_address' => $client['ip_address'] ?? 'Unknown',
                    'repeater_name' => $hostname,
                    'status' => $client['blocked'] ?? false ? 'blocked' : 'active',
                    'next_due_date' => now()->addDays(30),
                ]);
            }
        }
    }

    public function loadClientsFromDb(): void
    {
        $query = ClientModel::query();

        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('repeater_name', 'like', $searchTerm)
                  ->orWhere('mac_address', 'like', $searchTerm);
            });
        }

        if ($this->showOnlyBlocked) {
            $query->where('status', 'blocked');
        }

        $allClients = $query->orderBy('repeater_name')->get();

        $this->clients = $allClients->map(function ($client) {
            return [
                'id' => $client->id,
                'mac_address' => $client->mac_address,
                'ip_address' => $client->ip_address,
                'hostname' => $client->repeater_name,
                'repeater_name' => $client->repeater_name,
                'blocked' => $client->status === 'blocked',
                'next_due_date' => $client->next_due_date ? $client->next_due_date->format('Y-m-d') : null,
                'next_due_formatted' => $client->next_due_date ? $client->next_due_date->format('Y-m-d') : '-',
            ];
        })->toArray();

        $this->currentPage = 1; // reset to first page on reload
        $this->paginateClients();
    }

    protected function paginateClients(): void
    {
        $offset = ($this->currentPage - 1) * $this->perPage;
        $this->paginatedClients = array_slice($this->clients, $offset, $this->perPage);
    }

    public function updatedSearch(): void
    {
        $this->loadClientsFromDb();
    }

    public function updatedShowOnlyBlocked(): void
    {
        $this->loadClientsFromDb();
    }

    public function gotoPage($page): void
    {
        $this->currentPage = (int)$page;
        $this->paginateClients();
    }

    // Editing
    public function editClient(int $clientId): void
    {
        $client = ClientModel::findOrFail($clientId);

        $this->editingClientId = $client->id;
        $this->editNextDueDate = $client->next_due_date ? $client->next_due_date->format('Y-m-d') : null;
        $this->editRepeaterName = $client->repeater_name;
    }

    public function cancelEdit(): void
    {
        $this->editingClientId = null;
        $this->editNextDueDate = null;
        $this->editRepeaterName = null;
    }

    public function saveEdit(): void
    {
        $client = ClientModel::findOrFail($this->editingClientId);

        $client->update([
            'next_due_date' => $this->editNextDueDate,
            'repeater_name' => $this->editRepeaterName,
        ]);

        AdminLog::create([
            'action' => 'edit',
            'mac_address' => $client->mac_address,
            'performed_by' => Auth::id(),
            'metadata' => [
                'next_due_date' => $this->editNextDueDate,
                'repeater_name' => $this->editRepeaterName,
            ],
        ]);

        $this->cancelEdit();
        $this->loadClientsFromDb();
    }

    // Blocking/Unblocking
    public function block(string $macAddress, ClientDiscoveryService $discoveryService): void
    {
        if ($discoveryService->blockClient($macAddress)) {
            // Update DB record status to 'blocked'
            $client = ClientModel::where('mac_address', $macAddress)->first();
            if ($client) {
                $client->status = 'blocked';
                $client->save();
            }

            session()->flash('success', "Client $macAddress blocked successfully.");
            AdminLog::create([
                'action' => 'block',
                'mac_address' => $macAddress,
                'performed_by' => Auth::id(),
            ]);

            // Update clients array immediately to reflect the change in UI
            foreach ($this->clients as &$client) {
                if ($client['mac_address'] === $macAddress) {
                    $client['blocked'] = true;
                    break;
                }
            }
            unset($client);

            $this->paginateClients();
        } else {
            session()->flash('error', "Failed to block client $macAddress.");
        }
    }

    public function unblock(string $macAddress, ClientDiscoveryService $discoveryService): void
    {
        if ($discoveryService->unblockClient($macAddress)) {
            // Update DB record status to 'active'
            $client = ClientModel::where('mac_address', $macAddress)->first();
            if ($client) {
                $client->status = 'active';
                $client->save();
            }

            session()->flash('success', "Client $macAddress unblocked successfully.");
            AdminLog::create([
                'action' => 'unblock',
                'mac_address' => $macAddress,
                'performed_by' => Auth::id(),
            ]);

            // Update clients array immediately
            foreach ($this->clients as &$client) {
                if ($client['mac_address'] === $macAddress) {
                    $client['blocked'] = false;
                    break;
                }
            }
            unset($client);

            $this->paginateClients();
        } else {
            session()->flash('error', "Failed to unblock client $macAddress.");
        }
    }

    public function render()
    {
        return view('livewire.staff.client', [
            'clients' => $this->paginatedClients,
            'currentPage' => $this->currentPage,
            'perPage' => $this->perPage,
            'total' => count($this->clients),
            'totalPages' => ceil(count($this->clients) / $this->perPage),
        ])->layout('layouts.app', ['title' => 'Admin Dashboard']);
    }
}
