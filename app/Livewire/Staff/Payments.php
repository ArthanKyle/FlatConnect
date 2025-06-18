<?php

namespace App\Livewire\Staff;

use App\Jobs\BlockClientJob;
use App\Jobs\UnblockClientJob;
use App\Models\AdminLog;
use App\Models\Client;
use Livewire\Component;

class Payments extends Component
{
    public $search = '';

    public $statusFilter = ''; // 'paid' or 'unpaid'

    public function markAsPaid($clientId)
    {
        $client = Client::findOrFail($clientId);
        $client->payment_status = 'Paid';
        $client->next_due_date = now()->addDays(30);
        $client->block_status = 'Unblocked';
        $client->save();

        // Dispatch job to queue
        dispatch(new UnblockClientJob($client->mac_address, $client->repeater_name ?? 'Unknown'));

        AdminLog::create([
            'details' => "Marked {$client->fullname} as Paid",
            'mac_address' => $client->mac_address,
            'action' => 'Payment Update',
        ]);
    }

    public function markAsUnpaid($clientId)
    {
        $client = Client::findOrFail($clientId);
        $client->payment_status = 'Unpaid';
        $client->block_status = 'Blocked';
        $client->next_due_date = null;
        $client->save();

        // Dispatch job to block the client
        dispatch(new BlockClientJob($client->mac_address, $client->repeater_name ?? 'Unknown'));

        AdminLog::create([
            'details' => "Marked {$client->fullname} as Unpaid",
            'mac_address' => $client->mac_address,
            'action' => 'Payment Update',
        ]);
    }

    public function getFilteredClientsProperty()
    {
        return Client::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%")
                    ->orWhere('mac_address', 'like', "%{$this->search}%");
            }))
            ->get()
            ->map(function ($client) {
                if ($client->next_due) {
                    $now = now();

                    if ($now->lessThanOrEqualTo($client->next_due)) {
                        $client->dynamic_status = 'Paid';
                        $client->due_notice = 'Due in '.$now->diffInDays($client->next_due).' days';
                    } else {
                        $client->dynamic_status = 'Unpaid';
                        $client->due_notice = 'Overdue by '.$client->next_due->diffInDays($now).' days';
                    }

                    $client->next_due_formatted = $client->next_due->format('Y-m-d');
                } else {
                    $client->dynamic_status = 'Unpaid';
                    $client->due_notice = 'No payment record';
                    $client->next_due_formatted = null;
                }

                return $client;
            })
            ->when($this->statusFilter, fn ($clients) => $clients->filter(fn ($client) => strtolower($client->dynamic_status) === strtolower($this->statusFilter)
            )
            );
    }

    public function render()
    {
        return view('livewire.staff.payments', [
            'clients' => $this->filteredClients,
        ])->layout('layouts.app', ['title' => 'Admin Dashboard']);
    }
}
