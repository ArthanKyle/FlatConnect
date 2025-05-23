<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use App\Models\Client;
use App\Services\Tplink\ClientDiscoveryService;
use Carbon\Carbon;

class BlockOverdueClients extends Command
{
    // Command signature and description
    protected $signature = 'clients:block-overdue';
    protected $description = 'Automatically block clients whose next due date has passed';

    // Schedule method for automatic scheduling
    public function schedule(Schedule $schedule)
    {
        // Change this to everyMinute() for testing, then back to dailyAt('00:01')
        $schedule->command('clients:block-overdue')->dailyAt('00:01');
    }

    public function handle()
    {
        $this->info('Running block-overdue logic...');

        // Get all active clients whose due date is before today
        $clients = Client::where('next_due_date', '<', Carbon::today())
            ->where('status', 'active')
            ->get();

        $this->info("Found {$clients->count()} overdue client(s).");

        if ($clients->isEmpty()) {
            $this->info("No overdue clients found.");
            return 0;
        }

        $tplink = new ClientDiscoveryService();

        foreach ($clients as $client) {
            $this->info("Processing client {$client->mac_address}...");

            // Attempt to block client by MAC address
            $result = $tplink->blockClient($client->mac_address, $client->repeater_name ?? 'Unknown');

            if ($result) {
                $client->status = 'blocked';
                $client->save();

                $this->info("Blocked client: {$client->mac_address}");
            } else {
                $this->error("Failed to block client: {$client->mac_address}");
            }
        }

        $this->info('Block overdue command completed.');
        return 0;
    }
}
