@extends('doctor.layout')

@section('title', 'Create Prescription')
@section('page-title', 'Create Prescription')

@section('content')
<form id="prescription-form" method="POST" action="{{ route('doctor.prescriptions.store') }}" class="doctor-card p-3">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Appointment</label>
            <select name="appointment_id" class="form-select" required>
                <option value="">Select appointment</option>
                @foreach($appointments as $appointment)
                    <option value="{{ $appointment->id }}" @selected((string) old('appointment_id', request('appointment_id')) === (string) $appointment->id)>
                        {{ $appointment->patient->name }} - {{ $appointment->appointment_date?->format('d M Y') }} {{ substr($appointment->appointment_time, 0, 5) }}
                    </option>
                @endforeach
            </select>
            @if($appointments->isEmpty())
                <div class="form-text">Only accepted appointments with diagnosis and advice are available for prescription creation.</div>
            @endif
        </div>
        <div class="col-md-6">
            <label class="form-label">Diagnosis</label>
            <input name="diagnosis" value="{{ old('diagnosis', $selectedAppointment?->diagnosis) }}" class="form-control" placeholder="Diagnosis or override recommendation" required>
        </div>
        <div class="col-12">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
        </div>
    </div>

    <hr>
    <h2 class="h5 mb-3">Medicines</h2>
    @php
        $items = collect(old('items', [[]]));
        $itemRows = max($items->count(), 1);
    @endphp

    <div id="medicine-items">
        @for($i = 0; $i < $itemRows; $i++)
            @php $item = $items->get($i, []); @endphp
            <div class="row g-2 align-items-end border rounded p-2 mb-2 medicine-item-row">
                <div class="col-md-4">
                    <label class="form-label">Medicine</label>
                    <select name="items[{{ $i }}][medicine_id]" class="form-select">
                        <option value="">Select medicine</option>
                        @foreach($medicines as $medicine)
                            <option value="{{ $medicine->id }}" @selected(($item['medicine_id'] ?? null) == $medicine->id)>{{ $medicine->name }} - Rs. {{ number_format($medicine->price, 2) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dosage</label>
                    <input name="items[{{ $i }}][dosage]" value="{{ $item['dosage'] ?? '' }}" class="form-control" placeholder="1 tab twice">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Duration</label>
                    <input name="items[{{ $i }}][duration]" value="{{ $item['duration'] ?? '' }}" class="form-control" placeholder="5 days">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Instructions</label>
                    <input name="items[{{ $i }}][instructions]" value="{{ $item['instructions'] ?? '' }}" class="form-control" placeholder="After food">
                </div>
            </div>
        @endfor
    </div>

    <button id="add-medicine-item" class="btn btn-outline-success btn-sm mt-1" type="button">
        <i class="bi bi-plus-lg"></i> Add Medicine
    </button>
    <div id="medicine-error" class="alert alert-danger py-2 small mt-3 d-none"></div>
    <button class="btn btn-success mt-2">Create Prescription</button>
</form>

<template id="medicine-item-template">
    <div class="row g-2 align-items-end border rounded p-2 mb-2 medicine-item-row">
        <div class="col-md-4">
            <label class="form-label">Medicine</label>
            <select class="form-select" data-field="medicine_id">
                <option value="">Select medicine</option>
                @foreach($medicines as $medicine)
                    <option value="{{ $medicine->id }}">{{ $medicine->name }} - Rs. {{ number_format($medicine->price, 2) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Dosage</label>
            <input data-field="dosage" class="form-control" placeholder="1 tab twice">
        </div>
        <div class="col-md-2">
            <label class="form-label">Duration</label>
            <input data-field="duration" class="form-control" placeholder="5 days">
        </div>
        <div class="col-md-4">
            <label class="form-label">Instructions</label>
            <input data-field="instructions" class="form-control" placeholder="After food">
        </div>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('prescription-form');
        const itemsWrapper = document.getElementById('medicine-items');
        const addButton = document.getElementById('add-medicine-item');
        const error = document.getElementById('medicine-error');
        const template = document.getElementById('medicine-item-template');

        addButton.addEventListener('click', function () {
            const index = itemsWrapper.querySelectorAll('.medicine-item-row').length;
            const row = template.content.firstElementChild.cloneNode(true);

            row.querySelectorAll('[data-field]').forEach((input) => {
                input.name = `items[${index}][${input.dataset.field}]`;
            });

            itemsWrapper.appendChild(row);
        });

        form.addEventListener('submit', function (event) {
            let hasCompleteMedicine = false;
            let hasIncompleteMedicine = false;

            itemsWrapper.querySelectorAll('.medicine-item-row').forEach((row) => {
                const medicine = row.querySelector('[name$="[medicine_id]"]')?.value;
                const dosage = row.querySelector('[name$="[dosage]"]')?.value.trim();
                const duration = row.querySelector('[name$="[duration]"]')?.value.trim();
                const instructions = row.querySelector('[name$="[instructions]"]')?.value.trim();
                const hasAnyValue = Boolean(medicine || dosage || duration || instructions);
                const isComplete = Boolean(medicine && dosage && duration);

                if (isComplete) {
                    hasCompleteMedicine = true;
                } else if (hasAnyValue) {
                    hasIncompleteMedicine = true;
                }
            });

            if (!hasCompleteMedicine || hasIncompleteMedicine) {
                event.preventDefault();
                error.textContent = hasIncompleteMedicine
                    ? 'Please select medicine, dosage, and duration for each medicine row you fill.'
                    : 'Please add at least one medicine with dosage and duration.';
                error.classList.remove('d-none');
            } else {
                error.classList.add('d-none');
            }
        });
    });
</script>
@endsection
