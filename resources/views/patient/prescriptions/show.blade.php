@extends('layouts.patient')

@section('title', 'Prescription')
@section('page_title', 'Prescription')
@section('eyebrow', 'Medical record')

@section('content')
<div class="mx-auto max-w-6xl overflow-x-auto pb-20 lg:pb-0">
    <div id="prescription-print-toolbar" class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-blue-700 dark:text-blue-300">Prescription</p>
            <h1 class="mt-1 text-xl font-bold text-slate-950 dark:text-white">Your digital prescription</h1>
        </div>
        <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700" type="button">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v7H6v-7Z"/></svg>
            Print
        </button>
    </div>

    @include('prescriptions.document', ['prescription' => $prescription, 'showCartActions' => true])
</div>
@if(request()->boolean('print'))
    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
@endif
@endsection
