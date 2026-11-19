<?php

use App\Models\District;
use App\Models\Eatery;
use App\Models\Feature;
use App\Models\Permit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders eager-loaded relationship names and aggregates on browse pages', function () {
    $district = District::create([
        'name' => 'Northside',
    ]);

    $eatery = $district->eateries()->create([
        'name' => 'Harbor Kitchen',
        'slug' => 'harbor-kitchen',
        'address' => '12 Bay Road',
        'price_range' => 'mid',
    ]);

    $eatery->dishes()->create([
        'name' => 'Grilled Fish',
        'price' => 12.50,
        'is_bestseller' => true,
    ]);

    $eatery->permit()->create([
        'permit_no' => 'PERMIT-01',
        'expires_on' => '2020-01-15',
    ]);

    $feature = Feature::create([
        'name' => 'Outdoor seating',
    ]);
    $eatery->features()->attach($feature);

    $otherFeature = Feature::create([
        'name' => 'Takeaway',
    ]);
    $otherEatery = $district->eateries()->create([
        'name' => 'Northside Cafe',
        'slug' => 'northside-cafe',
        'address' => '8 Hill Street',
        'price_range' => 'budget',
    ]);
    $otherEatery->features()->attach($otherFeature);

    $reviewer = User::create([
        'name' => 'Sam Reviewer',
        'email' => 'sam@example.com',
        'password' => 'password',
    ]);

    $olderReview = $eatery->reviews()->create([
        'user_id' => $reviewer->id,
        'rating' => 5,
        'body' => 'Excellent food.',
    ]);
    $olderReview->forceFill(['created_at' => '2025-01-15 12:00:00'])->save();

    $newerReview = $eatery->reviews()->create([
        'user_id' => $reviewer->id,
        'rating' => 3,
        'body' => 'Good food.',
    ]);
    $newerReview->forceFill(['created_at' => '2026-01-15 12:00:00'])->save();

    $wasPreventingLazyLoading = Model::preventsLazyLoading();
    Model::preventLazyLoading(true);

    try {
        $this->get(route('eateries.index', ['feature' => $feature->name]))
            ->assertOk()
            ->assertSee('Harbor Kitchen')
            ->assertDontSee('Northside Cafe')
            ->assertSee('Northside')
            ->assertSee('Outdoor seating')
            ->assertSee('₱₱')
            ->assertSee('4.0 ★ (2 reviews)')
            ->assertSee(route('eateries.index', ['feature' => $feature->name]), false);

        $this->get(route('eateries.index'))
            ->assertOk()
            ->assertSee('Northside Cafe');

        $this->get(route('districts.index'))
            ->assertOk()
            ->assertSee('Northside')
            ->assertSee('2 reviews')
            ->assertSee('4.0 / 5');

        $this->get(route('districts.show', $district))
            ->assertOk()
            ->assertSee('Harbor Kitchen')
            ->assertSee('Northside Cafe')
            ->assertSee('2 reviews in Northside');

        $this->get(route('eateries.show', $eatery->slug))
            ->assertOk()
            ->assertSee('Grilled Fish')
            ->assertSee('₱12.50')
            ->assertSee('Bestseller')
            ->assertSee('Permit expired')
            ->assertSee('PERMIT', false)
            ->assertSee('Sam Reviewer')
            ->assertSee(route('reviewers.show', $reviewer), false)
            ->assertSee('Delete review')
            ->assertSee('Jan 15, 2026');

        $eateryPage = $this->get(route('eateries.show', $eatery->slug))->getContent();

        expect(strpos($eateryPage, 'Good food.'))->toBeLessThan(strpos($eateryPage, 'Excellent food.'));

        $this->get(route('reviewers.show', $reviewer))
            ->assertOk()
            ->assertSee('Harbor Kitchen')
            ->assertSee('Northside')
            ->assertSee('2 reviews written')
            ->assertSee('4.0 / 5');

        $reviewerPage = $this->get(route('reviewers.show', $reviewer))->getContent();

        expect(strpos($reviewerPage, 'Good food.'))->toBeLessThan(strpos($reviewerPage, 'Excellent food.'));
    } finally {
        Model::preventLazyLoading($wasPreventingLazyLoading);
    }
});

