<?php

namespace App\Livewire\Staff;

use App\Models\AdminLog as AdminLogModel;
use Livewire\Component;
use Livewire\WithPagination;

class Logs extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public function render()
    {
        return view('livewire.staff.logs', [
            'logs' => AdminLogModel::latest()->paginate($this->perPage),
        ])->layout('layouts.app', ['title' => 'Admin Logs']);
    }
}
