<?php

namespace Database\Seeders;

use App\Models\Eatery;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eatery = Eatery::query()
            ->where('slug', Str::slug('Carinderia ni Pastores'))
            ->firstOrFail();
        $carlos = User::query()->where('email', 'pastores@kaonta.test')->firstOrFail();
        $tita = User::query()->where('email', 'tita.pastores@kaonta.test')->firstOrFail();
        $factoryUsers = User::query()
            ->whereNotIn('email', ['pastores@kaonta.test', 'tita.pastores@kaonta.test'])
            ->get();

        $eatery->reviews()->createMany([
            [
                'user_id' => $carlos->id,
                'rating' => 5,
                'body' => 'Pinaka paborito ko ang Tinolang Manok.',
            ],
            [
                'user_id' => $tita->id,
                'rating' => 5,
                'body' => 'Lami kaayo! Balik-balikan ni Tita Pastores.',
            ],
            [
                'user_id' => $factoryUsers->firstOrFail()->id,
                'rating' => 4,
                'body' => 'Lami ug sulit ang pagkaon.',
            ],
        ]);

        $factoryEateries = Eatery::query()
            ->where('id', '!=', $eatery->id)
            ->where('is_open', true)
            ->get();

        foreach ($factoryEateries as $factoryEatery) {
            $reviewCount = fake()->numberBetween(0, 6);

            if ($reviewCount > 0) {
                Review::factory()
                    ->count($reviewCount)
                    ->for($factoryEatery)
                    ->recycle($factoryUsers)
                    ->create();
            }
        }
    }
}