it('shows a closed badge and pauses reviews for closed eateries', function () {
    $district = District::create([
        'name' => 'Southside',
    ]);
    $eatery = $district->eateries()->create([
        'name' => 'Closed Kitchen',
        'slug' => 'closed-kitchen',
        'address' => '4 South Road',
        'price_range' => 'budget',
        'is_open' => false,
    ]);
    $reviewer = User::create([
        'name' => 'Review Writer',
        'email' => 'writer@example.com',
        'password' => 'password',
    ]);

    $eatery->reviews()->create([
        'user_id' => $reviewer->id,
        'rating' => 4,
        'body' => 'A past review.',
    ]);

    $this->get(route('eateries.index'))
        ->assertOk()
        ->assertSee('Closed');

    $this->get(route('eateries.show', $eatery))
        ->assertOk()
        ->assertSee('This eatery is closed. Reviews are paused.')
        ->assertDontSee('Submit review')
        ->assertDontSee('name="body"', false);
});

it('shows home totals and the three highest-rated reviewed eateries', function () {
    $centralDistrict = District::create([
        'name' => 'Central',
    ]);
    $northDistrict = District::create([
        'name' => 'North',
    ]);
    $topEatery = $centralDistrict->eateries()->create([
        'name' => 'Top Table',
        'slug' => 'top-table',
        'address' => '1 Main Street',
        'price_range' => 'mid',
    ]);
    $secondEatery = $centralDistrict->eateries()->create([
        'name' => 'Second Spoon',
        'slug' => 'second-spoon',
        'address' => '2 Main Street',
        'price_range' => 'mid',
    ]);
    $thirdEatery = $northDistrict->eateries()->create([
        'name' => 'Third Plate',
        'slug' => 'third-plate',
        'address' => '3 Main Street',
        'price_range' => 'budget',
    ]);
    $unratedEatery = $northDistrict->eateries()->create([
        'name' => 'Unrated Eatery',
        'slug' => 'unrated-eatery',
        'address' => '4 Main Street',
        'price_range' => 'budget',
    ]);
    $reviewer = User::create([
        'name' => 'Rating Reviewer',
        'email' => 'rating@example.com',
        'password' => 'password',
    ]);

    $topEatery->reviews()->create(['user_id' => $reviewer->id, 'rating' => 5, 'body' => 'Excellent.']);
    $secondEatery->reviews()->create(['user_id' => $reviewer->id, 'rating' => 4, 'body' => 'Very good.']);
    $thirdEatery->reviews()->create(['user_id' => $reviewer->id, 'rating' => 3, 'body' => 'Good.']);

    $response = $this->get(route('home'))->assertOk();

    $response->assertViewHas('eateryCount', 4)
        ->assertViewHas('districtCount', 2)
        ->assertViewHas('reviewCount', 3)
        ->assertSee('Top Rated')
        ->assertSee('Top Table')
        ->assertSee('Second Spoon')
        ->assertSee('Third Plate')
        ->assertDontSee($unratedEatery->name);

    $content = $response->getContent();

    expect(strpos($content, 'Top Table'))->toBeLessThan(strpos($content, 'Second Spoon'))
        ->and(strpos($content, 'Second Spoon'))->toBeLessThan(strpos($content, 'Third Plate'));
});

it('creates and updates an eatery, its permit, and its selected features', function () {
    $district = District::create([
        'name' => 'Downtown',
    ]);
    $feature = Feature::create([
        'name' => 'Family friendly',
    ]);

    $this->get(route('eateries.create'))
        ->assertOk()
        ->assertSee('Permit number')
        ->assertSee('Family friendly');

    $this->post(route('eateries.store'), [
        'district_id' => $district->id,
        'name' => 'Form Eatery',
        'slug' => 'form-eatery',
        'address' => '15 Market Street',
        'price_range' => 'budget',
        'specialty' => '',
        'is_open' => '1',
        'features' => [$feature->id],
        'permit_no' => 'PERMIT-100',
        'expires_on' => '2027-01-15',
    ])->assertRedirect(route('eateries.show', 'form-eatery'));

    $eatery = Eatery::where('slug', 'form-eatery')->firstOrFail();

    $this->assertDatabaseHas('permits', [
        'eatery_id' => $eatery->id,
        'permit_no' => 'PERMIT-100',
    ]);
    $this->assertDatabaseHas('eatery_feature', [
        'eatery_id' => $eatery->id,
        'feature_id' => $feature->id,
    ]);

    $this->get(route('eateries.edit', 'form-eatery'))
        ->assertOk()
        ->assertSee('PERMIT-100')
        ->assertSee('name="_method" value="PUT"', false);

    $this->put(route('eateries.update', 'form-eatery'), [
        'district_id' => $district->id,
        'name' => 'Updated Eatery',
        'slug' => 'updated-eatery',
        'address' => '16 Market Street',
        'price_range' => 'splurge',
        'specialty' => 'Seafood',
        'is_open' => '0',
        'features' => [],
        'permit_no' => 'PERMIT-101',
        'expires_on' => '2028-01-15',
    ])->assertRedirect(route('eateries.show', 'updated-eatery'));

    $this->assertDatabaseHas('eateries', [
        'id' => $eatery->id,
        'slug' => 'updated-eatery',
        'is_open' => 0,
    ]);
    $permit = Permit::query()->where('eatery_id', $eatery->id)->firstOrFail();

    expect($permit->permit_no)->toBe('PERMIT-101')
        ->and($permit->expires_on->toDateString())->toBe('2028-01-15');
    $this->assertDatabaseMissing('eatery_feature', [
        'eatery_id' => $eatery->id,
        'feature_id' => $feature->id,
    ]);
});

