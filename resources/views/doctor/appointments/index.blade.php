@extends('doctor.layout')

@section('title', 'Appointments')
@section('page-title', 'Appointments')

@section('content')
@include('doctor.partials')
<div class="doctor-card p-3">
    <div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
        <div class="btn-group">
            @foreach(['' => 'All', 'pending' => 'Pending', 'approved' => 'Accepted', 'rejected' => 'Rejected', 'completed' => 'Completed'] as $value => $label)
                <a href="{{ route('doctor.appointments.index', $value ? ['status' => $value] : []) }}" class="btn btn-sm {{ $status === $value || (!$status && $value === '') ? 'btn-success' : 'btn-outline-success' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Patient</th><th>Date</th><th>Time</th><th>Status</th><th>Notes</th><th></th></tr></thead>
            <tbody>
                @forelse($appointments as $appointment)
                    <tr>
                        <td>{{ $appointment->patient->name }}</td>
                        <td>{{ $appointment->appointment_date?->format('d M Y') }}</td>
                        <td>{{ substr($appointment->appointment_time, 0, 5) }}</td>
                        <td><span class="badge text-bg-{{ $statusClasses[$appointment->status] ?? 'secondary' }}">{{ $statusLabels[$appointment->status] ?? ucfirst($appointment->status) }}</span></td>
                        <td class="text-secondary">{{ \Illuminate\Support\Str::limit($appointment->notes ?: $appointment->symptoms, 48) }}</td>
                        <td class="text-end">
                            <div class="d-inline-flex flex-wrap justify-content-end gap-2">
                                @if($appointment->status === 'completed' && $appointment->prescription)
                                    <a href="{{ route('doctor.prescriptions.show', $appointment->prescription) }}" class="btn btn-sm btn-outline-primary">View Prescription</a>
                                @endif
                                <a href="{{ route('doctor.appointments.show', $appointment) }}" class="btn btn-sm btn-outline-success">Manage</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-secondary py-4">No appointments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $appointments->links() }}
</div>
@endsection
