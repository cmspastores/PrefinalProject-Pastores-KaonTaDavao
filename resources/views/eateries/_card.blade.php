<article class="flex h-full flex-col gap-4 rounded-xl border border-stone-200 bg-white p-5 shadow-sm dark:border-stone-800 dark:bg-stone-900">
    <div class="space-y-2">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-lg font-semibold">
                <a class="hover:text-emerald-700 dark:hover:text-emerald-300" href="{{ route('eateries.show', $eatery->slug) }}">{{ $eatery->name }}</a>
            </h2>
            <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-medium capitalize dark:bg-stone-800">{{ $eatery->price_range }}</span>
        </div>
        <p class="text-sm text-stone-600 dark:text-stone-300">
            <a class="underline decoration-stone-300 underline-offset-2 hover:text-emerald-700 dark:hover:text-emerald-300" href="{{ route('districts.show', $eatery->district) }}">{{ $eatery->district->name }}</a>
            <span aria-hidden="true">·</span>
            {{ $eatery->address }}
        </p>
        @if ($eatery->specialty)
            <p class="text-sm text-stone-600 dark:text-stone-300">{{ $eatery->specialty }}</p>
        @endif
    </div>

    <div class="flex flex-wrap gap-2">
        @forelse ($eatery->features as $feature)
            <a class="rounded-full bg-emerald-50 px-3 py-1 text-xs text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200" href="{{ route('eateries.index', ['feature' => $feature->id]) }}">{{ $feature->name }}</a>
        @empty
            <span class="text-xs text-stone-500">No features listed</span>
        @endforelse
    </div>

    <div class="mt-auto flex flex-wrap gap-x-4 gap-y-1 border-t border-stone-100 pt-3 text-sm text-stone-600 dark:border-stone-800 dark:text-stone-300">
        <span>{{ $eatery->dishes_count }} dishes</span>
        <span>{{ $eatery->reviews_count }} reviews</span>
        <span>
            @if ($eatery->reviews_count > 0)
                {{ number_format((float) $eatery->reviews_avg_rating, 1) }} / 5
            @else
                No ratings yet
            @endif
        </span>
    </div>
</article>
