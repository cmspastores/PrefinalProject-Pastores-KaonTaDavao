<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Davao Eats')</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-stone-50 text-stone-900 dark:bg-stone-950 dark:text-stone-100">
    <header class="border-b border-stone-200 bg-white dark:border-stone-800 dark:bg-stone-900">
        <nav class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-4 py-4">
            <a class="text-xl font-bold text-emerald-800 dark:text-emerald-300" href="{{ route('eateries.index') }}">Davao Eats</a>
            <div class="flex flex-wrap items-center gap-4 text-sm font-medium">
                <a class="hover:text-emerald-700 dark:hover:text-emerald-300" href="{{ route('eateries.index') }}">Eateries</a>
                <a class="hover:text-emerald-700 dark:hover:text-emerald-300" href="{{ route('districts.index') }}">Districts</a>
                <a class="rounded-lg bg-emerald-700 px-4 py-2 text-white hover:bg-emerald-800" href="{{ route('eateries.create') }}">Add eatery</a>
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-6xl space-y-6 px-4 py-8">
        @if (session('status'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-900 dark:border-red-900 dark:bg-red-950 dark:text-red-200" role="alert">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
