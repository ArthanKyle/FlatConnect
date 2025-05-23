<?php

namespace App\Livewire\Client;

use Livewire\Component;
use App\Services\Tplink\ClientDiscoveryService;

class Dashboard extends Component
{
    public $connectedIp;
    public $clientIp;
    public $nextDueDate;
    public $payments;

    public function mount(ClientDiscoveryService $tplink)
    {
        $client = auth()->guard('client')->user();

        // Use the correct service and client model
        $this->connectedIp = $tplink->getConnectedIp($client->mac_address);
        $this->clientIp = $client->ip_address;
        $this->nextDueDate = $client->next_due_date;
        $this->payments = $client->payments()->latest()->get(); // Assuming relation exists
    }

    public function render()
    {
        return view('livewire.client.dashboard')->layout('layouts.app', ['title' => 'Client Dashboard']);
    }
}