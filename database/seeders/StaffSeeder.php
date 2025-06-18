<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('staff')->insert([
            [
                'name' => 'Arthan Ydeo',
                'email' => 'arthankyle.ydeo@gmail.com',
                'password' => Hash::make('Apc1975loss!'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
