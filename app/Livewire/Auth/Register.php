<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Request;

class Register extends Component
{
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:clients,email',
            'password'   => 'required|min:6|confirmed',
        ]);

        $clientIp = Request::ip();
        $clientMac = exec("arp -n " . escapeshellarg($clientIp) . " | awk '/$clientIp/ {print $3}'") ?: null;

        $client = Client::create([
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email,
            'password'   => Hash::make($this->password),
            'ip_address' => $clientIp,
            'mac_address' => $clientMac,
        ]);

        event(new Registered($client));
        Auth::guard('client')->login($client);

        return redirect()->route('verification.notice');
    }

    public function render()
    {
        return view('livewire.auth.register')->layout('layouts.app', ['title' => 'Register']);
    }
}
