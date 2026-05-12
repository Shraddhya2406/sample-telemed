@extends('layouts.patient')

@section('title', 'Appointment')

@section('content')
@php
    $statusClasses = [
        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-950 dark:text-yellow-200',
        'approved' => 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-200',
        'rejected' => 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-200',
        'completed' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200',
    ];

    $statusLabels = [
        'pending' => 'Pending',
        'approved' => 'Accepted',
        'rejected' => 'Rejected',
        'completed' => 'Completed',
    ];
@endphp

<div class="max-w-5xl mx-auto">
    <div class="bg-white p-6 rounded shadow mb-6 dark:bg-slate-900 dark:border dark:border-slate-800">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-950 dark:text-white">Appointment with Dr. {{ $appointment->doctor->name }}</h1>
                <p class="text-gray-600 dark:text-slate-300">{{ $appointment->appointment_date?->format('d M Y') }} at {{ substr($appointment->appointment_time, 0, 5) }}</p>
                <p class="text-gray-600 dark:text-slate-300">{{ $appointment->doctor->doctorProfile?->specialization ?? 'General Medicine' }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-sm {{ $statusClasses[$appointment->status] ?? 'bg-gray-100 text-gray-700 dark:bg-slate-800 dark:text-slate-200' }}">
                {{ $statusLabels[$appointment->status] ?? ucfirst($appointment->status) }}
            </span>
        </div>

        <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="border rounded p-3 bg-gray-50 dark:bg-slate-950 dark:border-slate-800">
                <div class="text-sm text-gray-600 dark:text-slate-400">Consultation Fee</div>
                <div class="font-semibold text-slate-950 dark:text-white">Rs. {{ number_format((float) $appointment->consultation_fee, 2) }}</div>
            </div>
            <div class="border rounded p-3 bg-gray-50 dark:bg-slate-950 dark:border-slate-800">
                <div class="text-sm text-gray-600 dark:text-slate-400">Payment Status</div>
                <div class="font-semibold {{ $appointment->payment_status === 'paid' ? 'text-green-700 dark:text-green-300' : 'text-gray-700 dark:text-slate-200' }}">{{ ucfirst($appointment->payment_status ?? 'unpaid') }}</div>
            </div>
            <div class="border rounded p-3 bg-gray-50 dark:bg-slate-950 dark:border-slate-800">
                <div class="text-sm text-gray-600 dark:text-slate-400">Payment ID</div>
                <div class="font-semibold break-all text-slate-950 dark:text-white">{{ $appointment->payment_id ?: 'N/A' }}</div>
            </div>
        </div>

        @if($appointment->status === 'pending')
            <form method="POST" action="{{ route('patient.appointments.cancel', $appointment) }}" class="mt-4">
                @csrf
                @method('PATCH')
                <button class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700" type="submit">Cancel Request</button>
            </form>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded shadow dark:bg-slate-900 dark:border dark:border-slate-800">
            <h2 class="text-lg font-bold mb-3 text-slate-950 dark:text-white">Consultation Details</h2>
            <div class="space-y-4">
                <div>
                    <div class="font-semibold text-slate-950 dark:text-white">Symptoms</div>
                    <p class="text-gray-700 dark:text-slate-300">{{ $appointment->symptoms ?: 'Not provided' }}</p>
                </div>
                <div>
                    <div class="font-semibold text-slate-950 dark:text-white">Doctor Diagnosis</div>
                    <p class="text-gray-700 dark:text-slate-300">{{ $appointment->diagnosis ?: 'Pending' }}</p>
                </div>
                <div>
                    <div class="font-semibold text-slate-950 dark:text-white">Advice</div>
                    <p class="text-gray-700 dark:text-slate-300">{{ $appointment->advice ?: 'Pending' }}</p>
                </div>
                @if($appointment->prescription)
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('patient.prescriptions.show', $appointment->prescription) }}" class="inline-block bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">View Prescription</a>
                        <a href="{{ route('patient.prescriptions.show', ['prescription' => $appointment->prescription, 'print' => 1]) }}" class="inline-block bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-900">Print Prescription</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white p-6 rounded shadow dark:bg-slate-900 dark:border dark:border-slate-800">
            <h2 class="text-lg font-bold mb-3 text-slate-950 dark:text-white">Messages</h2>
            <div
                class="space-y-3 mb-4"
                data-chat-messages
                data-chat-variant="patient"
                data-fetch-url="{{ route('patient.appointments.messages.index', $appointment) }}"
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
                    <p class="text-gray-600 dark:text-slate-400" data-chat-empty>No messages yet.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('patient.appointments.messages.store', $appointment) }}" data-chat-form>
                @csrf
                <textarea name="message" rows="3" class="w-full border rounded px-3 py-2 mb-2 dark:bg-slate-950 dark:border-slate-700 dark:text-slate-100" placeholder="Send a message to the doctor"></textarea>
                <div class="text-sm text-red-600 mb-2 hidden" data-chat-error></div>
                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700" type="submit">Send Message</button>
            </form>
        </div>
    </div>
</div>
@include('appointments.chat-script')
@endsection
