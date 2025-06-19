<?php

namespace App\Livewire\Auth;

use App\Models\Client;
use App\Services\Tplink\ClientDiscoveryService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Component;

class Register extends Component
{
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public ?string $mac_address = null;
    public string $error = '';

    public function mount()
    {
        $this->mac_address = $this->getMacAddressFromRequestIp();
    }

    protected function getMacAddressFromRequestIp(): ?string
    {
        $ip = request()->ip();
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            logger()->warning("Invalid IP format: $ip");
            return null;
        }

        $output = shell_exec('arp -a');
        if (! $output) {
            logger()->error("ARP command failed.");
            return null;
        }

        preg_match("/$ip\s+.*\s+((?:[0-9A-Fa-f]{2}[-:]){5}[0-9A-Fa-f]{2})/", $output, $matches);
        if (! isset($matches[1])) {
            logger()->info("MAC address not found for IP $ip.");
            return null;
        }

        $mac = strtoupper(str_replace('-', ':', $matches[1]));
        return $mac;
    }

    public function register()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'password' => 'required|min:8|confirmed',
        ]);

        $rateKey = 'register:'.request()->ip();
        if (RateLimiter::tooManyAttempts($rateKey, 3)) {
            $this->addError('rate', 'Too many attempts. Please wait a moment.');
            return;
        }

        RateLimiter::hit($rateKey, 60); // 1 minute lockout

        $clientIp = request()->ip();
        $clientMac = $this->getMacAddressFromRequestIp();

        if (! $clientMac || ! preg_match('/^([0-9A-F]{2}[:-]){5}([0-9A-F]{2})$/i', $clientMac)) {
            $this->addError('mac', 'MAC address could not be verified. Please connect to the repeater.');
            return;
        }

        try {
            $discovery = new ClientDiscoveryService();
            $clients = $discovery->fetchClients();
        } catch (ConnectionException $e) {
            logger()->error("Repeater connection failed: {$e->getMessage()}");
            $this->addError('mac', 'Repeater unreachable. Connect and try again.');
            return;
        } catch (\Throwable $e) {
            logger()->error("Unexpected error during client fetch: {$e->getMessage()}");
            $this->addError('mac', 'Unexpected error occurred.');
            return;
        }

        $match = collect($clients)->firstWhere('mac_address', $clientMac);
        if (! $match) {
            $this->addError('mac', 'Device not detected on the network. Please ensure you are connected to the authorized repeater.');
            return;
        }

        $clientData = [
            'first_name' => Str::title($this->first_name),
            'last_name' => Str::title($this->last_name),
            'email' => strtolower($this->email),
            'password' => Hash::make($this->password),
            'mac_address' => $clientMac,
            'ip_address' => $clientIp,
            'repeater_name' => $match['repeater_name'] ?? 'Unknown',
            'status' => 'active',
            'next_due_date' => now()->addDays(30),
        ];

        $client = Client::updateOrCreate(
            ['mac_address' => $clientMac],
            $clientData
        );

        event(new Registered($client));
        Auth::guard('client')->login($client);

        return redirect()->route('client.dashboard');
    }

    public function render()
    {
        return view('livewire.auth.register')->layout('layouts.app', ['title' => 'Register - FlatConnect']);
    }
}