it('shows custom validation messages and restores submitted values', function () {
    $district = District::create([
        'name' => 'Downtown',
    ]);

    $this->from(route('eateries.create'))
        ->followingRedirects()
        ->post(route('eateries.store'), [
            'district_id' => $district->id,
            'name' => 'Error Eatery',
            'slug' => 'error-eatery',
            'address' => '15 Market Street',
            'price_range' => 'luxury',
            'specialty' => '',
            'is_open' => '1',
            'permit_no' => 'PERMIT-ERROR',
        ])
        ->assertOk()
        ->assertSee('Choose budget, mid, or splurge for the price range.')
        ->assertSee('Enter an expiry date when a permit number is provided.')
        ->assertSee('value="Error Eatery"', false)
        ->assertSee('value="luxury"', false)
        ->assertSee('value="PERMIT-ERROR"', false);
});

it('validates the feature filter using its form request', function () {
    $this->from(route('eateries.index'))
        ->get(route('eateries.index', ['feature' => 'not-a-feature']))
        ->assertRedirect(route('eateries.index'))
        ->assertSessionHasErrors([
            'feature' => 'The selected feature could not be found.',
        ]);
});

it('combines eatery filters and keeps them in six-item pagination links', function () {
    $district = District::create([
        'name' => 'Central',
    ]);
    $otherDistrict = District::create([
        'name' => 'North',
    ]);
    $feature = Feature::create([
        'name' => 'Patio seating',
    ]);
    $otherFeature = Feature::create([
        'name' => 'Delivery',
    ]);

    for ($index = 1; $index <= 6; $index++) {
        $eatery = $district->eateries()->create([
            'name' => sprintf('Udon House %02d', $index),
            'slug' => sprintf('udon-house-%02d', $index),
            'address' => '1 Main Street',
            'price_range' => 'mid',
        ]);
        $eatery->features()->attach($feature);
    }

    $specialtyMatch = $district->eateries()->create([
        'name' => 'West End Grill',
        'slug' => 'west-end-grill',
        'address' => '7 Main Street',
        'price_range' => 'mid',
        'specialty' => 'Udon noodles',
    ]);
    $specialtyMatch->features()->attach($feature);

    $districtMismatch = $otherDistrict->eateries()->create([
        'name' => 'Outer District Cafe',
        'slug' => 'outer-district-cafe',
        'address' => '8 North Street',
        'price_range' => 'budget',
        'specialty' => 'Udon noodles',
    ]);
    $districtMismatch->features()->attach($feature);

    $featureMismatch = $district->eateries()->create([
        'name' => 'No Patio Cafe',
        'slug' => 'no-patio-cafe',
        'address' => '9 Main Street',
        'price_range' => 'budget',
        'specialty' => 'Udon noodles',
    ]);
    $featureMismatch->features()->attach($otherFeature);

    $filters = [
        'q' => 'Udon',
        'district' => $district->id,
        'feature' => $feature->name,
    ];

    $this->get(route('eateries.index', $filters))
        ->assertOk()
        ->assertSee('Udon House 01')
        ->assertSee('Udon House 06')
        ->assertDontSee('West End Grill')
        ->assertDontSee('Outer District Cafe')
        ->assertDontSee('No Patio Cafe')
        ->assertSee('name="q" value="Udon"', false)
        ->assertSee('value="'.$district->id.'" selected', false)
        ->assertSee('value="'.$feature->name.'" selected', false)
        ->assertSee(route('eateries.index', $filters + ['page' => 2]));

    $this->get(route('eateries.index', $filters + ['page' => 2]))
        ->assertOk()
        ->assertSee('West End Grill')
        ->assertDontSee('Udon House 01');

    $this->get(route('eateries.index', ['q' => 'nothing-matches']))
        ->assertOk()
        ->assertSee('No eateries match your filters.');
});

