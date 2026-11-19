@props(['rating', 'count' => null])

@php
    $numericRating = (float) $rating;
    $filledStars = (int) round($numericRating);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex flex-wrap items-center gap-x-2 gap-y-1']) }}>
    <span class="sr-only">{{ number_format($numericRating, 1) }} out of 5 stars</span>
    <span class="text-amber-600 dark:text-amber-300" aria-hidden="true">
        @for ($star = 1; $star <= 5; $star++)
            {{ $star <= $filledStars ? '★' : '☆' }}
        @endfor
    </span>
    @if ($count !== null)
        <span>{{ number_format($numericRating, 1) }} ★ ({{ $count }} {{ (int) $count === 1 ? 'review' : 'reviews' }})</span>
    @else
        <span>{{ number_format($numericRating, 0) }} / 5</span>
    @endif
</span>
