<?php

namespace App\Livewire\Client;

use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Livewire\Component;

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
        return 'FLC-'.strtoupper(uniqid());
    }

    public function logout()
    {
        if (Auth::guard('client')->check()) {
            $client = Auth::guard('client')->user();
            $client->setRememberToken(null); // Clear remember_token
            $client->save();

            Auth::guard('client')->logout();
            session()->invalidate();
            session()->regenerateToken();

            return redirect()->route('login'); // or 'client.login' if you have separate routes
        }

        return redirect()->route('login');
    }

    public function render()
    {
        return view('livewire.client.dashboard', [
            'transactionNumber' => $this->generateTransactionNumber(),
        ])->layout('layouts.app', ['title' => 'Client Dashboard']);
    }
}
