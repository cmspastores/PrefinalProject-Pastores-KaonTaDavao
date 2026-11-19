@extends('layouts.app')

@section('title', 'Eateries | Davao Eats')

@section('content')
    <section class="space-y-6">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Explore Davao</p>
                <h1 class="mt-1 text-3xl font-bold">Eateries</h1>
                <p class="mt-2 text-stone-600 dark:text-stone-300">Browse local places, specialties, and diner ratings.</p>
            </div>
            <a class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-medium hover:bg-stone-100 dark:border-stone-700 dark:hover:bg-stone-800" href="{{ route('districts.index') }}">Browse districts</a>
        </div>

        <form class="grid gap-4 rounded-xl border border-stone-200 bg-white p-5 sm:grid-cols-2 lg:grid-cols-4 dark:border-stone-800 dark:bg-stone-900" method="GET" action="{{ route('eateries.index') }}">
            <label class="grid gap-1 text-sm font-medium">
                <span>Search name or specialty</span>
                <input class="rounded-lg border border-stone-300 bg-transparent px-3 py-2 dark:border-stone-700" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Try noodles">
            </label>
            <label class="grid gap-1 text-sm font-medium">
                <span>District</span>
                <select class="rounded-lg border border-stone-300 bg-transparent px-3 py-2 dark:border-stone-700" name="district">
                    <option value="">All districts</option>
                    @foreach ($districts as $district)
                        <option value="{{ $district->id }}" @selected((string) $filters['district'] === (string) $district->id)>{{ $district->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-1 text-sm font-medium">
                <span>Feature</span>
                <select class="rounded-lg border border-stone-300 bg-transparent px-3 py-2 dark:border-stone-700" name="feature">
                    <option value="">All features</option>
                    @foreach ($features as $feature)
                        <option value="{{ $feature->name }}" @selected($filters['feature'] === $feature->name)>{{ $feature->name }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex items-end gap-2">
                <button class="rounded-lg bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800" type="submit">Filter</button>
                <a class="rounded-lg border border-stone-300 px-4 py-2 font-medium hover:bg-stone-100 dark:border-stone-700 dark:hover:bg-stone-800" href="{{ route('eateries.index') }}">Clear</a>
            </div>
        </form>

        @if ($eateries->isEmpty())
            <p class="rounded-xl border border-dashed border-stone-300 p-8 text-center text-stone-600 dark:border-stone-700 dark:text-stone-300">No eateries match your filters.</p>
        @else
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($eateries as $eatery)
                    <x-eatery-card :eatery="$eatery" />
                @endforeach
            </div>
            {{ $eateries->links() }}
        @endif
    </section>
@endsection
