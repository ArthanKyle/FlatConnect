<?php

namespace App\Livewire\Staff;

use Livewire\Component;

class Payments extends Component
{
    public array $clients = [];

    public function render()
    {
        return view('livewire.staff.payments')->layout('layouts.app', ['title' => 'Admin Dashboard']);;
    }
}
