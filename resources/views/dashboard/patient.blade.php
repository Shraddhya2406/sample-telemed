@extends('layouts.patient')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('eyebrow', 'Your care, organized')

@section('content')
@php
    $user = Auth::user();
    $latestQuiz = $user->quizAttempts()->latest()->first();
    $latestHealthConversation = $user->healthConversations()->latest()->first();
    $latestOrder = $user->orders()->latest()->first();
    $upcomingAppointment = $user->patientAppointments()->whereIn('status', ['pending', 'confirmed'])->orderBy('appointment_date')->orderBy('appointment_time')->first();
    $prescriptionCount = $user->patientPrescriptions()->count();
    $orderCount = $user->orders()->count();
    $appointmentCount = $user->patientAppointments()->count();
@endphp

<div class="space-y-8 pb-20 lg:pb-0">
    <section class="overflow-hidden rounded-lg border border-blue-100 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-6 p-6 lg:grid-cols-[1.35fr_.65fr] lg:p-8">
            <div>
                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">Patient Dashboard</span>
                <h2 class="mt-4 text-3xl font-bold tracking-tight text-slate-950 dark:text-white">Welcome back, {{ $user->name }}</h2>
                <p class="mt-3 max-w-2xl text-base leading-7 text-slate-600 dark:text-slate-300">Check your appointments, prescriptions, medicine orders, and quick health guidance from one clean workspace.</p>
                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('patient.health-quiz') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">Start AI Health Check</a>
                    <a href="{{ route('patient.appointments.create') }}" class="inline-flex items-center justify-center rounded-lg border border-emerald-200 bg-emerald-50 px-5 py-3 text-sm font-bold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300">Book Doctor</a>
                    <a href="{{ route('patient.medicines.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">Buy Medicines</a>
                </div>
            </div>
            <div class="rounded-lg bg-slate-50 p-5 dark:bg-slate-950">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Next Appointment</p>
                @if($upcomingAppointment)
                    <p class="mt-3 text-xl font-bold text-slate-950 dark:text-white">{{ optional($upcomingAppointment->doctor)->name ?? 'Doctor' }}</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ \Carbon\Carbon::parse($upcomingAppointment->appointment_date)->format('M d, Y') }} at {{ \Carbon\Carbon::parse($upcomingAppointment->appointment_time)->format('h:i A') }}</p>
                    <a href="{{ route('patient.appointments.show', $upcomingAppointment) }}" class="mt-5 inline-flex rounded-lg bg-white px-4 py-2 text-sm font-bold text-blue-700 ring-1 ring-blue-100 transition hover:bg-blue-50 dark:bg-slate-900 dark:ring-slate-800">View Details</a>
                @else
                    <p class="mt-3 text-xl font-bold text-slate-950 dark:text-white">No upcoming visits</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Book a consultation when you need medical guidance.</p>
                    <a href="{{ route('patient.appointments.create') }}" class="mt-5 inline-flex rounded-lg bg-blue-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-blue-700">Schedule Now</a>
                @endif
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Recent AI Assessment</p>
            <p class="mt-2 text-2xl font-bold text-slate-950 dark:text-white">{{ $latestHealthConversation ? str($latestHealthConversation->urgency_level)->headline() : ($latestQuiz?->result_category && $latestQuiz->result_category !== 'pending' ? $latestQuiz->result_category : 'Not taken yet') }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ $latestHealthConversation?->created_at ? $latestHealthConversation->created_at->diffForHumans() : 'Start with a quick symptom check.' }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Prescriptions</p>
            <p class="mt-2 text-2xl font-bold text-slate-950 dark:text-white">{{ $prescriptionCount }}</p>
            <p class="mt-2 text-sm text-slate-500">Doctor notes and medicines saved for you.</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Medicine Orders</p>
            <p class="mt-2 text-2xl font-bold text-slate-950 dark:text-white">{{ $orderCount }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ $latestOrder ? 'Latest: Order #' . $latestOrder->id : 'No orders placed yet.' }}</p>
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-[.85fr_1.15fr]">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="text-lg font-bold text-slate-950 dark:text-white">Quick Actions</h3>
            <div class="mt-5 grid gap-3">
                <a href="{{ route('patient.health-quiz') }}" class="flex items-center justify-between rounded-lg border border-blue-100 bg-blue-50 p-4 transition hover:border-blue-200 hover:bg-blue-100 dark:border-blue-900 dark:bg-blue-950/40">
                    <span><span class="block font-bold text-blue-900 dark:text-blue-100">AI Health Check</span><span class="text-sm text-blue-700 dark:text-blue-300">Chat about symptoms one question at a time.</span></span>
                    <span class="text-blue-700">-></span>
                </a>
                <a href="{{ route('patient.appointments.create') }}" class="flex items-center justify-between rounded-lg border border-emerald-100 bg-emerald-50 p-4 transition hover:border-emerald-200 hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-950/40">
                    <span><span class="block font-bold text-emerald-900 dark:text-emerald-100">Book Doctor</span><span class="text-sm text-emerald-700 dark:text-emerald-300">Choose a convenient slot.</span></span>
                    <span class="text-emerald-700">-></span>
                </a>
                <a href="{{ route('patient.medicines.index') }}" class="flex items-center justify-between rounded-lg border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300 hover:bg-white dark:border-slate-800 dark:bg-slate-950">
                    <span><span class="block font-bold text-slate-950 dark:text-white">Buy Medicines</span><span class="text-sm text-slate-600 dark:text-slate-300">Browse trusted pharmacy items.</span></span>
                    <span class="text-slate-600">-></span>
                </a>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-3">
                <h3 class="text-lg font-bold text-slate-950 dark:text-white">Recent Activity</h3>
                <a href="{{ route('patient.orders.index') }}" class="text-sm font-bold text-blue-700 dark:text-blue-300">View orders</a>
            </div>
            <div class="mt-5 space-y-4">
                <div class="flex gap-3">
                    <span class="mt-1 h-3 w-3 rounded-full bg-blue-600"></span>
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $appointmentCount }} appointment{{ $appointmentCount === 1 ? '' : 's' }} in your history</p>
                        <p class="text-sm text-slate-500">Manage visits and doctor messages from Appointments.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <span class="mt-1 h-3 w-3 rounded-full bg-emerald-600"></span>
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $prescriptionCount }} prescription{{ $prescriptionCount === 1 ? '' : 's' }} available</p>
                        <p class="text-sm text-slate-500">Add prescribed medicines directly to cart.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <span class="mt-1 h-3 w-3 rounded-full bg-amber-500"></span>
                    <div>
                        <p class="font-semibold text-slate-900 dark:text-white">Health guidance stays simple</p>
                        <p class="text-sm text-slate-500">AI assessments are preliminary and should be reviewed by a doctor.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
