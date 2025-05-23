<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Configuration\Commands;

Artisan::command('clients:block-overdue', function () {
    // Your blocking logic here or call your existing command class logic
    $this->info('Running block-overdue logic...');
    // For example, you could call the existing command's handle method or repeat the logic here.
});

return function (Commands $commands) {
    // Schedule the above command to run every minute for testing
    $commands->command('clients:block-overdue')->everyMinute();
};
