<?php

namespace Database\Seeders;

use App\Models\DetailType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DetailTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['Mobile', 'Home', 'Work', 'Email'];

        foreach ($types as $type) {
            DetailType::firstOrCreate(['name' => $type]);
        }
    }
}
