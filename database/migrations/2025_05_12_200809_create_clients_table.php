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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();;
            $table->string('last_name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('mac_address')->unique()->nullable();
            $table->string('repeater_name')->nullable();
            $table->string('ip_address')->nullable();
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->date('next_due_date')->nullable();
            $table->string('building')->nullable();            
            $table->string('apartment_number')->nullable();   
            $table->timestamp('last_seen_at')->nullable();     
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
