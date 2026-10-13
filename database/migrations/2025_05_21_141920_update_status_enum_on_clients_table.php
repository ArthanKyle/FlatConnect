<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support MODIFY COLUMN, so we check if the column needs updating
        // For SQLite, the column is already created with string type in the original migration
        // This migration is only needed if you're using MySQL/PostgreSQL
        
        if (Schema::hasColumn('clients', 'status')) {
            // Check database driver
            $driver = Schema::getConnection()->getDriverName();
            
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE clients MODIFY COLUMN status ENUM('active', 'inactive', 'blocked') DEFAULT 'active'");
            } elseif ($driver === 'pgsql') {
                // For PostgreSQL, would need to create enum type
                // For now, string type works fine
            }
            // SQLite uses string by default, no modification needed
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE clients MODIFY COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
        }
        // SQLite doesn't need rollback
    }
};
