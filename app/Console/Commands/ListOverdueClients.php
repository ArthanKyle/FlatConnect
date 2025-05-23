<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use Carbon\Carbon;

class ListOverdueClients extends Command
{
    protected $signature = 'clients:list-overdue';
    protected $description = 'List all active clients with overdue next_due_date';

    public function handle()
    {
        $today = Carbon::today();
        $this->info("Checking clients with next_due_date before {$today->toDateString()}...");

        $clients = Client::where('next_due_date', '<', $today)
            ->where('status', 'active')
            ->get();

        $count = $clients->count();

        if ($count === 0) {
            $this->info('No overdue clients found.');
            return 0;
        }

        $this->info("Found {$count} overdue client(s):");

        foreach ($clients as $client) {
            $this->line(" - MAC: {$client->mac_address} | Due Date: {$client->next_due_date} | Status: {$client->status}");
        }

        return 0;
    }
}
