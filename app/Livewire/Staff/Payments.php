<?php

namespace App\Livewire\Staff;

use Livewire\Component;
use App\Models\Client;
use App\Models\AdminLog;
use Illuminate\Support\Carbon;

class Payments extends Component
{
    public $search = '';
    public $statusFilter = ''; // 'paid' or 'unpaid'

    public function markAsPaid($clientId)
    {
        $client = Client::findOrFail($clientId);
        $client->status = 'Paid';
        $client->next_due = now()->addDays(30);
        $client->enforcement_status = 'Cleared'; // Optional: Reset enforcement
        $client->block_status = 'Unblocked';     // Optional: Visually reflect it
        $client->save();

        AdminLog::create([
            'action' => "Marked {$client->fullname} as Paid",
            'admin_id' => auth()->id(),
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
                        $client->due_notice = 'Due in ' . $now->diffInDays($client->next_due) . ' days';
                    } else {
                        $client->dynamic_status = 'Unpaid';
                        $client->due_notice = 'Overdue by ' . $client->next_due->diffInDays($now) . ' days';
                    }

                    $client->next_due_formatted = $client->next_due->format('Y-m-d');
                } else {
                    $client->dynamic_status = 'Unpaid';
                    $client->due_notice = 'No payment record';
                    $client->next_due_formatted = null;
                }

                return $client;
            })
            ->when($this->statusFilter, fn ($clients) =>
                $clients->filter(fn ($client) =>
                    strtolower($client->dynamic_status) === strtolower($this->statusFilter)
                )
            );
    }

    public function render()
    {
        return view('livewire.staff.payments', [
            'clients' => $this->filteredClients
        ])->layout('layouts.app', ['title' => 'Admin Dashboard']);
    }
}
