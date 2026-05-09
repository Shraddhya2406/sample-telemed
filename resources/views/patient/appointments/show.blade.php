@extends('layouts.app')

@section('title', 'Appointment')

@section('content')
@php
    $statusClasses = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'approved' => 'bg-green-100 text-green-800',
        'rejected' => 'bg-red-100 text-red-800',
        'completed' => 'bg-blue-100 text-blue-800',
    ];

    $statusLabels = [
        'pending' => 'Pending',
        'approved' => 'Accepted',
        'rejected' => 'Rejected',
        'completed' => 'Completed',
    ];
@endphp

<div class="max-w-5xl mx-auto">
    <div class="bg-white p-6 rounded shadow mb-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">Appointment with Dr. {{ $appointment->doctor->name }}</h1>
                <p class="text-gray-600">{{ $appointment->appointment_date?->format('d M Y') }} at {{ substr($appointment->appointment_time, 0, 5) }}</p>
                <p class="text-gray-600">{{ $appointment->doctor->doctorProfile?->specialization ?? 'General Medicine' }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-sm {{ $statusClasses[$appointment->status] ?? 'bg-gray-100 text-gray-700' }}">
                {{ $statusLabels[$appointment->status] ?? ucfirst($appointment->status) }}
            </span>
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
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-lg font-bold mb-3">Consultation Details</h2>
            <div class="space-y-4">
                <div>
                    <div class="font-semibold">Symptoms</div>
                    <p class="text-gray-700">{{ $appointment->symptoms ?: 'Not provided' }}</p>
                </div>
                <div>
                    <div class="font-semibold">Doctor Diagnosis</div>
                    <p class="text-gray-700">{{ $appointment->diagnosis ?: 'Pending' }}</p>
                </div>
                <div>
                    <div class="font-semibold">Advice</div>
                    <p class="text-gray-700">{{ $appointment->advice ?: 'Pending' }}</p>
                </div>
                @if($appointment->prescription)
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('patient.prescriptions.show', $appointment->prescription) }}" class="inline-block bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">View Prescription</a>
                        <a href="{{ route('patient.prescriptions.show', ['prescription' => $appointment->prescription, 'print' => 1]) }}" class="inline-block bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-900">Print Prescription</a>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-lg font-bold mb-3">Messages</h2>
            <div class="space-y-3 mb-4">
                @forelse($appointment->messages as $message)
                    <div class="border rounded p-3">
                        <div class="text-sm text-gray-600">{{ $message->sender->name }} · {{ $message->created_at->format('d M Y h:i A') }}</div>
                        <p>{{ $message->message }}</p>
                    </div>
                @empty
                    <p class="text-gray-600">No messages yet.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('patient.appointments.messages.store', $appointment) }}">
                @csrf
                <textarea name="message" rows="3" class="w-full border rounded px-3 py-2 mb-2" placeholder="Send a message to the doctor"></textarea>
                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700" type="submit">Send Message</button>
            </form>
        </div>
    </div>
</div>
@endsection
