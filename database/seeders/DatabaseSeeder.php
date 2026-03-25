<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ContactSeeder::class,
            DetailTypeSeeder::class,
        ]);

        DB::table('personal_access_tokens')->insertOrIgnore([
            'name' => 'postman-testing',
            'token' => hash('sha256', 'postman-token-123456789'),
            'abilities' => json_encode(['*']),
            'tokenable_type' => 'App\\Models\\User',
            'tokenable_id' => 1,
            'expires_at' => now()->addYear(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
