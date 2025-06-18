<?php

namespace App\Livewire\Staff;

use App\Models\Client;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.staff.dashboard', [
            'paidCount' => Client::where('payment_status', 'paid')->count(),
            'unpaidCount' => Client::where('payment_status', 'unpaid')->count(),
            'repeaterCount' => Client::whereNotNull('repeater_name')->count(),
        ])->layout('layouts.app', ['title' => 'Admin Dashboard']);
    }
}
