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
    ]);

    $eatery->permit()->create([
        'permit_no' => 'PERMIT-01',
        'expires_on' => '2027-01-15',
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

    $eatery->reviews()->createMany([
        ['user_id' => $reviewer->id, 'rating' => 5, 'body' => 'Excellent food.'],
        ['user_id' => $reviewer->id, 'rating' => 3, 'body' => 'Good food.'],
    ]);

    $wasPreventingLazyLoading = Model::preventsLazyLoading();
    Model::preventLazyLoading(true);

    try {
        $this->get(route('eateries.index', ['feature' => $feature->id]))
            ->assertOk()
            ->assertSee('Harbor Kitchen')
            ->assertDontSee('Northside Cafe')
            ->assertSee('Northside')
            ->assertSee('Outdoor seating')
            ->assertSee('4.0 / 5');

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
            ->assertSee('2 reviews');

        $this->get(route('eateries.show', $eatery->slug))
            ->assertOk()
            ->assertSee('Grilled Fish')
            ->assertSee('PERMIT', false)
            ->assertSee('Sam Reviewer');

        $this->get(route('reviewers.show', $reviewer))
            ->assertOk()
            ->assertSee('Harbor Kitchen')
            ->assertSee('Northside')
            ->assertSee('2 reviews')
            ->assertSee('4.0 / 5');
    } finally {
        Model::preventLazyLoading($wasPreventingLazyLoading);
    }
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
        ->get(route('eateries.index', ['feature' => 'invalid']))
        ->assertRedirect(route('eateries.index'))
        ->assertSessionHasErrors([
            'feature' => 'Choose a whole-number feature ID.',
        ]);
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