it('binds eateries by slug and stores and deletes reviews through named routes', function () {
    $district = District::create([
        'name' => 'Downtown',
    ]);
    $eatery = $district->eateries()->create([
        'name' => 'Carinderia ni Dela Cruz',
        'slug' => 'carinderia-ni-dela-cruz',
        'address' => '15 Market Street',
        'price_range' => 'budget',
    ]);
    $reviewer = User::create([
        'name' => 'Pat Reviewer',
        'email' => 'pat@example.com',
        'password' => 'password',
    ]);

    $this->get(route('eateries.show', $eatery))
        ->assertOk()
        ->assertSee('name="_method" value="DELETE"', false)
        ->assertSee(route('eateries.reviews.store', $eatery), false);

    $this->get(route('eateries.show', 'unknown-eatery'))->assertNotFound();

    $this->post(route('eateries.reviews.store', $eatery), [
        'user_id' => $reviewer->id,
        'rating' => 5,
        'body' => 'Excellent food.',
    ])->assertRedirect(route('eateries.show', $eatery));

    $review = $eatery->reviews()->firstOrFail();

    $this->get(route('eateries.show', $eatery))
        ->assertOk()
        ->assertSee(route('reviews.destroy', $review), false)
        ->assertSee('name="_method" value="DELETE"', false);

    $this->assertDatabaseHas('reviews', [
        'id' => $review->id,
        'user_id' => $reviewer->id,
        'rating' => 5,
    ]);

    $this->delete(route('reviews.destroy', $review))
        ->assertRedirect(route('eateries.show', $eatery));

    $this->assertDatabaseMissing('reviews', [
        'id' => $review->id,
    ]);
});

it('supports partial eatery updates without replacing omitted relationships', function () {
    $district = District::create([
        'name' => 'Downtown',
    ]);
    $feature = Feature::create([
        'name' => 'Family friendly',
    ]);
    $eatery = $district->eateries()->create([
        'name' => 'Harbor Kitchen',
        'slug' => 'harbor-kitchen',
        'address' => '12 Bay Road',
        'price_range' => 'mid',
        'specialty' => 'Seafood',
    ]);
    $eatery->features()->attach($feature);
    $permit = $eatery->permit()->create([
        'permit_no' => 'PERMIT-200',
        'expires_on' => '2027-01-15',
    ]);

    $this->patch(route('eateries.update', $eatery), [
        'name' => 'Harbor Kitchen Updated',
    ])->assertRedirect(route('eateries.show', $eatery));

    $this->assertDatabaseHas('eateries', [
        'id' => $eatery->id,
        'name' => 'Harbor Kitchen Updated',
        'specialty' => 'Seafood',
    ]);
    $this->assertDatabaseHas('eatery_feature', [
        'eatery_id' => $eatery->id,
        'feature_id' => $feature->id,
    ]);
    $this->assertDatabaseHas('permits', [
        'id' => $permit->id,
        'permit_no' => 'PERMIT-200',
    ]);
});

it('deletes an eatery and its dependent records through its resource route', function () {
    $district = District::create([
        'name' => 'Downtown',
    ]);
    $feature = Feature::create([
        'name' => 'Family friendly',
    ]);
    $eatery = $district->eateries()->create([
        'name' => 'Harbor Kitchen',
        'slug' => 'harbor-kitchen',
        'address' => '12 Bay Road',
        'price_range' => 'mid',
    ]);
    $eatery->features()->attach($feature);
    $eatery->dishes()->create([
        'name' => 'Grilled Fish',
        'price' => 12.50,
    ]);
    $permit = $eatery->permit()->create([
        'permit_no' => 'PERMIT-300',
        'expires_on' => '2027-01-15',
    ]);
    $reviewer = User::create([
        'name' => 'Pat Reviewer',
        'email' => 'pat@example.com',
        'password' => 'password',
    ]);
    $review = $eatery->reviews()->create([
        'user_id' => $reviewer->id,
        'rating' => 4,
        'body' => 'Good food.',
    ]);

    $this->delete(route('eateries.destroy', $eatery))
        ->assertRedirect(route('eateries.index'));

    $this->assertDatabaseMissing('eateries', ['id' => $eatery->id]);
    $this->assertDatabaseMissing('eatery_feature', [
        'eatery_id' => $eatery->id,
        'feature_id' => $feature->id,
    ]);
    $this->assertDatabaseMissing('dishes', ['eatery_id' => $eatery->id]);
    $this->assertDatabaseMissing('permits', ['id' => $permit->id]);
    $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
});
