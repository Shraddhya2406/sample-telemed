@extends('doctor.layout')

@section('title', 'Appointment')
@section('page-title', 'Appointment with '.$appointment->patient->name)

@section('content')
@include('doctor.partials')
<div class="row g-4">
    <div class="col-xl-8">
        <div class="doctor-card p-3 mb-4">
            <div class="d-flex flex-wrap gap-3 justify-content-between">
                <div>
                    <h2 class="h5 mb-1">{{ $appointment->patient->name }}</h2>
                    <div class="text-secondary">{{ $appointment->appointment_date?->format('d M Y') }} at {{ substr($appointment->appointment_time, 0, 5) }}</div>
                </div>
                <span class="badge text-bg-{{ $statusClasses[$appointment->status] ?? 'secondary' }} align-self-start">{{ $statusLabels[$appointment->status] ?? ucfirst($appointment->status) }}</span>
            </div>
            <form method="POST" action="{{ route('doctor.appointments.status', $appointment) }}" class="d-flex flex-wrap gap-2 mt-3">
                @csrf
                @method('PATCH')
                <button name="status" value="approved" class="btn btn-sm btn-success">Accept</button>
                <button name="status" value="rejected" class="btn btn-sm btn-outline-danger">Reject</button>
                <button name="status" value="completed" class="btn btn-sm btn-primary">Complete</button>
            </form>
        </div>

        <div class="doctor-card p-3 mb-4">
            <h2 class="h5 mb-3">Consultation Notes</h2>
            <form method="POST" action="{{ route('doctor.appointments.notes', $appointment) }}">
                @csrf
                @method('PATCH')
                <div class="mb-3">
                    <label class="form-label">Symptoms</label>
                    <textarea name="symptoms" class="form-control" rows="3">{{ old('symptoms', $appointment->symptoms) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Diagnosis</label>
                    <textarea name="diagnosis" class="form-control" rows="3">{{ old('diagnosis', $appointment->diagnosis) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Advice</label>
                    <textarea name="advice" class="form-control" rows="3">{{ old('advice', $appointment->advice) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Internal Notes</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $appointment->notes) }}</textarea>
                </div>
                <button class="btn btn-success">Save Notes</button>
                <a href="{{ route('doctor.prescriptions.create', ['appointment_id' => $appointment->id]) }}" class="btn btn-outline-success">Create Prescription</a>
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
                    <div class="fw-semibold">{{ $attempt->result_category }} · Score {{ $attempt->total_score }}</div>
                    <div class="small text-secondary mb-2">{{ $attempt->created_at->format('d M Y') }}</div>
                    @foreach($attempt->quizAnswers as $answer)
                        <div class="small"><strong>{{ $answer->healthQuestion->question }}</strong><br>{{ $answer->healthOption->option_text }}</div>
                    @endforeach
                </div>
            @empty
                <div class="text-secondary">No quiz attempts found.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
