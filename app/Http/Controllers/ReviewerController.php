<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReviewerController extends Controller
{
    public function show(User $user): View
    {
        $user->load([
            'reviews' => function (HasMany $query): void {
                $query->with('eatery.district')
                    ->orderByDesc('created_at')
                    ->orderByDesc('id');
            },
        ])
            ->loadCount('reviews')
            ->loadAvg('reviews', 'rating');

        return view('reviewers.show', [
            'reviewer' => $user,
        ]);
    }
}
