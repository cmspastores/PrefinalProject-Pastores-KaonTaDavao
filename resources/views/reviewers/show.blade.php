@extends('layouts.app')

@section('title', $reviewer->name.' | Davao Eats')

@section('content')
    <section class="space-y-6">
        <div class="space-y-2">
            <a class="text-sm text-emerald-700 hover:underline dark:text-emerald-300" href="{{ route('eateries.index') }}">← All eateries</a>
            <h1 class="text-3xl font-bold">{{ $reviewer->name }}</h1>
            <p class="text-stone-600 dark:text-stone-300">
                {{ $reviewer->reviews_count }} reviews written ·
                @if ($reviewer->reviews_count > 0)
                    {{ number_format((float) $reviewer->reviews_avg_rating, 1) }} / 5 average rating
                @else
                    No ratings yet
                @endif
            </p>
        </div>

        <div class="space-y-4">
            @forelse ($reviewer->reviews as $review)
                <article class="space-y-2 rounded-xl border border-stone-200 bg-white p-5 dark:border-stone-800 dark:bg-stone-900">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h2 class="font-semibold">
                            <a class="hover:text-emerald-700 dark:hover:text-emerald-300" href="{{ route('eateries.show', $review->eatery->slug) }}">{{ $review->eatery->name }}</a>
                            <span class="font-normal text-stone-500">in</span>
                            <a class="font-normal hover:text-emerald-700 dark:hover:text-emerald-300" href="{{ route('districts.show', $review->eatery->district) }}">{{ $review->eatery->district->name }}</a>
                        </h2>
                        <x-rating :rating="$review->rating" class="text-sm text-amber-700 dark:text-amber-300" />
                    </div>
                    <p class="text-stone-700 dark:text-stone-200">{{ $review->body }}</p>
                </article>
            @empty
                <p class="rounded-xl border border-dashed border-stone-300 p-8 text-center text-stone-600 dark:border-stone-700 dark:text-stone-300">This reviewer has not written any reviews.</p>
            @endforelse
        </div>
    </section>
@endsection
