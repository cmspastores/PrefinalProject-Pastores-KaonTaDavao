@extends('layouts.app')

@section('title', 'Districts | Davao Eats')

@section('content')
    <section class="space-y-6">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Explore by area</p>
            <h1 class="mt-1 text-3xl font-bold">Districts</h1>
        </div>

        <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($districts as $district)
                <article class="space-y-3 rounded-xl border border-stone-200 bg-white p-5 shadow-sm dark:border-stone-800 dark:bg-stone-900">
                    <h2 class="text-lg font-semibold">
                        <a class="hover:text-emerald-700 dark:hover:text-emerald-300" href="{{ route('districts.show', $district) }}">{{ $district->name }}</a>
                    </h2>
                    <p class="text-sm text-stone-600 dark:text-stone-300">{{ $district->eateries_count }} eateries · {{ $district->reviews_count }} reviews</p>
                    <p class="text-sm text-stone-600 dark:text-stone-300">
                        @if ($district->reviews_count > 0)
                            {{ number_format((float) $district->reviews_avg_rating, 1) }} / 5 average rating
                        @else
                            No ratings yet
                        @endif
                    </p>
                </article>
            @empty
                <p class="text-stone-600 dark:text-stone-300">No districts have been added yet.</p>
            @endforelse
        </div>

        {{ $districts->links() }}
    </section>
@endsection
