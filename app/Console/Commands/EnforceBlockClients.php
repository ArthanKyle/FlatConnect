<?php

namespace App\Console\Commands;

use App\Jobs\BlockClientJob;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Console\Command;

class EnforceBlockClients extends Command
{
    protected $signature = 'clients:enforce-blocks';

    protected $description = 'Dispatch jobs to block all overdue clients';

    public function handle(): void
    {
        $this->info('Dispatching block jobs for overdue clients...');

        $overdueClients = Client::whereDate('next_due_date', '<', Carbon::today())->get();

        foreach ($overdueClients as $client) {
            BlockClientJob::dispatch($client->mac_address, $client->repeater_name ?? 'Unknown');
        }

        $this->info('Done. Dispatched '.count($overdueClients).' jobs.');
    }
}
