<?php

use App\Models\District;
use App\Models\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('persists eatery relationships and casts configured attributes', function () {
    $district = District::create([
        'name' => 'Downtown',
    ]);

    $eatery = $district->eateries()->create([
        'name' => 'Harbor Kitchen',
        'slug' => 'harbor-kitchen',
        'address' => '12 Bay Road',
        'price_range' => 'mid',
    ]);

    $permit = $eatery->permit()->create([
        'permit_no' => 'PERMIT-001',
        'expires_on' => '2027-01-15',
    ]);

    $dish = $eatery->dishes()->create([
        'name' => 'Grilled Fish',
        'price' => 12.50,
    ]);

    $feature = Feature::create([
        'name' => 'Outdoor seating',
    ]);
    $eatery->features()->attach($feature);

    $user = User::create([
        'name' => 'Sam User',
        'email' => 'sam@example.com',
        'password' => 'password',
    ]);

    $review = $eatery->reviews()->create([
        'user_id' => $user->id,
        'rating' => 5,
        'body' => 'Excellent food.',
    ]);

    $eatery->refresh();
    $dish->refresh();

    expect($eatery->is_open)->toBeTrue()
        ->and($dish->is_bestseller)->toBeFalse()
        ->and($permit->expires_on->toDateString())->toBe('2027-01-15')
        ->and($eatery->district->is($district))->toBeTrue()
        ->and($eatery->permit->is($permit))->toBeTrue()
        ->and($eatery->features->contains($feature))->toBeTrue()
        ->and($review->user->is($user))->toBeTrue()
        ->and($user->reviews->contains($review))->toBeTrue();
});
