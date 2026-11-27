<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin credentials for Innovative Engineering BD
        User::updateOrCreate(
            ['email' => 'admin@innovativeengbd.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Also create admin@hostdivine.com for convenience if user tests with same email
        User::updateOrCreate(
            ['email' => 'admin@hostdivine.com'],
            [
                'name' => 'Admin HostDivine',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
