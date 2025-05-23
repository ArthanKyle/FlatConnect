<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call the ClientSeeder
        $this->call(ClientSeeder::class);

        // Call the StaffSeeder
        $this->call(StaffSeeder::class);
    }
}