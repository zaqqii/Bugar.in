<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds to create the administrator account.
     */
    public function run(): void
    {
        // 1. Akun Admin Utama (admin@bugar.in)
        User::updateOrCreate(
            ['email' => 'admin@bugar.in'],
            [
                'name' => 'Super Administrator',
                'username' => 'admin',
                'password' => Hash::make('Admin123!'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        // 2. Akun Admin Ops Cadangan (admin@gerak.in - sesuai placeholder login)
        User::updateOrCreate(
            ['email' => 'admin@gerak.in'],
            [
                'name' => 'Admin Console Ops',
                'username' => 'admin_ops',
                'password' => Hash::make('Admin123!'),
                'role' => 'admin',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
    }
}

