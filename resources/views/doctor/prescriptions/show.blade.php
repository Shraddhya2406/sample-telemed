@extends('doctor.layout')

@section('title', 'Prescription')
@section('page-title', 'Prescription for '.$prescription->patient->name)

@section('content')
<div class="mx-auto max-w-6xl overflow-x-auto">
    <div class="no-print mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Prescription Preview</p>
            <h2 class="mt-1 text-lg font-bold text-slate-950">Ready to print or share</h2>
        </div>
        <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50" type="button">
            <i data-lucide="printer" class="h-4 w-4"></i>
            Print
        </button>
    </div>

    @include('prescriptions.document', ['prescription' => $prescription, 'showCartActions' => false])
</div>
@endsection
