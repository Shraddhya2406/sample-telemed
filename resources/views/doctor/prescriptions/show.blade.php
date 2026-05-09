@extends('doctor.layout')

@section('title', 'Prescription')
@section('page-title', 'Prescription for '.$prescription->patient->name)

@section('content')
<div class="doctor-card p-4 print-area">
    <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
        <div>
            <h2 class="h4 mb-1">{{ config('app.name', 'Sample Telemed') }}</h2>
            <div class="text-secondary">Dr. {{ $prescription->doctor->name }}</div>
        </div>
        <button onclick="window.print()" class="btn btn-outline-success no-print"><i class="bi bi-printer me-1"></i> Print</button>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4"><strong>Patient</strong><br>{{ $prescription->patient->name }}</div>
        <div class="col-md-4"><strong>Date</strong><br>{{ $prescription->created_at->format('d M Y') }}</div>
        <div class="col-md-4"><strong>Diagnosis</strong><br>{{ $prescription->diagnosis ?: 'Not specified' }}</div>
    </div>
    <table class="table">
        <thead><tr><th>Medicine</th><th>Dosage</th><th>Duration</th><th>Instructions</th></tr></thead>
        <tbody>
            @foreach($prescription->items as $item)
                <tr>
                    <td>{{ $item->medicine->name }}</td>
                    <td>{{ $item->dosage }}</td>
                    <td>{{ $item->duration }}</td>
                    <td>{{ $item->instructions }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if($prescription->notes)
        <div class="mt-3"><strong>Notes</strong><p class="mb-0">{{ $prescription->notes }}</p></div>
    @endif
</div>
@endsection
