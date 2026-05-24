@extends('layouts.patient')

@section('title', 'Appointment')
@section('page_title', 'Appointment Details')
@section('eyebrow', 'Doctor consultation')

@section('content')
@php
    $statusClasses = [
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-950 dark:text-amber-300 dark:ring-amber-900',
        'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:ring-emerald-900',
        'confirmed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-950 dark:text-emerald-300 dark:ring-emerald-900',
        'rejected' => 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-950 dark:text-rose-300 dark:ring-rose-900',
        'completed' => 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-950 dark:text-blue-300 dark:ring-blue-900',
    ];

    $statusLabels = [
        'pending' => 'Pending',
        'approved' => 'Accepted',
        'confirmed' => 'Confirmed',
        'rejected' => 'Rejected',
        'completed' => 'Completed',
    ];

    $paymentStatusClass = $appointment->payment_status === 'paid'
        ? 'text-emerald-700 dark:text-emerald-300'
        : 'text-slate-700 dark:text-slate-200';
@endphp

<div class="mx-auto max-w-6xl space-y-4 pb-20 lg:pb-0">
    <a href="{{ route('patient.appointments.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-blue-700 transition hover:text-blue-800 dark:text-blue-300 dark:hover:text-blue-200">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19 8 12l7-7" />
        </svg>
        Back to appointments
    </a>

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-4 p-4 lg:grid-cols-[1fr_18rem] lg:p-5">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-950 dark:text-blue-300">Appointment</span>
                    <span class="rounded-full px-2.5 py-1 text-xs font-bold ring-1 {{ $statusClasses[$appointment->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700' }}">
                        {{ $statusLabels[$appointment->status] ?? ucfirst($appointment->status) }}
                    </span>
                </div>

                <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-950 dark:text-white">Dr. {{ $appointment->doctor->name }}</h2>
                <p class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $appointment->doctor->doctorProfile?->specialization ?? 'General Medicine' }}</p>
                <p class="mt-2 max-w-2xl text-sm leading-5 text-slate-500 dark:text-slate-400">
                    {{ $appointment->appointment_date?->format('M d, Y') }} at {{ substr($appointment->appointment_time, 0, 5) }}
                </p>

                <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                    @if($appointment->prescription)
                        <a href="{{ route('patient.prescriptions.show', $appointment->prescription) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Zm0 0v6h6M8 13h8M8 17h5" />
                            </svg>
                            View Prescription
                        </a>
                        <a href="{{ route('patient.prescriptions.show', ['prescription' => $appointment->prescription, 'print' => 1]) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v7H6v-7Z" />
                            </svg>
                            Print
                        </a>
                    @endif

                    @if($appointment->status === 'pending')
                        <form method="POST" action="{{ route('patient.appointments.cancel', $appointment) }}">
                            @csrf
                            @method('PATCH')
                            <button class="inline-flex w-full items-center justify-center rounded-lg border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm font-bold text-rose-700 transition hover:border-rose-300 hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-300" type="submit">Cancel Request</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-950">
                <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Payment Summary</p>
                <div class="mt-3 divide-y divide-slate-200 dark:divide-slate-800">
                    <div class="flex items-center justify-between gap-3 py-2 first:pt-0">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Fee</p>
                        <p class="text-base font-bold text-slate-950 dark:text-white">Rs. {{ number_format((float) $appointment->consultation_fee, 2) }}</p>
                    </div>
                    <div class="flex items-center justify-between gap-3 py-2">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Status</p>
                        <p class="font-bold {{ $paymentStatusClass }}">{{ ucfirst($appointment->payment_status ?? 'unpaid') }}</p>
                    </div>
                    <div class="grid gap-1 py-2 pb-0">
                        <p class="text-sm text-slate-500 dark:text-slate-400">Payment ID</p>
                        <p class="break-all text-sm font-bold text-slate-950 dark:text-white">{{ $appointment->payment_id ?: 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 lg:grid-cols-[.9fr_1.1fr]">
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                <h3 class="font-bold text-slate-950 dark:text-white">Consultation Details</h3>
            </div>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                <div class="p-4">
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Symptoms</p>
                    <p class="mt-1 text-sm leading-5 text-slate-700 dark:text-slate-300">{{ $appointment->symptoms ?: 'Not provided' }}</p>
                </div>
                <div class="p-4">
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Doctor Diagnosis</p>
                    <p class="mt-1 text-sm leading-5 text-slate-700 dark:text-slate-300">{{ $appointment->diagnosis ?: 'Pending' }}</p>
                </div>
                <div class="p-4">
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Advice</p>
                    <p class="mt-1 text-sm leading-5 text-slate-700 dark:text-slate-300">{{ $appointment->advice ?: 'Pending' }}</p>
                </div>
            </div>
        </div>

        <div id="messages" class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                <h3 class="font-bold text-slate-950 dark:text-white">Messages</h3>
            </div>
            <div class="p-4">
                <div
                    class="mb-4 space-y-3"
                    data-chat-messages
                    data-chat-variant="patient"
                    data-fetch-url="{{ rtrim(request()->getBaseUrl(), '/') }}/patient/appointments/{{ $appointment->id }}/messages"
                    data-appointment-id="{{ $appointment->id }}"
                    data-current-user-id="{{ auth()->id() }}"
                    data-last-id="{{ $appointment->messages->max('id') ?? 0 }}"
                >
                    @forelse($appointment->messages->sortBy('id') as $message)
                        <div class="chat-message {{ $message->sender_id === auth()->id() ? 'chat-message-own' : 'chat-message-other' }}" data-message-id="{{ $message->id }}">
                            <div class="chat-bubble">
                                <div class="chat-meta">{{ $message->sender_id === auth()->id() ? 'You' : $message->sender->name }} &middot; {{ $message->created_at->format('d M Y h:i A') }}</div>
                                <div class="chat-body">{{ $message->message }}</div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500 dark:text-slate-400" data-chat-empty>No messages yet.</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('patient.appointments.messages.store', $appointment) }}" data-chat-form>
                    @csrf
                    <textarea name="message" rows="3" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 transition focus:border-blue-300 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-blue-700 dark:focus:ring-blue-950" placeholder="Send a message to the doctor"></textarea>
                    <div class="mb-2 mt-2 hidden text-sm text-rose-600" data-chat-error></div>
                    <button class="mt-3 inline-flex items-center justify-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-emerald-700" type="submit">Send Message</button>
                </form>
            </div>
        </div>
    </section>
</div>
@include('appointments.chat-script')
@endsection
