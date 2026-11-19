<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;

class ReviewerController extends Controller
{
    public function show(User $user): View
    {
        $user->load('reviews.eatery.district')
            ->loadCount('reviews')
            ->loadAvg('reviews', 'rating');

        return view('reviewers.show', [
            'reviewer' => $user,
        ]);
    }
}
