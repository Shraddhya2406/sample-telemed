@extends('doctor.layout')

@section('title', 'Patient History')
@section('page-title', $patient->name)

@section('content')
@include('doctor.partials')
<div class="row g-4">
    <div class="col-xl-7">
        <div class="doctor-card p-3 mb-4">
            <h2 class="h5 mb-3">Appointments</h2>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Date</th><th>Status</th><th>Diagnosis</th><th></th></tr></thead>
                    <tbody>
                        @foreach($patient->patientAppointments as $appointment)
                            <tr>
                                <td>{{ $appointment->appointment_date?->format('d M Y') }}</td>
                                <td><span class="badge text-bg-{{ $statusClasses[$appointment->status] ?? 'secondary' }}">{{ $statusLabels[$appointment->status] ?? ucfirst($appointment->status) }}</span></td>
                                <td>{{ \Illuminate\Support\Str::limit($appointment->diagnosis, 50) }}</td>
                                <td><a href="{{ route('doctor.appointments.show', $appointment) }}" class="btn btn-sm btn-light">View</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="doctor-card p-3">
            <h2 class="h5 mb-3">Prescriptions</h2>
            @forelse($patient->patientPrescriptions as $prescription)
                <div class="border rounded p-2 mb-2">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $prescription->created_at->format('d M Y') }}</strong>
                        <a href="{{ route('doctor.prescriptions.show', $prescription) }}" class="small">Open</a>
                    </div>
                    <div>{{ $prescription->diagnosis ?: $prescription->notes }}</div>
                    <div class="small text-secondary">{{ $prescription->items->pluck('medicine.name')->filter()->join(', ') }}</div>
                </div>
            @empty
                <div class="text-secondary">No prescriptions yet.</div>
            @endforelse
        </div>
    </div>

    <div class="col-xl-5">
        <div class="doctor-card p-3">
            <h2 class="h5 mb-3">Quiz Results</h2>
            @forelse($patient->quizAttempts as $attempt)
                <div class="border rounded p-2 mb-3">
                    <div class="fw-semibold">{{ $attempt->result_category }} · Score {{ $attempt->total_score }}</div>
                    <div class="small text-secondary mb-2">{{ $attempt->created_at->format('d M Y h:i A') }}</div>
                    @foreach($attempt->quizAnswers as $answer)
                        <div class="mb-2 small">
                            <strong>{{ $answer->healthQuestion?->question ?? 'Question unavailable' }}</strong><br>
                            {{ $answer->healthOption?->option_text ?? 'Answer unavailable' }}
                        </div>
                    @endforeach
                </div>
            @empty
                <div class="text-secondary">No quiz history found.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
