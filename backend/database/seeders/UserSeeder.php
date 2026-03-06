<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Admin User', 'email' => 'admin@test.com', 'role' => 'Admin'],
            ['name' => 'Manager User', 'email' => 'manager@test.com', 'role' => 'Manager'],
            ['name' => 'Finance User', 'email' => 'finance@test.com', 'role' => 'Finance'],
            ['name' => 'Legal User', 'email' => 'legal@test.com', 'role' => 'Legal'],
            ['name' => 'CEO User', 'email' => 'ceo@test.com', 'role' => 'CEO'],
            ['name' => 'HR User', 'email' => 'hr@test.com', 'role' => 'HR'],
            ['name' => 'Employee User', 'email' => 'employee@test.com', 'role' => 'Employee'],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );

            // Sync roles to avoid duplicates if already assigned
            $user->syncRoles([$userData['role']]);
        }
    }
}
