@extends('layouts.app')

@section('title', $district->name.' | Davao Eats')

@section('content')
    <section class="space-y-6">
        <div class="space-y-3">
            <a class="text-sm text-emerald-700 hover:underline dark:text-emerald-300" href="{{ route('districts.index') }}">← All districts</a>
            <h1 class="text-3xl font-bold">{{ $district->name }}</h1>
            <p class="text-stone-600 dark:text-stone-300">
                {{ $district->eateries_count }} eateries ·
                @if ($district->reviews_count > 0)
                    {{ number_format((float) $district->reviews_avg_rating, 1) }} / 5 average rating
                @else
                    No ratings yet
                @endif
            </p>
            <p class="text-stone-600 dark:text-stone-300">{{ $district->reviews_count }} reviews in {{ $district->name }}</p>
        </div>

        @if ($district->eateries->isEmpty())
            <p class="rounded-xl border border-dashed border-stone-300 p-8 text-center text-stone-600 dark:border-stone-700 dark:text-stone-300">No eateries in this district yet.</p>
        @else
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($district->eateries as $eatery)
                    <x-eatery-card :eatery="$eatery" />
                @endforeach
            </div>
        @endif
    </section>
@endsection
