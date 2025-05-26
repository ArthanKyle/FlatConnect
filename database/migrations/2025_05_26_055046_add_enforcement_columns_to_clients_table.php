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
        Schema::table('clients', function (Blueprint $table) {
            $table->enum('block_status', ['blocked', 'unblocked', 'pending_block', 'pending_unblock'])->default('unblocked');
            $table->enum('repeater_status', ['online', 'offline'])->default('online');
            $table->enum('enforcement_status', ['synced', 'pending'])->default('synced');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            //
        });
    }
};
