@extends('doctor.layout')

@section('title', 'Prescriptions')
@section('page-title', 'Prescriptions')

@section('content')
<div class="doctor-card p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">Prescription Records</h2>
        <a href="{{ route('doctor.prescriptions.create') }}" class="btn btn-success btn-sm"><i class="bi bi-plus-circle me-1"></i> New</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Patient</th><th>Diagnosis</th><th>Medicines</th><th>Date</th><th></th></tr></thead>
            <tbody>
                @forelse($prescriptions as $prescription)
                    <tr>
                        <td>{{ $prescription->patient->name }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($prescription->diagnosis ?: $prescription->notes, 60) }}</td>
                        <td>{{ $prescription->items->count() }}</td>
                        <td>{{ $prescription->created_at->format('d M Y') }}</td>
                        <td class="text-end"><a href="{{ route('doctor.prescriptions.show', $prescription) }}" class="btn btn-sm btn-outline-success">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-secondary py-4">No prescriptions created yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $prescriptions->links() }}
</div>
@endsection
