<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Illuminate\Support\Facades\Request;
use App\Models\Client;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public $connectedIp;
    public $clientIp;
    public $nextDueDate;
    public $payments = []; 

    public function mount()
    {
        $this->connectedIp = Request::ip(); 
        $client = Auth::guard('client')->user(); 

        if ($client) {
            $this->clientIp = $client->ip_address; 
            $this->nextDueDate = $client->next_due_date;

            $this->payments = Payment::where('client_id', $client->id)
                ->orderByDesc('created_at')
                ->get();
        }
    }

    public function generateTransactionNumber()
    {
        return 'FLC-' . strtoupper(uniqid());
    }

     public function logout()
    {
        Auth::logout(); 
        session()->invalidate();
        session()->regenerateToken(); 

        return redirect()->route('login');
    }

    public function render()
    {
      return view('livewire.client.dashboard', [
        'transactionNumber' => $this->generateTransactionNumber()
    ])->layout('layouts.app', ['title' => 'Client Dashboard']);
    }
}
