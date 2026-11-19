<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    private const FEATURE_NAMES = [
        'Suki ni Pastores',
        'Budget Meal',
        'Halal',
        'Open 24 Hours',
        'Aircon',
        'Pet Friendly',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::FEATURE_NAMES as $featureName) {
            Feature::query()->create([
                'name' => $featureName,
            ]);
        }
    }
}
