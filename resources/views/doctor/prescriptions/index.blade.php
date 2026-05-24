@extends('doctor.layout')

@section('title', 'Prescriptions')
@section('page-title', 'Prescriptions')

@section('content')
@php
    $hasFilters = filled($filters['search'] ?? null) || filled($filters['date_from'] ?? null) || filled($filters['date_to'] ?? null);
@endphp

<section class="rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 p-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Patient Prescriptions</p>
                <h2 class="mt-1 text-lg font-bold text-slate-950">Prescription Records</h2>
                <p class="mt-1 text-sm text-slate-500">Search patient prescriptions and narrow records by created date.</p>
            </div>

            <a href="{{ route('doctor.prescriptions.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:bg-emerald-700">
                <i data-lucide="plus" class="h-4 w-4"></i>
                New Prescription
            </a>
        </div>

        <form method="GET" action="{{ route('doctor.prescriptions.index') }}" class="mt-4 grid gap-3 lg:grid-cols-[minmax(16rem,1fr)_11rem_11rem_auto] lg:items-end">
            <label class="block">
                <span class="text-xs font-semibold text-slate-600">Search</span>
                <span class="relative mt-1 block">
                    <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                    <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Patient, diagnosis, medicine" class="h-10 w-full rounded-lg border border-slate-200 bg-white pl-9 pr-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                </span>
            </label>

            <label class="block">
                <span class="text-xs font-semibold text-slate-600">From</span>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
            </label>

            <label class="block">
                <span class="text-xs font-semibold text-slate-600">To</span>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="mt-1 h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
            </label>

            <div class="flex gap-2">
                <button type="submit" class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-lg bg-slate-950 px-3 text-sm font-semibold text-white transition hover:bg-slate-800 lg:flex-none">
                    <i data-lucide="filter" class="h-4 w-4"></i>
                    Filter
                </button>
                @if($hasFilters)
                    <a href="{{ route('doctor.prescriptions.index') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:border-emerald-200 hover:text-emerald-700">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3">
        <p class="text-sm font-semibold text-slate-700">
            Showing {{ $prescriptions->firstItem() ?? 0 }}-{{ $prescriptions->lastItem() ?? 0 }} of {{ $prescriptions->total() }} records
        </p>
        @if($hasFilters)
            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">Filtered</span>
        @endif
    </div>

    <div class="hidden overflow-x-auto md:block">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Patient</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Diagnosis</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Medicines</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Date</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($prescriptions as $prescription)
                    <tr class="transition hover:bg-emerald-50/40">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-sm font-bold text-emerald-700">
                                    {{ str($prescription->patient?->name ?? 'P')->substr(0, 1)->upper() }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-950">{{ $prescription->patient?->name ?? 'Patient unavailable' }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $prescription->patient?->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="max-w-sm px-4 py-3 text-slate-600">{{ str($prescription->diagnosis ?: $prescription->notes ?: 'No diagnosis recorded')->limit(80) }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $prescription->items->count() }} items</span>
                            <p class="mt-1 max-w-xs truncate text-xs text-slate-500">{{ $prescription->items->pluck('medicine.name')->filter()->take(3)->join(', ') ?: 'No medicines listed' }}</p>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-700">{{ $prescription->created_at->format('d M Y') }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <a href="{{ route('doctor.prescriptions.show', $prescription) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-50">
                                View
                                <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-4">
                            <x-doctor.empty-state title="No prescriptions found" message="Try changing the search or date filters." icon="clipboard-list" class="py-8" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="space-y-3 p-4 md:hidden">
        @forelse($prescriptions as $prescription)
            <article class="rounded-xl border border-slate-200 p-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-slate-950">{{ $prescription->patient?->name ?? 'Patient unavailable' }}</p>
                        <p class="text-xs text-slate-500">{{ $prescription->created_at->format('d M Y') }} · {{ $prescription->items->count() }} medicines</p>
                    </div>
                    <a href="{{ route('doctor.prescriptions.show', $prescription) }}" class="shrink-0 rounded-lg border border-emerald-200 px-2.5 py-1.5 text-xs font-semibold text-emerald-700">View</a>
                </div>
                <p class="mt-2 text-sm text-slate-600">{{ str($prescription->diagnosis ?: $prescription->notes ?: 'No diagnosis recorded')->limit(90) }}</p>
                <p class="mt-1 truncate text-xs text-slate-500">{{ $prescription->items->pluck('medicine.name')->filter()->take(4)->join(', ') ?: 'No medicines listed' }}</p>
            </article>
        @empty
            <x-doctor.empty-state title="No prescriptions found" message="Try changing the search or date filters." icon="clipboard-list" class="py-8" />
        @endforelse
    </div>

    @if($prescriptions->hasPages())
        <div class="border-t border-slate-100 px-4 py-3">
            {{ $prescriptions->links() }}
        </div>
    @endif
</section>
@endsection
