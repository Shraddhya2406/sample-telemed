@extends('doctor.layout')

@section('title', 'Create Prescription')
@section('page-title', 'Create Prescription')

@section('content')
<form id="prescription-form" method="POST" action="{{ route('doctor.prescriptions.store') }}">
    @csrf
    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-950">Prescription Details</h2>
                <p class="mt-1 text-sm text-slate-500">Choose an approved appointment, confirm diagnosis, and add medicines.</p>
            </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Appointment</label>
                <select name="appointment_id" class="h-12 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" required>
                    <option value="">Select appointment</option>
                    @foreach($appointments as $appointment)
                        <option value="{{ $appointment->id }}" @selected((string) old('appointment_id', request('appointment_id')) === (string) $appointment->id)>
                            {{ $appointment->patient->name }} - {{ $appointment->appointment_date?->format('d M Y') }} {{ substr($appointment->appointment_time, 0, 5) }}
                        </option>
                    @endforeach
                </select>
                @if($appointments->isEmpty())
                    <p class="mt-2 text-sm text-amber-700">Only confirmed appointments with diagnosis and advice are available.</p>
                @endif
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Diagnosis</label>
                <input name="diagnosis" value="{{ old('diagnosis', $selectedAppointment?->diagnosis) }}" class="h-12 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="Diagnosis or recommendation" required>
            </div>
            <div class="md:col-span-2">
                <label class="mb-2 block text-sm font-semibold text-slate-700">Clinical Notes</label>
                <textarea name="notes" class="min-h-28 w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="Follow-up advice, precautions, or lifestyle instructions">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="mt-8 flex items-center justify-between gap-3 border-t border-slate-200 pt-6">
            <div>
                <h2 class="text-base font-semibold text-slate-950">Medicines</h2>
                <p class="text-sm text-slate-500">Add dosage, duration, and instructions for each medicine.</p>
            </div>
            <button id="add-medicine-item" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-emerald-200 hover:text-emerald-700" type="button">
                <i data-lucide="plus" class="h-4 w-4"></i>
                Add Medicine
            </button>
        </div>

        @php
            $items = collect(old('items', [[]]));
            $itemRows = max($items->count(), 1);
        @endphp

        <div id="medicine-items" class="mt-4 space-y-3">
            @for($i = 0; $i < $itemRows; $i++)
                @php $item = $items->get($i, []); @endphp
                <div class="medicine-item-row rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                    <div class="grid gap-3 lg:grid-cols-[minmax(14rem,1.4fr)_minmax(8rem,0.7fr)_minmax(8rem,0.7fr)_minmax(12rem,1fr)]">
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">Medicine</label>
                            <select name="items[{{ $i }}][medicine_id]" class="medicine-select h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                <option value="">Select medicine</option>
                                @foreach($medicines as $medicine)
                                    <option value="{{ $medicine->id }}" @selected(($item['medicine_id'] ?? null) == $medicine->id)>{{ $medicine->name }} - Rs. {{ number_format($medicine->price, 2) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">Dosage</label>
                            <input name="items[{{ $i }}][dosage]" value="{{ $item['dosage'] ?? '' }}" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="1 tab twice">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">Duration</label>
                            <input name="items[{{ $i }}][duration]" value="{{ $item['duration'] ?? '' }}" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="5 days">
                        </div>
                        <div>
                            <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">Instructions</label>
                            <input name="items[{{ $i }}][instructions]" value="{{ $item['instructions'] ?? '' }}" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="After food">
                        </div>
                    </div>
                </div>
            @endfor
        </div>

        <div id="medicine-error" class="mt-4 hidden rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800"></div>

        <div class="mt-6 flex flex-wrap items-center gap-3">
            <button class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                <i data-lucide="send" class="h-4 w-4"></i>
                Create Prescription
            </button>
            <a href="{{ route('doctor.prescriptions.index') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300">Cancel</a>
        </div>
    </section>

</form>

<template id="medicine-item-template">
    <div class="medicine-item-row rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
        <div class="grid gap-3 lg:grid-cols-[minmax(14rem,1.4fr)_minmax(8rem,0.7fr)_minmax(8rem,0.7fr)_minmax(12rem,1fr)]">
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">Medicine</label>
                <select class="medicine-select h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" data-field="medicine_id">
                    <option value="">Select medicine</option>
                    @foreach($medicines as $medicine)
                        <option value="{{ $medicine->id }}">{{ $medicine->name }} - Rs. {{ number_format($medicine->price, 2) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">Dosage</label>
                <input data-field="dosage" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="1 tab twice">
            </div>
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">Duration</label>
                <input data-field="duration" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="5 days">
            </div>
            <div>
                <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-slate-500">Instructions</label>
                <input data-field="instructions" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" placeholder="After food">
            </div>
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
            if (window.lucide) window.lucide.createIcons();
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

                if (isComplete) hasCompleteMedicine = true;
                else if (hasAnyValue) hasIncompleteMedicine = true;
            });

            if (!hasCompleteMedicine || hasIncompleteMedicine) {
                event.preventDefault();
                error.textContent = hasIncompleteMedicine
                    ? 'Please select medicine, dosage, and duration for each medicine row you fill.'
                    : 'Please add at least one medicine with dosage and duration.';
                error.classList.remove('hidden');
            } else {
                error.classList.add('hidden');
            }
        });
    });
</script>
@endsection
