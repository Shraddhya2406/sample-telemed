@extends('doctor.layout')

@section('title', 'Patients')
@section('page-title', 'Patients')

@section('content')
<div class="doctor-card p-3">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Name</th><th>Email</th><th>Appointments</th><th></th></tr></thead>
            <tbody>
                @forelse($patients as $patient)
                    <tr>
                        <td>{{ $patient->name }}</td>
                        <td>{{ $patient->email }}</td>
                        <td>{{ $patient->appointments_count }}</td>
                        <td class="text-end"><a href="{{ route('doctor.patients.show', $patient) }}" class="btn btn-sm btn-outline-success">Open History</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-secondary py-4">No assigned patients yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $patients->links() }}
</div>
@endsection
