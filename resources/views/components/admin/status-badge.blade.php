@props([
    'value',
])

@php
    $normalized = strtolower((string) $value);
    $bgColor = match ($normalized) {
        'active', 'published', 'graded', 'success', 'easy' => 'bg-emerald-100 text-emerald-700',
        'draft', 'submitted', 'pending', 'medium', 'in_progress' => 'bg-amber-100 text-amber-700',
        'disqualified', 'failed', 'hard', 'closed' => 'bg-rose-100 text-rose-700',
        default => 'bg-slate-100 text-slate-600',
    };
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $bgColor }}">{{ ucfirst((string) $value) }}</span>
