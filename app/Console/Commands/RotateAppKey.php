<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RotateAppKey extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:rotate-key 
                            {--force : Force key rotation without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Rotate application encryption key with backup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (app()->environment('production') && !$this->option('force')) {
            if (!$this->confirm('⚠️  This will invalidate all encrypted data and sessions. Continue?')) {
                $this->info('Operation cancelled.');
                return 1;
            }
        }

        // Backup current key
        $currentKey = config('app.key');
        
        if ($currentKey) {
            $backupFile = storage_path('app/.app_key_backup_' . now()->format('Y-m-d_His'));
            file_put_contents($backupFile, $currentKey);
            $this->info("✅ Current key backed up to: {$backupFile}");
        }

        // Generate new key
        Artisan::call('key:generate', ['--force' => true]);
        
        $this->info('🔑 New application key generated');
        $this->warn('⚠️  All users will be logged out');
        $this->warn('⚠️  Encrypted data with old key cannot be decrypted');
        
        // Clear caches
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        
        $this->info('✅ Caches cleared');
        $this->info('');
        $this->info('Next steps:');
        $this->line('1. Update .env on all servers');
        $this->line('2. Restart queue workers');
        $this->line('3. Notify users of session expiration');

        return 0;
    }
}
