@extends('layouts.patient')

@section('title', 'Quiz Result')
@section('page_title', 'Health Result')
@section('eyebrow', 'Personal guidance')

@section('content')
@php
    $diseaseName = $recommendation?->disease_name ?? $quizAttempt?->result_category ?? 'General wellness support';
    $medicineName = $recommendation?->medicine_name ?? 'Please consult a doctor';
    $advice = $recommendation?->advice ?? 'Rest, stay hydrated, and consult a doctor if symptoms continue.';
@endphp

<div class="mx-auto max-w-3xl pb-20 lg:pb-0">
    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="border-b border-slate-100 bg-slate-50 px-6 py-6 text-center dark:border-slate-800 dark:bg-slate-950">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
                <svg class="h-9 w-9" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.7-9.3a1 1 0 0 0-1.4-1.4L9 10.6 7.7 9.3a1 1 0 0 0-1.4 1.4l2 2a1 1 0 0 0 1.4 0l4-4Z" clip-rule="evenodd"/></svg>
            </div>
            <h2 class="mt-4 text-3xl font-bold text-slate-950 dark:text-white">Your Assessment Is Ready</h2>
            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">This summary is written in plain language so your next step is clear.</p>
        </div>

        <div class="space-y-4 p-6 sm:p-8">
            <div class="rounded-lg border border-blue-100 bg-blue-50 p-5 dark:border-blue-900 dark:bg-blue-950/40">
                <p class="text-sm font-semibold text-blue-700 dark:text-blue-300">Possible concern</p>
                <p class="mt-2 text-2xl font-bold text-slate-950 dark:text-white">{{ $diseaseName }}</p>
            </div>
            <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-5 dark:border-emerald-900 dark:bg-emerald-950/40">
                <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Suggested medicine</p>
                <p class="mt-2 text-2xl font-bold text-slate-950 dark:text-white">{{ $medicineName }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Advice</p>
                <p class="mt-2 leading-7 text-slate-700 dark:text-slate-200">{{ $advice }}</p>
            </div>

            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200">
                This is not a medical diagnosis. Please consult a doctor for proper treatment.
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('patient.medicines.index') }}" class="inline-flex flex-1 items-center justify-center rounded-lg bg-emerald-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-700">Buy Medicines</a>
                <a href="{{ route('patient.appointments.create') }}" class="inline-flex flex-1 items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-700">Book Doctor</a>
                <a href="{{ route('dashboard.patient') }}" class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200">Dashboard</a>
            </div>
        </div>
    </section>
</div>
@endsection
