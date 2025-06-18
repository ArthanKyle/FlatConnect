<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE clients MODIFY COLUMN status ENUM('active', 'inactive', 'blocked') DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE clients MODIFY COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
    }
};
