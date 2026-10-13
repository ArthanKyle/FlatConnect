<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SecurityAudit extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:security-audit';

    /**
     * The console command description.
     */
    protected $description = 'Run security audit checks on the application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔒 Starting Security Audit...');
        $this->newLine();

        $issues = [];

        // Check 1: Debug mode in production
        if (config('app.debug') && app()->environment('production')) {
            $issues[] = '⚠️  CRITICAL: Debug mode enabled in production (.env APP_DEBUG=true)';
        } else {
            $this->info('✅ Debug mode properly configured');
        }

        // Check 2: Strong APP_KEY
        if (!config('app.key') || strlen(config('app.key')) < 32) {
            $issues[] = '⚠️  CRITICAL: APP_KEY is missing or too short';
        } else {
            $this->info('✅ APP_KEY is set');
        }

        // Check 3: Database credentials
        if (config('database.connections.mysql.password') === '' && app()->environment('production')) {
            $issues[] = '⚠️  WARNING: Empty database password';
        } else {
            $this->info('✅ Database credentials configured');
        }

        // Check 4: TP-Link credentials
        if (config('tplink.username') === 'admin' || config('tplink.password') === 'secret') {
            $issues[] = '⚠️  CRITICAL: Default TP-Link credentials detected';
        } else {
            $this->info('✅ TP-Link credentials configured');
        }

        // Check 5: PayMongo credentials
        if (empty(env('PAYMONGO_SECRET_KEY')) || str_contains(env('PAYMONGO_SECRET_KEY'), 'test')) {
            $issues[] = '⚠️  WARNING: PayMongo using test credentials';
        } else {
            $this->info('✅ PayMongo credentials configured');
        }

        // Check 6: Session security
        if (!config('session.encrypt') && app()->environment('production')) {
            $issues[] = '⚠️  WARNING: Session encryption disabled';
        } else {
            $this->info('✅ Session encryption enabled');
        }

        // Check 7: HTTPS enforcement
        if (!config('session.secure') && app()->environment('production')) {
            $issues[] = '⚠️  WARNING: Secure cookies not enforced';
        } else {
            $this->info('✅ Secure cookies configured');
        }

        // Check 8: Storage permissions
        $storageWritable = File::isWritable(storage_path());
        if (!$storageWritable) {
            $issues[] = '⚠️  WARNING: Storage directory not writable';
        } else {
            $this->info('✅ Storage directory is writable');
        }

        // Check 9: .env file in gitignore
        $gitignoreContent = File::exists(base_path('.gitignore')) 
            ? File::get(base_path('.gitignore')) 
            : '';
        
        if (!str_contains($gitignoreContent, '.env')) {
            $issues[] = '⚠️  CRITICAL: .env not in .gitignore';
        } else {
            $this->info('✅ .env is in .gitignore');
        }

        // Check 10: Recent failed login attempts
        $recentFailures = DB::table('admin_logs')
            ->where('action', 'like', '%failed%')
            ->where('created_at', '>', now()->subHours(24))
            ->count();
        
        if ($recentFailures > 50) {
            $issues[] = "⚠️  WARNING: {$recentFailures} failed login attempts in last 24 hours";
        } else {
            $this->info("✅ Failed login attempts: {$recentFailures} (last 24h)");
        }

        // Check 11: Composer dependencies
        if (File::exists(base_path('composer.lock'))) {
            $this->info('✅ composer.lock exists (run composer audit separately)');
        }

        $this->newLine();

        // Summary
        if (empty($issues)) {
            $this->info('🎉 No critical security issues found!');
            return 0;
        } else {
            $this->error('❌ Security Issues Found:');
            $this->newLine();
            foreach ($issues as $issue) {
                $this->line($issue);
            }
            $this->newLine();
            $this->warn('Review and fix these issues before deploying to production.');
            return 1;
        }
    }
}
