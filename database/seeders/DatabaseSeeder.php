<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Demo Customer',
            'email' => 'demo.customer@example.com',
            'is_admin' => false,
        ]);

        User::factory()->create([
            'name' => 'Demo Admin',
            'email' => 'admin@demo.test',
            'is_admin' => true,
        ]);
    }
}
