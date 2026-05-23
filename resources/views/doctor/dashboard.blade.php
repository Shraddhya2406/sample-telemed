@extends('doctor.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@php
    $doctor = auth()->user();
    $profile = $doctor?->doctorProfile;
    $recentPatients = $appointments->unique('patient_id')->take(4);
@endphp

<section class="overflow-hidden rounded-3xl border border-blue-100 bg-white shadow-sm">
    <div class="grid gap-6 p-6 lg:grid-cols-[1fr_20rem] lg:p-8">
        <div class="min-w-0">
            <div class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-sm font-semibold text-blue-700 ring-1 ring-blue-100">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                Ready for consultations
            </div>
            <h2 class="mt-5 text-3xl font-bold tracking-normal text-slate-950">Good day, Dr. {{ $doctor?->name }}</h2>
            <p class="mt-2 max-w-2xl text-base leading-7 text-slate-500">
                {{ $profile?->specialization ?: 'Clinical specialist' }} workspace for appointments, prescriptions, patient history, and secure video care.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('doctor.appointments.index', ['status' => 'pending']) }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                    <i data-lucide="calendar-plus" class="h-4 w-4"></i>
                    Review Requests
                </a>
                <a href="{{ route('doctor.prescriptions.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:text-emerald-700">
                    <i data-lucide="clipboard-plus" class="h-4 w-4"></i>
                    New Prescription
                </a>
            </div>
        </div>
        <div class="rounded-2xl bg-slate-950 p-5 text-white">
            <p class="text-sm font-medium text-slate-300">Today</p>
            <p class="mt-2 text-4xl font-bold">{{ now()->format('d') }}</p>
            <p class="text-sm text-slate-300">{{ now()->format('F Y') }}</p>
            <div class="mt-6 rounded-2xl bg-white/10 p-4">
                <p class="text-sm text-slate-300">Appointments today</p>
                <p class="mt-1 text-2xl font-semibold">{{ $todayAppointments->count() }}</p>
            </div>
        </div>
    </div>
</section>

<section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-doctor.metric-card label="Total Patients" :value="$stats['patients']" icon="users" tone="blue" detail="Unique patients under your care" />
    <x-doctor.metric-card label="Today's Appointments" :value="$stats['today']" icon="calendar-check" tone="emerald" detail="Scheduled for the current day" />
    <x-doctor.metric-card label="Completed Consultations" :value="$stats['completed']" icon="badge-check" tone="blue" detail="Completed clinical sessions" />
    <x-doctor.metric-card label="Pending Prescriptions" :value="$stats['pending_prescriptions']" icon="file-clock" tone="amber" detail="Consultations ready for prescription" />
</section>

<section class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1.5fr)_minmax(22rem,0.8fr)]">
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="text-base font-semibold text-slate-950">Today's Appointments</h2>
                <p class="text-sm text-slate-500">Quickly move from triage to consultation.</p>
            </div>
            <a href="{{ route('doctor.appointments.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Patient</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Time</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($todayAppointments as $appointment)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-slate-950">{{ $appointment->patient->name }}</p>
                                <p class="text-sm text-slate-500">{{ str($appointment->symptoms ?: $appointment->notes ?: 'No symptoms provided')->limit(54) }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm font-medium text-slate-700">{{ substr($appointment->appointment_time, 0, 5) }}</td>
                            <td class="px-5 py-4"><x-doctor.status-badge :status="$appointment->status" /></td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('doctor.appointments.show', $appointment) }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700">
                                    Open
                                    <i data-lucide="arrow-right" class="h-4 w-4"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-5">
                                <x-doctor.empty-state title="No appointments today" message="Your daily schedule is clear for now." icon="calendar" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-slate-950">Upcoming Video Consultations</h2>
                    <p class="text-sm text-slate-500">Approved appointments ready for video.</p>
                </div>
                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ $upcomingConsultations->count() }}</span>
            </div>
            <div class="mt-4 space-y-3">
                @forelse($upcomingConsultations as $appointment)
                    <div class="rounded-2xl border border-slate-200 p-4 transition hover:border-blue-200 hover:shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-slate-950">{{ $appointment->patient->name }}</p>
                                <p class="text-sm text-slate-500">{{ $appointment->appointment_date?->format('d M Y') }} at {{ substr($appointment->appointment_time, 0, 5) }}</p>
                            </div>
                            <a href="{{ route('doctor.call.start', ['patient' => $appointment->patient, 'appointment_id' => $appointment->id]) }}" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white transition hover:bg-emerald-600" aria-label="Start video call">
                                <i data-lucide="video" class="h-4 w-4"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <x-doctor.empty-state title="No video calls queued" message="Confirmed appointments will appear here." icon="video" class="py-8" />
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold text-slate-950">Recent Patient Activity</h2>
            <div class="mt-4 space-y-4">
                @forelse($recentPatients as $appointment)
                    <a href="{{ route('doctor.patients.show', $appointment->patient) }}" class="flex items-center gap-3 rounded-2xl p-2 transition hover:bg-slate-50">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 font-semibold text-blue-700">{{ str($appointment->patient->name)->substr(0, 1)->upper() }}</span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-semibold text-slate-950">{{ $appointment->patient->name }}</span>
                            <span class="block text-xs text-slate-500">{{ $appointment->appointment_date?->diffForHumans() }}</span>
                        </span>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">Patient history will appear after appointments are booked.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>
@endsection
