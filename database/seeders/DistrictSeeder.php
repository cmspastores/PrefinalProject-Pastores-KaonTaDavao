<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    private const DISTRICT_NAMES = [
        'Poblacion',
        'Talomo',
        'Agdao',
        'Buhangin',
        'Toril',
        'Bunawan',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::DISTRICT_NAMES as $districtName) {
            District::query()->create([
                'name' => $districtName,
            ]);
        }
    }
}
