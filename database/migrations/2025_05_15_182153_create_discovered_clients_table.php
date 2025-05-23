<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscoveredClientsTable extends Migration
{
    public function up()
    {
        Schema::create('discovered_clients', function (Blueprint $table) {
            $table->id();
            $table->string('mac_address')->unique();
            $table->string('ip_address')->nullable();
            $table->string('repeater_name')->nullable();  // hostname
            $table->integer('rssi')->nullable();
            $table->string('rate')->nullable();
            $table->string('active_time')->nullable();
            $table->bigInteger('upload')->nullable();
            $table->bigInteger('download')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('discovered_clients');
    }
}
