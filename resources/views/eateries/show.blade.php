@extends('layouts.app')

@section('title', $eatery->name.' | Davao Eats')

@section('content')
    <article class="space-y-8">
        <header class="space-y-4">
            <a class="text-sm text-emerald-700 hover:underline dark:text-emerald-300" href="{{ route('eateries.index') }}">← All eateries</a>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-stone-600 dark:text-stone-300">
                        <a class="hover:underline" href="{{ route('districts.show', $eatery->district) }}">{{ $eatery->district->name }}</a>
                        <span aria-hidden="true">·</span>
                        <span class="capitalize">{{ $eatery->price_range }}</span>
                        <span aria-hidden="true">·</span>
                        {{ $eatery->is_open ? 'Open' : 'Closed' }}
                    </p>
                    <h1 class="mt-2 text-3xl font-bold">{{ $eatery->name }}</h1>
                    <p class="mt-2 text-stone-600 dark:text-stone-300">{{ $eatery->address }}</p>
                    @if ($eatery->specialty)
                        <p class="mt-1 text-stone-600 dark:text-stone-300">Specialty: {{ $eatery->specialty }}</p>
                    @endif
                </div>
                <div class="flex flex-wrap gap-2">
                    <a class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium hover:bg-white dark:border-stone-700 dark:hover:bg-stone-900" href="{{ route('eateries.edit', $eatery) }}">Edit eatery</a>
                    <form method="POST" action="{{ route('eateries.destroy', $eatery) }}">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950" type="submit">Delete eatery</button>
                    </form>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($eatery->features as $feature)
                    <a class="rounded-full bg-emerald-50 px-3 py-1 text-sm text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200" href="{{ route('eateries.index', ['feature' => $feature->id]) }}">{{ $feature->name }}</a>
                @endforeach
            </div>
            <p class="text-sm text-stone-600 dark:text-stone-300">
                {{ $eatery->reviews_count }} reviews
                <span aria-hidden="true">·</span>
                @if ($eatery->reviews_count > 0)
                    {{ number_format((float) $eatery->reviews_avg_rating, 1) }} / 5 average rating
                @else
                    No ratings yet
                @endif
            </p>
        </header>

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="space-y-3 lg:col-span-2">
                <h2 class="text-xl font-semibold">Dishes ({{ $eatery->dishes->count() }})</h2>
                @forelse ($eatery->dishes as $dish)
                    <article class="flex items-center justify-between gap-4 rounded-lg border border-stone-200 bg-white p-4 dark:border-stone-800 dark:bg-stone-900">
                        <div>
                            <h3 class="font-medium">{{ $dish->name }}</h3>
                            @if ($dish->is_bestseller)
                                <span class="text-sm text-amber-700 dark:text-amber-300">Bestseller</span>
                            @endif
                        </div>
                        <span>{{ number_format((float) $dish->price, 2) }}</span>
                    </article>
                @empty
                    <p class="rounded-lg border border-dashed border-stone-300 p-4 text-stone-600 dark:border-stone-700 dark:text-stone-300">No dishes listed yet.</p>
                @endforelse
            </section>

            <aside class="space-y-3">
                <h2 class="text-xl font-semibold">Permit</h2>
                <div class="rounded-lg border border-stone-200 bg-white p-4 dark:border-stone-800 dark:bg-stone-900">
                    @if ($eatery->permit)
                        <p><span class="font-medium">Permit:</span> {{ $eatery->permit->permit_no }}</p>
                        <p class="mt-2 text-sm text-stone-600 dark:text-stone-300">Expires {{ $eatery->permit->expires_on->toFormattedDateString() }}</p>
                    @else
                        <p class="text-stone-600 dark:text-stone-300">No permit details listed.</p>
                    @endif
                </div>
            </aside>
        </div>

        <section class="space-y-4">
            <h2 class="text-xl font-semibold">Reviews ({{ $eatery->reviews_count }})</h2>
            @forelse ($eatery->reviews as $review)
                <article class="rounded-lg border border-stone-200 bg-white p-5 dark:border-stone-800 dark:bg-stone-900">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <a class="font-semibold hover:text-emerald-700 dark:hover:text-emerald-300" href="{{ route('reviewers.show', $review->user) }}">{{ $review->user->name }}</a>
                        <span class="text-sm text-amber-700 dark:text-amber-300">{{ $review->rating }} / 5</span>
                    </div>
                    <p class="mt-3 text-stone-700 dark:text-stone-200">{{ $review->body }}</p>
                    <form class="mt-3" method="POST" action="{{ route('reviews.destroy', $review) }}">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm font-medium text-red-700 hover:underline dark:text-red-300" type="submit">Delete review</button>
                    </form>
                </article>
            @empty
                <p class="rounded-lg border border-dashed border-stone-300 p-4 text-stone-600 dark:border-stone-700 dark:text-stone-300">No reviews yet.</p>
            @endforelse
        </section>

        <section class="space-y-4 border-t border-stone-200 pt-6 dark:border-stone-800">
            <h2 class="text-xl font-semibold">Add a review</h2>
            @if ($reviewers->isEmpty())
                <p class="text-stone-600 dark:text-stone-300">A reviewer account is needed before a review can be added.</p>
            @else
                <form class="grid gap-4 rounded-lg border border-stone-200 bg-white p-5 dark:border-stone-800 dark:bg-stone-900" method="POST" action="{{ route('eateries.reviews.store', $eatery) }}">
                    @csrf
                    <label class="grid gap-1 text-sm font-medium">
                        <span>Reviewer</span>
                        <select class="rounded-lg border border-stone-300 bg-transparent px-3 py-2 dark:border-stone-700" name="user_id" required>
                            @foreach ($reviewers as $reviewer)
                                <option value="{{ $reviewer->id }}" @selected(old('user_id') == $reviewer->id)>{{ $reviewer->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-medium">
                        <span>Rating</span>
                        <select class="rounded-lg border border-stone-300 bg-transparent px-3 py-2 dark:border-stone-700" name="rating" required>
                            @foreach (range(1, 5) as $rating)
                                <option value="{{ $rating }}" @selected(old('rating', 5) == $rating)>{{ $rating }} / 5</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-medium">
                        <span>Review</span>
                        <textarea class="rounded-lg border border-stone-300 bg-transparent px-3 py-2 dark:border-stone-700" name="body" rows="4" required>{{ old('body') }}</textarea>
                    </label>
                    <button class="justify-self-start rounded-lg bg-emerald-700 px-5 py-2.5 font-semibold text-white hover:bg-emerald-800" type="submit">Submit review</button>
                </form>
            @endif
        </section>
    </article>
@endsection
