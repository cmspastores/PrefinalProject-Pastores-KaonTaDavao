<?php

namespace App\Http\Controllers;

use App\Models\District;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistrictController extends Controller
{
    public function index(): View
    {
        $districts = District::query()
            ->withCount(['eateries', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(12);

        return view('districts.index', [
            'districts' => $districts,
        ]);
    }

    public function show(District $district): View
    {
        $district->load([
            'eateries' => function (HasMany $query): void {
                $query->with(['district', 'features'])
                    ->withCount(['reviews', 'dishes'])
                    ->withAvg('reviews', 'rating')
                    ->orderBy('name')
                    ->orderBy('id');
            },
        ])->loadCount(['eateries', 'reviews'])->loadAvg('reviews', 'rating');

        return view('districts.show', [
            'district' => $district,
        ]);
    }
}
