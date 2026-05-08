@extends('doctor.layout')

@section('title', 'Create Prescription')
@section('page-title', 'Create Prescription')

@section('content')
<form method="POST" action="{{ route('doctor.prescriptions.store') }}" class="doctor-card p-3">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Appointment</label>
            <select name="appointment_id" class="form-select" required>
                <option value="">Select appointment</option>
                @foreach($appointments as $appointment)
                    <option value="{{ $appointment->id }}" @selected((string) old('appointment_id', request('appointment_id')) === (string) $appointment->id)>
                        {{ $appointment->patient->name }} · {{ $appointment->appointment_date?->format('d M Y') }} {{ substr($appointment->appointment_time, 0, 5) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Diagnosis</label>
            <input name="diagnosis" value="{{ old('diagnosis') }}" class="form-control" placeholder="Diagnosis or override recommendation">
        </div>
        <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
        </div>
    </div>

    <hr>
    <h2 class="h5 mb-3">Medicines</h2>
    @for($i = 0; $i < 3; $i++)
        <div class="row g-2 align-items-end border rounded p-2 mb-2">
            <div class="col-md-4">
                <label class="form-label">Medicine</label>
                <select name="items[{{ $i }}][medicine_id]" class="form-select">
                    <option value="">Select medicine</option>
                    @foreach($medicines as $medicine)
                        <option value="{{ $medicine->id }}" @selected(old("items.$i.medicine_id") == $medicine->id)>{{ $medicine->name }} · Rs. {{ number_format($medicine->price, 2) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Dosage</label>
                <input name="items[{{ $i }}][dosage]" value="{{ old("items.$i.dosage") }}" class="form-control" placeholder="1 tab twice">
            </div>
            <div class="col-md-2">
                <label class="form-label">Duration</label>
                <input name="items[{{ $i }}][duration]" value="{{ old("items.$i.duration") }}" class="form-control" placeholder="5 days">
            </div>
            <div class="col-md-4">
                <label class="form-label">Instructions</label>
                <input name="items[{{ $i }}][instructions]" value="{{ old("items.$i.instructions") }}" class="form-control" placeholder="After food">
            </div>
        </div>
    @endfor

    <button class="btn btn-success mt-2">Create Prescription</button>
</form>
@endsection
