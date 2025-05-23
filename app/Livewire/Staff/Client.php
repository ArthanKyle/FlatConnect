<?php

namespace App\Livewire\Staff;

use Livewire\Component;
use App\Models\Client as ClientModel;
use App\Services\Tplink\ClientDiscoveryService;
use App\Models\AdminLog;
use Illuminate\Support\Facades\Auth;

class Client extends Component
{
    public $clients = [];
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
                if (empty($repeaterName) || $repeaterName === 'Unknown') {
                    $repeaterName = $hostname;
                }

                $existingClient->update([
                    'ip_address' => $client['ip_address'] ?? 'Unknown',
                    'repeater_name' => $repeaterName,
                    'status' => $client['blocked'] ?? false ? 'blocked' : 'active',
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
                'next_due_formatted' => $client->status === 'blocked'
                    ? 'Until payment is fulfilled'
                    : ($client->next_due_date ? $client->next_due_date->format('Y-m-d') : '-'),
            ];
        })->toArray();

        $this->currentPage = 1;
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

        $originalRepeater = $client->repeater_name;
        $originalDueDate = optional($client->next_due_date)->format('Y-m-d');

        $client->update([
            'next_due_date' => $this->editNextDueDate,
            'repeater_name' => $this->editRepeaterName,
        ]);

        $details = "Edited client {$client->mac_address}: Repeater name '{$originalRepeater}' → '{$this->editRepeaterName}', Due date '{$originalDueDate}' → '{$this->editNextDueDate}'";

        AdminLog::create([
            'action' => 'edit',
            'mac_address' => $client->mac_address,
            'details' => $details,
        ]);

        $this->cancelEdit();
        $this->loadClientsFromDb();
    }

    public function block(string $macAddress, ClientDiscoveryService $discoveryService): void
    {
        if ($discoveryService->blockClient($macAddress)) {
            $client = ClientModel::where('mac_address', $macAddress)->first();
            if ($client) {
                $client->status = 'blocked';
                $client->save();

                AdminLog::create([
                    'action' => 'block',
                    'mac_address' => $macAddress,
                    'details' => "Blocked client {$macAddress}: Next due date suspended until payment is fulfilled",
                ]);
            }

            session()->flash('success', "Client $macAddress blocked successfully.");

            // Refresh client list to update UI properly
            $this->loadClientsFromDb();

        } else {
            session()->flash('error', "Failed to block client $macAddress.");
        }
    }

    public function unblock(string $macAddress, ClientDiscoveryService $discoveryService): void
    {
        if ($discoveryService->unblockClient($macAddress)) {
            $client = ClientModel::where('mac_address', $macAddress)->first();
            if ($client) {
                $client->status = 'active';
                $client->next_due_date = now()->addDays(30);
                $client->save();

                AdminLog::create([
                    'action' => 'unblock',
                    'mac_address' => $macAddress,
                    'details' => "Unblocked client {$macAddress}: Access restored, next due date reset to " . $client->next_due_date->format('Y-m-d'),
                ]);
            }

            session()->flash('success', "Client $macAddress unblocked successfully.");

            // Refresh client list to update UI properly
            $this->loadClientsFromDb();

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
