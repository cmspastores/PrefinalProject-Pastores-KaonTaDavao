<?php

namespace Database\Seeders;

use App\Models\Dish;
use App\Models\District;
use App\Models\Eatery;
use App\Models\Feature;
use App\Models\Permit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class EaterySeeder extends Seeder
{
    private const LAST_NAME = 'Pastores';

    private const FACTORY_EATERY_COUNT = 14;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $featureIds = Feature::query()->pluck('id');
        $eateryName = 'Carinderia ni '.self::LAST_NAME;

        $eatery = Eatery::query()->create([
            'district_id' => District::query()->where('name', 'Poblacion')->value('id'),
            'name' => $eateryName,
            'slug' => Str::slug($eateryName),
            'address' => 'San Pedro St., Poblacion, Davao City',
            'price_range' => 'budget',
            'specialty' => 'Tinolang Manok',
            'is_open' => true,
        ]);

        $eatery->features()->sync(
            Feature::query()
                ->whereIn('name', ['Suki ni '.self::LAST_NAME, 'Budget Meal'])
                ->pluck('id'),
        );

        $eatery->permit()->create([
            'permit_no' => 'DVO-2026-0916',
            'expires_on' => '2027-09-16',
        ]);

        $eatery->dishes()->createMany([
            ['name' => 'Tinolang Manok', 'price' => 85.00, 'is_bestseller' => true],
            ['name' => 'Kinilaw na Tuna', 'price' => 120.00, 'is_bestseller' => false],
            ['name' => 'Durian Shake', 'price' => 75.00, 'is_bestseller' => false],
        ]);

        for ($index = 0; $index < self::FACTORY_EATERY_COUNT; $index++) {
            $factory = Eatery::factory();

            if ($index < 3) {
                $factory = $factory->closed();
            }

            $factoryEatery = $factory->create();
            $expiresOn = $index === 0
                ? today()->subDay()->toDateString()
                : today()->addDays(30 + $index)->toDateString();

            $factoryEatery->permit()->save(
                Permit::factory()
                    ->for($factoryEatery)
                    ->make([
                        'permit_no' => sprintf('DVO-2026-%04d', $index + 1),
                        'expires_on' => $expiresOn,
                    ]),
            );

            $dishCount = fake()->numberBetween(2, 5);

            for ($dishIndex = 0; $dishIndex < $dishCount; $dishIndex++) {
                $factoryEatery->dishes()->save(Dish::factory()->make([
                    'eatery_id' => $factoryEatery->id,
                ]));
            }

            $factoryEatery->features()->attach(
                $featureIds->random(fake()->numberBetween(1, 3))->all(),
            );
        }
    }
}
