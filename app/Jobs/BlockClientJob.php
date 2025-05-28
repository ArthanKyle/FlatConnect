<?php

namespace App\Jobs;

use App\Services\Tplink\ClientDiscoveryService;
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
        Log::info("Block Client Job started for {$this->mac}");

        $success = $tplink->blockClient($this->mac, $this->hostname);

        if ($success) {
            Log::info("Blocked client {$this->mac} via queued job.");
        } else {
            Log::error("Failed to block client {$this->mac} via queued job.");
        }
    }
}
