<?php

namespace App\Services\Tplink;

use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Client;

class ClientDiscoveryService
{
    protected string $baseUrl = 'http://192.168.1.11';
    protected CookieJar $cookieJar;

    public function __construct()
    {
        $this->cookieJar = new CookieJar();
    }

    public function login(): bool
    {
        Log::info('Attempting to login to TP-Link controller at ' . $this->baseUrl);

        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
            'Origin' => $this->baseUrl,
            'Referer' => $this->baseUrl . '/',
            'User-Agent' => 'Mozilla/5.0',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->withOptions([
            'verify' => false,
            'cookies' => $this->cookieJar,
        ])
        ->asForm()
        ->post($this->baseUrl . '/', [
            'username' => 'AdminExistentialCrisis',
            'password' => '66E652DD4948E20796A7B37C64FC0079',
        ]);

        if ($response->successful()) {
            Log::info('Login successful.');
            return true;
        } else {
            Log::error('Login failed with status: ' . $response->status());
            return false;
        }
    }

    public function fetchClients(): array
    {
        Log::info('Starting client fetch sequence.');

        if (!$this->login()) {
            Log::error('Login failed, aborting client fetch.');
            return [];
        }

        $responses = [
            'user' => $this->getJson('/data/status.client.user.json?operation=load'),
            'portal' => $this->getJson('/data/status.client.portaluser.json?operation=load'),
            'blocklist' => $this->getJson('/data/status.client.blocklist.json?operation=load'),
        ];

        if (in_array(false, $responses, true)) {
            Log::error('One or more client endpoints failed.');
            return [];
        }

        $excludedMacs = [
            '8C-B8-7E-D4-52-01', // Your PC MAC
        ];

        $allClients = collect(
            array_merge(
                $responses['user']['data'] ?? [],
                $responses['portal']['data'] ?? []
            )
        );

        $blockedMacs = collect($responses['blocklist']['data'] ?? [])
            ->pluck('MAC')
            ->map(fn($mac) => strtoupper($mac))
            ->toArray();

        return $allClients->map(function ($client) use ($blockedMacs, $excludedMacs) {
            $mac = strtoupper($client['MAC'] ?? '');
            if (!$mac || in_array($mac, $excludedMacs)) return null;

            $ip = $client['IP'] ?? 'Unknown';
            $hostname = $client['hostname'] ?? 'Unknown';
            $isBlocked = in_array($mac, $blockedMacs);
            $status = $isBlocked ? 'blocked' : 'active';

            $existing = Client::where('mac_address', $mac)->first();

            if (!$existing) {
                Client::create([
                    'mac_address' => $mac,
                    'ip_address' => $ip,
                    'repeater_name' => $hostname,
                    'status' => $status,
                    'next_due_date' => now()->addDays(30),
                ]);
            } else {
                $updateData = [
                    'ip_address' => $ip,
                    'status' => $status,
                    'next_due_date' => $existing->next_due_date ?? now()->addDays(30),
                ];

                if ($hostname && $hostname !== 'Unknown') {
                    if ($existing->repeater_name !== $hostname) {
                        Log::info("Updating repeater_name for MAC {$mac} from '{$existing->repeater_name}' to '{$hostname}'");
                    }
                    $updateData['repeater_name'] = $hostname;
                }

                $existing->update($updateData);
            }

            return [
                'mac_address' => $mac,
                'ip_address' => $ip,
                'hostname' => $hostname,
                'repeater_name' => $hostname,
                'blocked' => $isBlocked,
            ];
        })->filter()->toArray();
    }

    private function getJson(string $endpoint)
    {
        $response = Http::withHeaders([
            'X-Requested-With' => 'XMLHttpRequest',
            'Origin' => $this->baseUrl,
            'Referer' => $this->baseUrl . '/',
            'User-Agent' => 'Mozilla/5.0',
        ])
        ->withOptions([
            'verify' => false,
            'cookies' => $this->cookieJar,
        ])
        ->get($this->baseUrl . $endpoint);

        return $response->successful() ? $response->json() : false;
    }

    public function blockClient(string $mac, string $hostname = 'Unknown', int $up = 0, int $down = 0): bool
    {
        if (!$this->login()) return false;

        $mac = strtoupper($mac);
        $timestamp = round(microtime(true) * 1000);

        $response = Http::withHeaders([
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
            'Referer' => $this->baseUrl . '/',
            'User-Agent' => 'Mozilla/5.0',
            'X-Requested-With' => 'XMLHttpRequest',
            'Origin' => $this->baseUrl,
        ])
        ->withOptions([
            'verify' => false,
            'cookies' => $this->cookieJar,
        ])
        ->get($this->baseUrl . "/data/status.client.user.json", [
            'operation' => 'block',
            'hostname' => $hostname,
            'MAC' => $mac,
            'up' => $up,
            'down' => $down,
            '_' => $timestamp,
        ]);

        if ($response->successful()) {
            Log::info("Blocked client with MAC {$mac}");
            return true;
        }

        Log::error("Failed to block client {$mac}, status: " . $response->status());
        return false;
    }

    public function unblockClient(string $mac, string $hostname = 'Unknown', int $up = 0, int $down = 0): bool
    {
        if (!$this->login()) return false;

        $mac = strtoupper($mac);
        $timestamp = round(microtime(true) * 1000);

        $response = Http::withHeaders([
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
            'Referer' => $this->baseUrl . '/',
            'User-Agent' => 'Mozilla/5.0',
            'X-Requested-With' => 'XMLHttpRequest',
            'Origin' => $this->baseUrl,
        ])
        ->withOptions([
            'verify' => false,
            'cookies' => $this->cookieJar,
        ])
        ->get($this->baseUrl . "/data/status.client.blocklist.json", [
            'operation' => 'remove',
            'hostname' => $hostname,
            'MAC' => $mac,
            'up' => $up,
            'down' => $down,
            '_' => $timestamp,
        ]);

        if ($response->successful()) {
            Log::info("Unblocked client with MAC {$mac}");
            return true;
        }

        Log::error("Failed to unblock client {$mac}, status: " . $response->status());
        return false;
    }
}
