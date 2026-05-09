@extends('doctor.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@include('doctor.partials')
<div class="row g-3 mb-4">
    @foreach([
        ['label' => 'Total Patients', 'value' => $stats['patients'], 'icon' => 'bi-people-fill'],
        ['label' => 'Appointments', 'value' => $stats['appointments'], 'icon' => 'bi-calendar2-check-fill'],
        ['label' => 'Prescriptions', 'value' => $stats['prescriptions'], 'icon' => 'bi-prescription2'],
        ['label' => 'Pending Requests', 'value' => $stats['pending'], 'icon' => 'bi-hourglass-split'],
    ] as $card)
        <div class="col-6 col-xl-3">
            <div class="doctor-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-secondary small">{{ $card['label'] }}</div>
                        <div class="fs-3 fw-bold">{{ $card['value'] }}</div>
                    </div>
                    <i class="bi {{ $card['icon'] }} fs-3 text-success"></i>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="doctor-card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 mb-0">Recent Appointments</h2>
                <a href="{{ route('doctor.appointments.index') }}" class="btn btn-sm btn-outline-success">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead><tr><th>Patient</th><th>Date</th><th>Time</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($appointments as $appointment)
                            <tr>
                                <td>{{ $appointment->patient->name }}</td>
                                <td>{{ $appointment->appointment_date?->format('d M Y') }}</td>
                                <td>{{ \Illuminate\Support\Str::of($appointment->appointment_time)->substr(0, 5) }}</td>
                                <td><span class="badge text-bg-{{ $statusClasses[$appointment->status] ?? 'secondary' }}">{{ $statusLabels[$appointment->status] ?? ucfirst($appointment->status) }}</span></td>
                                <td><a href="{{ route('doctor.appointments.show', $appointment) }}" class="btn btn-sm btn-light">Open</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary py-4">No appointments yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
