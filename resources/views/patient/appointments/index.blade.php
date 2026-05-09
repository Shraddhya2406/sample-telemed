@extends('layouts.app')

@section('title', 'My Appointments')

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

<div class="max-w-6xl mx-auto">
    <div class="bg-white p-6 rounded shadow mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">My Appointments</h1>
            <p class="text-gray-600">Track doctor consultations and appointment decisions.</p>
        </div>
        <a href="{{ route('patient.appointments.create') }}" class="inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Book Appointment</a>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-4">Doctor</th>
                        <th class="p-4">Date</th>
                        <th class="p-4">Time</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Prescription</th>
                        <th class="p-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($appointments as $appointment)
                        <tr>
                            <td class="p-4">
                                <div class="font-semibold">Dr. {{ $appointment->doctor->name }}</div>
                                <div class="text-sm text-gray-600">{{ $appointment->doctor->doctorProfile?->specialization ?? 'General Medicine' }}</div>
                            </td>
                            <td class="p-4">{{ $appointment->appointment_date?->format('d M Y') }}</td>
                            <td class="p-4">{{ substr($appointment->appointment_time, 0, 5) }}</td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full text-sm {{ $statusClasses[$appointment->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $statusLabels[$appointment->status] ?? ucfirst($appointment->status) }}
                                </span>
                            </td>
                            <td class="p-4">
                                @if($appointment->prescription)
                                    <div class="flex flex-wrap gap-3">
                                        <a href="{{ route('patient.prescriptions.show', $appointment->prescription) }}" class="text-indigo-600 hover:underline">View</a>
                                        <a href="{{ route('patient.prescriptions.show', ['prescription' => $appointment->prescription, 'print' => 1]) }}" class="text-gray-800 hover:underline">Print</a>
                                    </div>
                                @else
                                    <span class="text-gray-500">Not issued</span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <a href="{{ route('patient.appointments.show', $appointment) }}" class="text-green-700 hover:underline">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-600">No appointments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $appointments->links() }}</div>
</div>
@endsection
