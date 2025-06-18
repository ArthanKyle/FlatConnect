<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('client_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->integer('download_limit')->default(5); // Mbps
            $table->integer('upload_limit')->default(5);   // Mbps
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_limits');
    }
};
