@props([
    'label',
    'value',
    'icon' => 'activity',
    'tone' => 'blue',
    'detail' => null,
])

@php
    $tones = [
        'blue' => 'bg-blue-50 text-blue-600 ring-blue-100',
        'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
        'amber' => 'bg-amber-50 text-amber-600 ring-amber-100',
        'rose' => 'bg-rose-50 text-rose-600 ring-rose-100',
    ];
@endphp

<article {{ $attributes->merge(['class' => 'group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-xl']) }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-3xl font-semibold tracking-normal text-slate-950">{{ $value }}</p>
        </div>
        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ring-1 {{ $tones[$tone] ?? $tones['blue'] }}">
            <i data-lucide="{{ $icon }}" class="h-5 w-5"></i>
        </span>
    </div>
    @if($detail)
        <p class="mt-4 text-sm text-slate-500">{{ $detail }}</p>
    @endif
</article>
