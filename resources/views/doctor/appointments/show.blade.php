@extends('doctor.layout')

@section('title', 'Appointment')
@section('page-title', 'Appointment with '.$appointment->patient->name)

@section('content')
@include('doctor.partials')
@php
    $notesLocked = in_array($appointment->status, ['rejected', 'completed'], true);
@endphp
<div class="row g-4">
    <div class="col-xl-8">
        <div class="doctor-card p-3 mb-4">
            <div class="d-flex flex-wrap gap-3 justify-content-between">
                <div>
                    <h2 class="h5 mb-1">{{ $appointment->patient->name }}</h2>
                    <div class="text-secondary">{{ $appointment->appointment_date?->format('d M Y') }} at {{ substr($appointment->appointment_time, 0, 5) }}</div>
                </div>
                <div class="d-flex flex-wrap align-items-start justify-content-end gap-2">
                    @if($appointment->status === 'completed' && $appointment->prescription)
                        <a href="{{ route('doctor.prescriptions.show', $appointment->prescription) }}" class="btn btn-sm btn-outline-primary">View Prescription</a>
                    @endif
                    <span class="badge text-bg-{{ $statusClasses[$appointment->status] ?? 'secondary' }} align-self-start">{{ $statusLabels[$appointment->status] ?? ucfirst($appointment->status) }}</span>
                </div>
            </div>
            @if($appointment->status === 'pending')
                <form method="POST" action="{{ route('doctor.appointments.status', $appointment) }}" class="d-flex flex-wrap gap-2 mt-3">
                    @csrf
                    @method('PATCH')
                    <button name="status" value="approved" class="btn btn-sm btn-success">Accept</button>
                    <button name="status" value="rejected" class="btn btn-sm btn-outline-danger">Reject</button>
                </form>
            @endif
        </div>

        <div class="doctor-card p-3 mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h2 class="h5 mb-0">Consultation Notes</h2>
                @if($notesLocked)
                    <span class="badge text-bg-light border">Locked</span>
                @endif
            </div>
            <form method="POST" action="{{ route('doctor.appointments.notes', $appointment) }}">
                @csrf
                @method('PATCH')
                <div class="mb-3 border rounded p-3 bg-light">
                    <div class="form-label fw-semibold mb-1">Symptoms</div>
                    <div class="text-secondary">{{ $appointment->symptoms ?: 'Not provided' }}</div>
                </div>
                <div class="mb-3 border rounded p-3 bg-light">
                    <div class="form-label fw-semibold mb-1">Notes</div>
                    <div class="text-secondary">{{ $appointment->notes ?: 'Not provided' }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Diagnosis</label>
                    <textarea name="diagnosis" class="form-control" rows="3" required @readonly($notesLocked)>{{ old('diagnosis', $appointment->diagnosis) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Advice</label>
                    <textarea name="advice" class="form-control" rows="3" required @readonly($notesLocked)>{{ old('advice', $appointment->advice) }}</textarea>
                </div>
                @unless($notesLocked)
                    <button class="btn btn-success">Save</button>
                    <button class="btn btn-outline-success" name="next" value="prescription">Create Prescription</button>
                @endunless
            </form>
        </div>

        <div class="doctor-card p-3">
            <h2 class="h5 mb-3">Messages</h2>
            <div class="vstack gap-2 mb-3">
                @forelse($appointment->messages as $message)
                    <div class="border rounded p-2">
                        <div class="small text-secondary">{{ $message->sender->name }} · {{ $message->created_at->format('d M Y h:i A') }}</div>
                        <div>{{ $message->message }}</div>
                    </div>
                @empty
                    <div class="text-secondary">No messages yet.</div>
                @endforelse
            </div>
            <form method="POST" action="{{ route('doctor.appointments.messages.store', $appointment) }}">
                @csrf
                <textarea name="message" class="form-control mb-2" rows="2" placeholder="Write a message to the patient"></textarea>
                <button class="btn btn-success">Send</button>
            </form>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="doctor-card p-3">
            <h2 class="h5 mb-3">Quiz History</h2>
            @forelse($appointment->patient->quizAttempts as $attempt)
                <div class="border rounded p-2 mb-2">
                    <div class="fw-semibold">{{ $attempt->result_category }}</div>
                    <div class="small text-secondary mb-2">{{ $attempt->created_at->format('d M Y') }}</div>
                    @foreach($attempt->quizAnswers as $answer)
                        <div class="small">
                            <strong>{{ $answer->healthQuestion?->question ?? 'Question unavailable' }}</strong><br>
                            {{ $answer->healthOption?->option_text ?? 'Answer unavailable' }}
                        </div>
                    @endforeach
                </div>
            @empty
                <div class="text-secondary">No quiz attempts found.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
