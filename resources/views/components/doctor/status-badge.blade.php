@props(['status' => 'pending'])

@php
    $styles = [
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'confirmed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'completed' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'rejected' => 'bg-red-50 text-red-700 ring-red-200',
        'ended' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'initiated' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
    ];

    $labels = [
        'approved' => 'Confirmed',
        'initiated' => 'Incoming',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset '.($styles[$status] ?? 'bg-slate-100 text-slate-700 ring-slate-200')]) }}>
    {{ $labels[$status] ?? str($status)->headline() }}
</span>
