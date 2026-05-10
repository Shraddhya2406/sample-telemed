@extends('layouts.patient')

@section('title', 'Profile')
@section('page_title', 'Profile')
@section('eyebrow', 'Your health account')

@section('content')
@php
    $user = Auth::user();
    $latestQuiz = $user->quizAttempts()->latest()->first();
    $appointments = $user->patientAppointments()->latest()->take(4)->get();
    $prescriptions = $user->patientPrescriptions()->latest()->take(4)->get();
@endphp

<div class="grid gap-6 pb-20 lg:grid-cols-[.8fr_1.2fr] lg:pb-0">
    <section class="h-fit rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-blue-600 text-2xl font-bold text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div>
                <h2 class="text-2xl font-bold text-slate-950 dark:text-white">{{ $user->name }}</h2>
                <p class="text-sm text-slate-500">{{ $user->email }}</p>
            </div>
        </div>

        <div class="mt-6 space-y-4">
            <div>
                <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Full name</label>
                <input value="{{ $user->name }}" readonly class="mt-2 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-950">
            </div>
            <div>
                <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Email address</label>
                <input value="{{ $user->email }}" readonly class="mt-2 w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm dark:border-slate-700 dark:bg-slate-950">
            </div>
            <button type="button" disabled class="w-full rounded-lg border border-slate-200 px-5 py-3 text-sm font-bold text-slate-500 dark:border-slate-700">Edit profile coming soon</button>
        </div>
    </section>

    <div class="space-y-6">
        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-lg font-bold text-slate-950 dark:text-white">Medical History</h3>
            <div class="mt-5 grid gap-4 sm:grid-cols-3">
                <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-950/40">
                    <p class="text-sm font-semibold text-blue-700 dark:text-blue-300">Latest quiz</p>
                    <p class="mt-2 font-bold text-slate-950 dark:text-white">{{ $latestQuiz?->result_category && $latestQuiz->result_category !== 'pending' ? $latestQuiz->result_category : 'Not recorded' }}</p>
                </div>
                <div class="rounded-lg bg-emerald-50 p-4 dark:bg-emerald-950/40">
                    <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Prescriptions</p>
                    <p class="mt-2 font-bold text-slate-950 dark:text-white">{{ $user->patientPrescriptions()->count() }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-950">
                    <p class="text-sm font-semibold text-slate-500">Appointments</p>
                    <p class="mt-2 font-bold text-slate-950 dark:text-white">{{ $user->patientAppointments()->count() }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-lg font-bold text-slate-950 dark:text-white">Recent Appointments</h3>
                <a href="{{ route('patient.appointments.index') }}" class="text-sm font-bold text-blue-700 dark:text-blue-300">View all</a>
            </div>
            <div class="mt-5 space-y-3">
                @forelse($appointments as $appointment)
                    <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-950">
                        <p class="font-bold text-slate-950 dark:text-white">{{ optional($appointment->doctor)->name ?? 'Doctor' }}</p>
                        <p class="text-sm text-slate-500">{{ $appointment->appointment_date?->format('M d, Y') }} - {{ ucfirst($appointment->status) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No appointments yet.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-lg font-bold text-slate-950 dark:text-white">Recent Prescriptions</h3>
                <a href="{{ route('patient.prescriptions.index') }}" class="text-sm font-bold text-blue-700 dark:text-blue-300">View all</a>
            </div>
            <div class="mt-5 space-y-3">
                @forelse($prescriptions as $prescription)
                    <a href="{{ route('patient.prescriptions.show', $prescription) }}" class="block rounded-lg bg-slate-50 p-4 transition hover:bg-blue-50 dark:bg-slate-950 dark:hover:bg-slate-800">
                        <p class="font-bold text-slate-950 dark:text-white">Prescription #{{ $prescription->id }}</p>
                        <p class="text-sm text-slate-500">{{ $prescription->created_at?->format('M d, Y') }}</p>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No prescriptions yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
