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
        if (app()->environment('production')) {
            throw new \RuntimeException('Seeder cannot run in production.');
        }

        $password = env('SEEDER_PASSWORD');

        if (!$password) {
            throw new \RuntimeException('SEEDER_PASSWORD environment variable is required.');
        }

        User::factory()->create([
            'username' => 'Test User',
            'email' => 'test@example.com',
            'password' => $password,
        ]);
    }
}
