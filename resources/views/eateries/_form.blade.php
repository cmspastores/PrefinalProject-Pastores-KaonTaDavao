@php
    $isEditing = $eatery !== null;
    $selectedFeatureIds = old('features', $eatery?->features->modelKeys() ?? []);
    $selectedFeatureIds = is_array($selectedFeatureIds) ? $selectedFeatureIds : [];
    $priceRanges = ['budget', 'mid', 'splurge'];
    $selectedPriceRange = old('price_range', $eatery?->price_range ?? 'budget');
    $selectedPriceRange = is_string($selectedPriceRange) ? $selectedPriceRange : ($eatery?->price_range ?? 'budget');
@endphp

    <section class="mx-auto max-w-3xl space-y-6">
        <div>
            <a class="text-sm text-emerald-700 hover:underline dark:text-emerald-300" href="{{ $isEditing ? route('eateries.show', $eatery->slug) : route('eateries.index') }}">← Back</a>
            <h1 class="mt-2 text-3xl font-bold">{{ $isEditing ? 'Edit eatery' : 'Add an eatery' }}</h1>
        </div>

        <form class="space-y-6 rounded-xl border border-stone-200 bg-white p-6 dark:border-stone-800 dark:bg-stone-900" method="POST" action="{{ $isEditing ? route('eateries.update', $eatery->slug) : route('eateries.store') }}">
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <label class="space-y-1 text-sm font-medium">
                    <span>Name</span>
                    <input class="w-full rounded-lg border border-stone-300 bg-transparent px-3 py-2 dark:border-stone-700" name="name" maxlength="100" required value="{{ old('name', $eatery?->name) }}">
                </label>
                <label class="space-y-1 text-sm font-medium">
                    <span>District</span>
                    <select class="w-full rounded-lg border border-stone-300 bg-transparent px-3 py-2 dark:border-stone-700" name="district_id" required>
                        @foreach ($districts as $district)
                            <option value="{{ $district->id }}" @selected(old('district_id', $eatery?->district_id) == $district->id)>{{ $district->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="space-y-1 text-sm font-medium">
                    <span>Price range</span>
                    <select class="w-full rounded-lg border border-stone-300 bg-transparent px-3 py-2 capitalize dark:border-stone-700" name="price_range" required>
                        @if (! in_array($selectedPriceRange, $priceRanges, true))
                            <option value="{{ $selectedPriceRange }}" selected>{{ $selectedPriceRange }}</option>
                        @endif
                        @foreach ($priceRanges as $priceRange)
                            <option value="{{ $priceRange }}" @selected($selectedPriceRange === $priceRange)>{{ $priceRange }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="space-y-1 text-sm font-medium sm:col-span-2">
                    <span>Address</span>
                    <input class="w-full rounded-lg border border-stone-300 bg-transparent px-3 py-2 dark:border-stone-700" name="address" required value="{{ old('address', $eatery?->address) }}">
                </label>
                <label class="space-y-1 text-sm font-medium sm:col-span-2">
                    <span>Specialty (optional)</span>
                    <input class="w-full rounded-lg border border-stone-300 bg-transparent px-3 py-2 dark:border-stone-700" name="specialty" maxlength="100" value="{{ old('specialty', $eatery?->specialty) }}">
                </label>
            </div>

            <label class="flex items-center gap-2 text-sm font-medium">
                <input type="hidden" name="is_open" value="0">
                <input class="rounded border-stone-300 text-emerald-700" type="checkbox" name="is_open" value="1" @checked(old('is_open', $eatery?->is_open ?? true))>
                Currently open
            </label>

            <fieldset class="space-y-3">
                <legend class="text-sm font-semibold">Features</legend>
                <div class="grid gap-2 sm:grid-cols-2">
                    @forelse ($features as $feature)
                        <label class="flex items-center gap-2 text-sm">
                            <input class="rounded border-stone-300 text-emerald-700" type="checkbox" name="features[]" value="{{ $feature->id }}" @checked(in_array($feature->id, $selectedFeatureIds))>
                            {{ $feature->name }}
                        </label>
                    @empty
                        <p class="text-sm text-stone-600 dark:text-stone-300">No features are available yet.</p>
                    @endforelse
                </div>
            </fieldset>

            <fieldset class="grid gap-5 border-t border-stone-200 pt-5 sm:grid-cols-2 dark:border-stone-800">
                <legend class="px-1 text-sm font-semibold">Permit</legend>
                <label class="space-y-1 text-sm font-medium">
                    <span>Permit number</span>
                    <input class="w-full rounded-lg border border-stone-300 bg-transparent px-3 py-2 dark:border-stone-700" name="permit_no" required value="{{ old('permit_no', $eatery?->permit?->permit_no) }}">
                </label>
                <label class="space-y-1 text-sm font-medium">
                    <span>Expiry date</span>
                    <input class="w-full rounded-lg border border-stone-300 bg-transparent px-3 py-2 dark:border-stone-700" type="date" name="expires_on" required value="{{ old('expires_on', $eatery?->permit?->expires_on?->toDateString()) }}">
                </label>
            </fieldset>

            <button class="rounded-lg bg-emerald-700 px-5 py-2.5 font-semibold text-white hover:bg-emerald-800" type="submit">{{ $isEditing ? 'Save changes' : 'Create eatery' }}</button>
        </form>
    </section>
