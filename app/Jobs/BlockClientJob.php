<?php

namespace App\Jobs;

use App\Models\Client;
use App\Services\Tplink\ClientDiscoveryService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BlockClientJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $mac;

    public string $hostname;

    public function __construct(string $mac, string $hostname = 'Unknown')
    {
        $this->mac = $mac;
        $this->hostname = $hostname;
    }

    public function handle(ClientDiscoveryService $tplink): void
    {
        $client = Client::where('mac_address', $this->mac)->first();

        if (! $client) {
            Log::warning("No client found for MAC {$this->mac}. Skipping block.");

            return;
        }

        // 🔐 Check if client is overdue (before today)
        if (! $client->next_due_date || Carbon::parse($client->next_due_date)->gte(Carbon::today())) {
            Log::info("Client {$this->mac} is not overdue yet. Blocking skipped.");

            return;
        }

        Log::info("Client {$this->mac} is overdue. Proceeding to block...");

        $success = $tplink->blockClient($this->mac, $this->hostname);

        if ($success) {
            $client->status = 'blocked';
            $client->save();
            Log::info("Blocked client {$this->mac} via queued job.");
        } else {
            Log::error("Failed to block client {$this->mac} via queued job.");
        }
    }
}
