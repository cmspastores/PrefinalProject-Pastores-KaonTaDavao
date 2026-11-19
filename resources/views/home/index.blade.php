@extends('layouts.app')

@section('title', 'Davao Eats')

@section('content')
    <div class="space-y-10">
        <section class="space-y-6 rounded-2xl bg-emerald-900 px-6 py-12 text-white sm:px-10">
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-200">A local guide to Davao</p>
            <h1 class="max-w-2xl text-4xl font-bold">Find your next favorite eatery.</h1>
            <p class="max-w-2xl text-emerald-100">Explore neighborhood restaurants, browse their dishes, and read reviews from the community.</p>
            <div class="flex flex-wrap gap-3">
                <a class="rounded-lg bg-white px-5 py-2.5 font-semibold text-emerald-900 hover:bg-emerald-50" href="{{ route('eateries.index') }}">Browse eateries</a>
                <a class="rounded-lg border border-emerald-200 px-5 py-2.5 font-semibold text-white hover:bg-emerald-800" href="{{ route('districts.index') }}">Explore districts</a>
            </div>
        </section>

        <section aria-label="Davao Eats statistics" class="grid gap-4 sm:grid-cols-3">
            <article class="rounded-xl border border-stone-200 bg-white p-5 dark:border-stone-800 dark:bg-stone-900">
                <p class="text-3xl font-bold">{{ $eateryCount }}</p>
                <h2 class="mt-1 text-sm text-stone-600 dark:text-stone-300">Eateries</h2>
            </article>
            <article class="rounded-xl border border-stone-200 bg-white p-5 dark:border-stone-800 dark:bg-stone-900">
                <p class="text-3xl font-bold">{{ $districtCount }}</p>
                <h2 class="mt-1 text-sm text-stone-600 dark:text-stone-300">Districts</h2>
            </article>
            <article class="rounded-xl border border-stone-200 bg-white p-5 dark:border-stone-800 dark:bg-stone-900">
                <p class="text-3xl font-bold">{{ $reviewCount }}</p>
                <h2 class="mt-1 text-sm text-stone-600 dark:text-stone-300">Reviews</h2>
            </article>
        </section>

        <section class="space-y-5">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Community favorites</p>
                <h2 class="mt-1 text-2xl font-bold">Top Rated</h2>
            </div>
            @if ($topRatedEateries->isEmpty())
                <p class="text-stone-600 dark:text-stone-300">No rated eateries yet.</p>
            @else
                <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($topRatedEateries as $eatery)
                        <x-eatery-card :eatery="$eatery" />
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
