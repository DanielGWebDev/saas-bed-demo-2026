<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add 4 users manually
        $users = [
            [
                'given_name' => 'Admin',
                'family_name' => 'Istrator',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('Password1'),
            ],
            [
                'given_name' => 'Staff',
                'family_name' => 'User',
                'email' => 'staff@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('Password1'),
            ],
            [
                'given_name' => 'Client',
                'family_name' => 'User',
                'email' => 'client@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('Password1'),
            ],
            [
                'given_name' => 'Dummy',
                'family_name' => 'User',
                'email' => 'dummy@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('Password1'),
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        // Add 6 random factory users
        User::factory()->count(6)->create();
    }
}
