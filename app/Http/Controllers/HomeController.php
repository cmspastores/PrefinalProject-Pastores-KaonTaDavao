<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Eatery;
use App\Models\Review;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $topRatedEateries = Eatery::query()
            ->with(['district', 'features'])
            ->withCount(['reviews', 'dishes'])
            ->withAvg('reviews', 'rating')
            ->whereHas('reviews')
            ->orderByDesc('reviews_avg_rating')
            ->orderBy('name')
            ->limit(3)
            ->get();

        return view('home.index', [
            'eateryCount' => Eatery::query()->count(),
            'districtCount' => District::query()->count(),
            'reviewCount' => Review::query()->count(),
            'topRatedEateries' => $topRatedEateries,
        ]);
    }
}
