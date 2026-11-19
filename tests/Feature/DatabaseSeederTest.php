<?php

use App\Models\District;
use App\Models\Eatery;
use App\Models\Feature;
use App\Models\Permit;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('seeds the required Davao eateries, users, and relationships', function () {
    $this->seed();

    $this->assertDatabaseCount('districts', 6)
        ->assertDatabaseCount('features', 6)
        ->assertDatabaseCount('users', 12)
        ->assertDatabaseCount('eateries', 15)
        ->assertDatabaseCount('permits', 15);

    $districtNames = District::query()->orderBy('name')->pluck('name')->all();
    $featureNames = Feature::query()->orderBy('name')->pluck('name')->all();

    expect($districtNames)->toBe(['Agdao', 'Buhangin', 'Bunawan', 'Poblacion', 'Talomo', 'Toril'])
        ->and($featureNames)->toBe([
            'Aircon',
            'Budget Meal',
            'Halal',
            'Open 24 Hours',
            'Pet Friendly',
            'Suki ni Pastores',
        ]);

    $carlos = User::query()->where('email', 'pastores@kaonta.test')->firstOrFail();
    $tita = User::query()->where('email', 'tita.pastores@kaonta.test')->firstOrFail();

    expect($carlos->name)->toBe('Carlos Manuel Pastores')
        ->and(Hash::check('kaonta2026', $carlos->password))->toBeTrue()
        ->and($tita->name)->toBe('Tita Pastores')
        ->and(Hash::check('kaonta2026', $tita->password))->toBeTrue();

    $eatery = Eatery::query()
        ->with(['district', 'features', 'permit', 'dishes', 'reviews'])
        ->where('slug', Str::slug('Carinderia ni Pastores'))
        ->firstOrFail();

    expect($eatery->name)->toBe('Carinderia ni Pastores')
        ->and($eatery->district->name)->toBe('Poblacion')
        ->and($eatery->address)->toBe('San Pedro St., Poblacion, Davao City')
        ->and($eatery->price_range)->toBe('budget')
        ->and($eatery->specialty)->toBe('Tinolang Manok')
        ->and($eatery->is_open)->toBeTrue()
        ->and($eatery->permit->permit_no)->toBe('DVO-2026-0916')
        ->and($eatery->permit->expires_on->toDateString())->toBe('2027-09-16')
        ->and($eatery->reviews->count())->toBeGreaterThanOrEqual(3);

    $personalFeatureNames = $eatery->features->pluck('name')->sort()->values()->all();

    expect($personalFeatureNames)->toBe(['Budget Meal', 'Suki ni Pastores']);

    $dishes = $eatery->dishes->keyBy('name');

    expect($dishes->keys()->sort()->values()->all())->toBe([
        'Durian Shake',
        'Kinilaw na Tuna',
        'Tinolang Manok',
    ])
        ->and((float) $dishes['Tinolang Manok']->price)->toBe(85.0)
        ->and($dishes['Tinolang Manok']->is_bestseller)->toBeTrue()
        ->and((float) $dishes['Kinilaw na Tuna']->price)->toBe(120.0)
        ->and((float) $dishes['Durian Shake']->price)->toBe(75.0);

    $this->assertDatabaseHas('reviews', [
        'eatery_id' => $eatery->id,
        'user_id' => $tita->id,
        'rating' => 5,
        'body' => 'Lami kaayo! Balik-balikan ni Tita Pastores.',
    ]);

    $otherEateries = Eatery::query()
        ->with('permit')
        ->withCount(['dishes', 'features', 'reviews'])
        ->where('id', '!=', $eatery->id)
        ->get();

    expect($otherEateries)->toHaveCount(14)
        ->and($otherEateries->where('is_open', false))->toHaveCount(3);

    foreach ($otherEateries as $otherEatery) {
        expect($otherEatery->permit)->not->toBeNull()
            ->and($otherEatery->permit->permit_no)->toMatch('/^DVO-\d{4}-\d{4}$/')
            ->and($otherEatery->dishes_count)->toBeGreaterThanOrEqual(2)
            ->and($otherEatery->dishes_count)->toBeLessThanOrEqual(5)
            ->and($otherEatery->features_count)->toBeGreaterThanOrEqual(1)
            ->and($otherEatery->features_count)->toBeLessThanOrEqual(3);

        if ($otherEatery->is_open) {
            expect($otherEatery->reviews_count)->toBeGreaterThanOrEqual(0)
                ->and($otherEatery->reviews_count)->toBeLessThanOrEqual(6);
        } else {
            expect($otherEatery->reviews_count)->toBe(0);
        }
    }

    $expiredPermit = Permit::query()->where('permit_no', 'DVO-2026-0001')->firstOrFail();

    expect($expiredPermit->expires_on->toDateString())->toBe(today()->subDay()->toDateString());

    expect(Review::query()->whereBetween('rating', [1, 5])->count())->toBe(Review::query()->count());
});
