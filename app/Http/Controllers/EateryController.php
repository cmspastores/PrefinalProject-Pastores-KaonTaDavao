<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilterEateriesRequest;
use App\Http\Requests\StoreEateryRequest;
use App\Http\Requests\UpdateEateryRequest;
use App\Models\District;
use App\Models\Eatery;
use App\Models\Feature;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EateryController extends Controller
{
    private const EATERY_ATTRIBUTES = [
        'district_id',
        'name',
        'address',
        'price_range',
        'specialty',
        'is_open',
    ];

    public function index(FilterEateriesRequest $request): View
    {
        $filters = $request->validated();
        $searchTerm = $filters['q'] ?? null;
        $districtId = $filters['district'] ?? null;
        $featureName = $filters['feature'] ?? null;

        $eateries = Eatery::query()
            ->with(['district', 'features'])
            ->withCount(['reviews', 'dishes'])
            ->withAvg('reviews', 'rating')
            ->when($searchTerm !== null && $searchTerm !== '', function (Builder $query) use ($searchTerm): void {
                $query->where(function (Builder $query) use ($searchTerm): void {
                    $query->where('name', 'like', '%'.$searchTerm.'%')
                        ->orWhere('specialty', 'like', '%'.$searchTerm.'%');
                });
            })
            ->when($districtId !== null && $districtId !== '', function (Builder $query) use ($districtId): void {
                $query->where('district_id', $districtId);
            })
            ->when($featureName !== null && $featureName !== '', function (Builder $query) use ($featureName): void {
                $query->whereHas('features', function (Builder $featureQuery) use ($featureName): void {
                    $featureQuery->where('features.name', $featureName);
                });
            })
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(6)
            ->withQueryString();

        return view('eateries.index', [
            'eateries' => $eateries,
            'districts' => District::query()->orderBy('name')->get(),
            'features' => Feature::query()->orderBy('name')->get(),
            'filters' => [
                'q' => $searchTerm ?? '',
                'district' => $districtId ?? '',
                'feature' => $featureName ?? '',
            ],
        ]);
    }

    public function create(): View
    {
        return view('eateries.form', [
            'districts' => District::query()->orderBy('name')->get(),
            'features' => Feature::query()->orderBy('name')->get(),
            'eatery' => null,
        ]);
    }

    public function store(StoreEateryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $eatery = DB::transaction(function () use ($validated): Eatery {
            $attributes = Arr::only($validated, self::EATERY_ATTRIBUTES);
            $attributes['slug'] = $this->slugForName($validated['name']);
            $eatery = Eatery::create($attributes);
            $this->syncFeaturesAndPermit($eatery, $validated);

            return $eatery;
        });

        return redirect()
            ->route('eateries.show', $eatery->slug)
            ->with('status', 'Eatery created.');
    }

    public function show(Eatery $eatery): View
    {
        $eatery->load([
            'district',
            'dishes' => fn (HasMany $query) => $query->orderBy('name'),
            'features',
            'permit',
            'reviews' => function (HasMany $query): void {
                $query->with('user')
                    ->orderByDesc('created_at')
                    ->orderByDesc('id');
            },
        ])->loadCount('reviews')->loadAvg('reviews', 'rating');

        return view('eateries.show', [
            'eatery' => $eatery,
            'reviewers' => User::query()->orderBy('name')->get(),
        ]);
    }

    public function edit(Eatery $eatery): View
    {
        $eatery->load(['features', 'permit']);

        return view('eateries.form', [
            'districts' => District::query()->orderBy('name')->get(),
            'features' => Feature::query()->orderBy('name')->get(),
            'eatery' => $eatery,
        ]);
    }

    public function update(UpdateEateryRequest $request, Eatery $eatery): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($eatery, $validated): void {
            $attributes = Arr::only($validated, self::EATERY_ATTRIBUTES);
            $attributes['slug'] = $this->slugForName($validated['name'], $eatery->id);
            $eatery->update($attributes);
            $this->syncFeaturesAndPermit($eatery, $validated);
        });

        return redirect()
            ->route('eateries.show', $eatery->slug)
            ->with('status', 'Eatery updated.');
    }

    public function destroy(Eatery $eatery): RedirectResponse
    {
        DB::transaction(function () use ($eatery): void {
            $eatery->features()->detach();
            $eatery->reviews()->delete();
            $eatery->dishes()->delete();
            $eatery->permit()->delete();
            $eatery->delete();
        });

        return redirect()
            ->route('eateries.index')
            ->with('status', 'Eatery deleted.');
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function syncFeaturesAndPermit(Eatery $eatery, array $validated): void
    {
        $eatery->features()->sync($validated['features']);
        $eatery->permit()->updateOrCreate([], Arr::only($validated, ['permit_no', 'expires_on']));
    }

    private function slugForName(string $name, ?int $ignoreEateryId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'eatery';
        $suffix = 1;

        do {
            $slug = $suffix === 1 ? $baseSlug : $baseSlug.'-'.$suffix;
            $suffix++;

            $query = Eatery::query()->where('slug', $slug);

            if ($ignoreEateryId !== null) {
                $query->where('id', '!=', $ignoreEateryId);
            }
        } while ($query->exists());

        return $slug;
    }
}
