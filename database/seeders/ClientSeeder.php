<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Client::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doye@example.com',
            'password' => Hash::make('password123'),
            'mac_address' => '00:1A:2B:3C:4D:5E',
            'repeater_name' => 'Repeater-1',
            'ip_address' => '192.168.1.100',
            'status' => 'active',
            'next_due_date' => now()->addDays(30),
        ]);

        Client::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane.smioth@example.com',
            'password' => Hash::make('password123'),
            'mac_address' => '00:1A:2B:3C:4D:5F',
            'repeater_name' => 'Repeater-2',
            'ip_address' => '192.168.1.101',
            'status' => 'inactive',
            'next_due_date' => now()->addDays(60),
        ]);
    }
}