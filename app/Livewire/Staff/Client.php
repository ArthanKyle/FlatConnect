<?php

namespace App\Livewire\Staff;

use Livewire\Component;
use App\Models\Client as ClientModel;
use App\Services\Tplink\ClientDiscoveryService;
use App\Models\AdminLog;
use Carbon\Carbon;

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
    public $editFirstName = null;
    public $editLastName = null;
    public $editApartmentNumber = null;
    public $editBuilding = null;
    public $rateLimitClientId = null;
    public $rateLimitDownload = 5;
    public $rateLimitUpload = 5;

    protected $listeners = ['refreshClients' => 'loadClientsFromDb'];

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
                    'repeater_status' => 'online',
                    'enforcement_status' => 'synced',
                ]);
            } else {
                ClientModel::create([
                    'mac_address' => $mac,
                    'ip_address' => $client['ip_address'] ?? 'Unknown',
                    'repeater_name' => $hostname,
                    'block_status' => ($client['blocked'] ?? false) ? 'blocked' : 'unblocked',
                    'repeater_status' => 'online',
                    'enforcement_status' => 'synced',
                    'next_due_date' => now()->addDays(30),
                ]);
            }
        }

        $this->syncClientStatuses($discovered);
    }

    protected function syncClientStatuses(array $discoveredClients): void
    {
        $discoveredMacs = collect($discoveredClients)->pluck('mac_address')->toArray();

        $offlineClients = ClientModel::whereNotIn('mac_address', $discoveredMacs)
                                ->where('repeater_status', '!=', 'offline')
                                ->get();

        foreach ($offlineClients as $client) {
            $client->update([
                'repeater_status' => 'offline',
                'enforcement_status' => 'pending',
            ]);

            AdminLog::create([
                'action' => 'Automatic Inactive',
                'mac_address' => $client->mac_address,
                'details' => "Client {$client->mac_address} marked inactive automatically (repeater offline).",
            ]);
        }
    }

    public function loadClientsFromDb(): void
    {
        $query = ClientModel::with('bandwidthLimit');

        if (!empty($this->search)) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('repeater_name', 'like', $searchTerm)
                  ->orWhere('mac_address', 'like', $searchTerm);
            });
        }

        if ($this->showOnlyBlocked) {
            $query->where('block_status', 'blocked');
        }

        $allClients = $query->orderBy('repeater_name')->get();

        $this->clients = $allClients->map(function ($client) {
            $nextDueDateFormatted = '-';
            if ($client->isBlocked()) {
                $nextDueDateFormatted = 'Until payment is fulfilled';
            } elseif ($client->next_due_date instanceof Carbon) {
                $nextDueDateFormatted = $client->next_due_date->format('Y-m-d');
            } elseif ($client->next_due_date) {
                try {
                    $nextDueDateFormatted = Carbon::parse($client->next_due_date)->format('Y-m-d');
                } catch (\Exception $e) {
                    $nextDueDateFormatted = '-';
                }
            }

            return [
                'id' => $client->id,
                'fullname' => trim("{$client->first_name} {$client->last_name}"),
                'mac_address' => $client->mac_address,
                'ip_address' => $client->ip_address,
                'hostname' => $client->repeater_name,
                'repeater_name' => $client->repeater_name,
                'apartment_number' => $client->apartment_number,
                'building' => $client->building,
                'blocked' => $client->isBlocked(),
                'block_status' => $client->block_status,
                'repeater_status' => $client->repeater_status,
                'enforcement_status' => $client->enforcement_status,
                'last_seen_at' => $client->last_seen_at ? $client->last_seen_at->format('Y-m-d H:i:s') : null,
                'next_due_date' => $client->next_due_date instanceof Carbon
                    ? $client->next_due_date->format('Y-m-d')
                    : null,
                'next_due_formatted' => $nextDueDateFormatted,
                'rate_limit_download' => $client->bandwidthLimit->download_limit ?? null,
                'rate_limit_upload' => $client->bandwidthLimit->upload_limit ?? null,
            ];
        })->toArray();

        $this->currentPage = 1;
        $this->paginateClients();
    }

    protected function getTotalPages(): int
    {
        return (int) ceil(count($this->clients) / $this->perPage);
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

    // This method is called when a staff member clicks on the "Edit" button
    public function editClient(int $clientId): void
    {
        $client = ClientModel::findOrFail($clientId);

        $this->editingClientId = $client->id;
        $this->editNextDueDate = $client->next_due_date ? $client->next_due_date->format('Y-m-d') : null;
        $this->editRepeaterName = $client->repeater_name;
        $this->editFirstName = $client->first_name;
        $this->editLastName = $client->last_name;
        $this->editApartmentNumber = $client->apartment_number;
        $this->editBuilding = $client->building;
    }

    // This method is called when a staff member clicks on the "Cancel" button
    public function cancelEdit(): void
    {
        $this->editingClientId = null;
        $this->editNextDueDate = null;
        $this->editRepeaterName = null;
        $this->editFirstName = null;
        $this->editLastName = null;
        $this->editApartmentNumber = null;
        $this->editBuilding = null;
    }

    // This method is called when a staff member clicks on the "Save" button
    public function saveEdit(): void
    {
        $client = ClientModel::findOrFail($this->editingClientId);

        $originalRepeater = $client->repeater_name;
        $originalDueDate = optional($client->next_due_date)->format('Y-m-d');

        $client->update([
            'next_due_date' => $this->editNextDueDate,
            'repeater_name' => $this->editRepeaterName,
            'first_name' => $this->editFirstName,
            'last_name' => $this->editLastName,
            'apartment_number' => $this->editApartmentNumber,
            'building' => $this->editBuilding,
        ]);

        $details = "Edited client {$client->mac_address}: Repeater name '{$originalRepeater}' → '{$this->editRepeaterName}', Due date '{$originalDueDate}' → '{$this->editNextDueDate}'";

        AdminLog::create([
            'action' => 'Edit',
            'mac_address' => $client->mac_address,
            'details' => $details,
        ]);

        $this->cancelEdit();
        $this->loadClientsFromDb();
    }

    // This method is called when a staff member clicks on the "Block" button
    public function block(string $macAddress, ClientDiscoveryService $discoveryService): void
    {
        if ($discoveryService->blockClient($macAddress)) {
            $client = ClientModel::where('mac_address', $macAddress)->first();
            if ($client) {
                $client->update(['block_status' => 'blocked']);
                AdminLog::create([
                    'action' => 'Block',
                    'mac_address' => $macAddress,
                    'details' => "Blocked client {$macAddress}: Next due date suspended until payment is fulfilled.",
                ]);
                $this->loadClientsFromDb();
            }
        }
    }

    // This method is called when a staff member clicks on the "Unblock" button
    public function unblock(string $macAddress, ClientDiscoveryService $discoveryService): void
    {
        if ($discoveryService->unblockClient($macAddress)) {
            $client = ClientModel::where('mac_address', $macAddress)->first();
            if ($client) {
                $client->update(['block_status' => 'unblocked']);
                AdminLog::create([
                    'action' => 'Unblock',
                    'mac_address' => $macAddress,
                    'details' => "Unblocked client {$macAddress}: Access restored, next due date reset to " . ($client->next_due_date ? $client->next_due_date->format('Y-m-d') : '-'),
                ]);
                $this->loadClientsFromDb();
            }
        }
    }

    // This method is called when the rate limiter is opened for a specific client
    public function openRateLimiter(int $clientId): void
    {
        $this->rateLimitClientId = $clientId;
        $client = ClientModel::with('bandwidthLimit')->find($clientId);

        if ($client && $client->bandwidthLimit) {
            $this->rateLimitDownload = $client->bandwidthLimit->download_limit;
            $this->rateLimitUpload = $client->bandwidthLimit->upload_limit;
        } else {
            $this->rateLimitDownload = 5;
            $this->rateLimitUpload = 5;
        }
    }

    // This method is called when the rate limit values are updated      
    public function saveRateLimit(ClientDiscoveryService $discoveryService): void
    {
        $client = ClientModel::findOrFail($this->rateLimitClientId);

        $success = $discoveryService->limitClientBandwidth(
            $client->mac_address,
            $this->rateLimitDownload,
            $this->rateLimitUpload
        );

        if ($success) {
            $client->bandwidthLimit()->updateOrCreate(
            ['client_id' => $client->id],
            [
                'download_limit' => $this->rateLimitDownload,
                'upload_limit' => $this->rateLimitUpload,
            ]
        );

            AdminLog::create([
                'action' => 'Rate Limit',
                'mac_address' => $client->mac_address,
                'details' => "Applied {$this->rateLimitDownload}Mbps DL / {$this->rateLimitUpload}Mbps UL to {$client->mac_address}.",
            ]);
        }

        $this->rateLimitClientId = null;
        $this->loadClientsFromDb();
    }

    public function render()
    {
        $totalPages = $this->getTotalPages();

        return view('livewire.staff.client', [
            'clients' => $this->paginatedClients,
            'currentPage' => $this->currentPage,
            'perPage' => $this->perPage,
            'totalClients' => count($this->clients),
            'totalPages' => $totalPages,
            'showOnlyBlocked' => $this->showOnlyBlocked,
            'search' => $this->search,
        ])->layout('layouts.app', ['title' => 'Admin Dashboard']);
    }
}
