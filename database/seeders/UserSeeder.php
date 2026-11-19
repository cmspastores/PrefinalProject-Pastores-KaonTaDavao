<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->create([
            'name' => 'Carlos Manuel Pastores',
            'email' => 'pastores@kaonta.test',
            'password' => Hash::make('kaonta2026'),
        ]);

        User::query()->create([
            'name' => 'Tita Pastores',
            'email' => 'tita.pastores@kaonta.test',
            'password' => Hash::make('kaonta2026'),
        ]);

        User::factory()->count(10)->create();
    }
}
