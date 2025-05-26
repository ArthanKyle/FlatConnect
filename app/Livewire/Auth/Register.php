<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Request;
use App\Services\Tplink\ClientDiscoveryService;
use Illuminate\Http\Client\ConnectionException;

class Register extends Component
{
    public string $first_name = '';
    public string $last_name  = '';
    public string $email      = '';
    public string $password   = '';
    public string $password_confirmation = '';
    public $mac_address = '';

    public function mount()
    {
        $this->mac_address = $this->getMacAddressFromRequestIp();
    }

    public function getMacAddressFromRequestIp(): ?string
    {
        $ip = request()->ip();
        logger()->info("Fetching MAC address for IP: {$ip}");

        $output = shell_exec("arp -a");

        if ($output) {
            logger()->debug("ARP Output:\n" . $output);
            preg_match("/$ip\s+.*\s+((?:[0-9A-Fa-f]{2}[-:]){5}[0-9A-Fa-f]{2})/", $output, $matches);
            if (isset($matches[1])) {
                logger()->info("MAC address found: " . $matches[1]);
                return $matches[1];
            } else {
                logger()->warning("No MAC address match found for IP: {$ip}");
            }
        } else {
            logger()->error("Failed to fetch ARP output");
        }

        return null;
    }

    public function register()
    {
        $this->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:clients,email',
            'password'   => 'required|min:6|confirmed',
        ]);

        $clientIp = Request::ip();
        $clientMac = $this->getMacAddressFromRequestIp();

        if (!$clientMac) {
            $this->addError('mac', 'MAC address could not be detected. Please connect to the repeater.');
            return;
        }

        // Use a try-catch to handle potential connection timeouts
        $discovery = new ClientDiscoveryService();
        try {
            $clients = $discovery->fetchClients();
        } catch (ConnectionException $e) {
            logger()->error("Client fetch failed: " . $e->getMessage());
            $this->addError('mac', 'Unable to reach the repeater. Please ensure you are connected and try again.');
            return;
        } catch (\Exception $e) {
            logger()->error("Unexpected error during client fetch: " . $e->getMessage());
            $this->addError('mac', 'An unexpected error occurred. Please try again.');
            return;
        }

        $match = collect($clients)->firstWhere('mac_address', strtoupper($clientMac));

        if (!$match) {
            $this->addError('mac', 'Device not found on the repeater. Please try again while connected.');
            return;
        }

        $existingClient = Client::where('mac_address', strtoupper($clientMac))->first();

        if ($existingClient) {
            $existingClient->update([
                'first_name' => $this->first_name,
                'last_name'  => $this->last_name,
                'email'      => $this->email,
                'password'   => Hash::make($this->password),
            ]);
            $client = $existingClient;
        } else {
            $client = Client::create([
                'first_name'     => $this->first_name,
                'last_name'      => $this->last_name,
                'email'          => $this->email,
                'password'       => Hash::make($this->password),
                'mac_address'    => strtoupper($clientMac),
                'ip_address'     => $clientIp,
                'repeater_name'  => $match['repeater_name'] ?? 'Unknown',
                'status'         => 'active',
                'next_due_date'  => now()->addDays(30),
            ]);
        }

       event(new Registered($client));
       Auth::guard('client')->login($client);

       return view('livewire.client.dashboard')->layout('layouts.app', ['title' => 'Client Dashboard']);

    }

    public function render()
    {
        return view('livewire.auth.register')->layout('layouts.app', ['title' => 'Register - FlatConnect']);
    }
}
