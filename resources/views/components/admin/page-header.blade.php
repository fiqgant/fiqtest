@props([
    'title',
    'subtitle' => null,
])

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-extrabold text-slate-800 dark:text-slate-100 tracking-tight">{{ $title }}</h2>
        @if($subtitle)
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">{{ $subtitle }}</p>
        @endif
    </div>
    @if(trim((string) $slot) !== '')
        <div class="flex items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
