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
