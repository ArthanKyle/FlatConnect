<?php

namespace App\Livewire\Staff;

use Livewire\Component;

class Settings extends Component
{
    public function render()
    {
        return view('livewire.staff.settings')->layout('layouts.app', ['title' => 'Admin Dashboard']);
    }
}
