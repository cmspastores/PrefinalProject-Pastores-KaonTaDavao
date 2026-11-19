<header class="border-b border-stone-200 bg-white dark:border-stone-800 dark:bg-stone-900">
    <nav class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-4">
        <a class="text-xl font-bold text-emerald-800 dark:text-emerald-300" href="{{ route('home') }}">Davao Eats</a>
        <div class="flex flex-wrap items-center gap-4 text-sm font-medium">
            <a class="hover:text-emerald-700 dark:hover:text-emerald-300" href="{{ route('home') }}">Home</a>
            <a class="hover:text-emerald-700 dark:hover:text-emerald-300" href="{{ route('eateries.index') }}">Eateries</a>
            <a class="hover:text-emerald-700 dark:hover:text-emerald-300" href="{{ route('districts.index') }}">Districts</a>
            <a class="rounded-lg bg-emerald-700 px-4 py-2 text-white hover:bg-emerald-800" href="{{ route('eateries.create') }}">Add Eatery</a>
        </div>
    </nav>
</header>
