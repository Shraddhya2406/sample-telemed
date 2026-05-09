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
    <div class="col-xl-8">
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

    <div class="col-xl-4" id="availability">
        <div class="doctor-card p-3">
            <h2 class="h5 mb-3">Availability</h2>
            <form id="availability-form" method="POST" action="{{ route('doctor.availability.update') }}">
                @csrf
                @php
                    $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                    $slots = collect(old('slots', $availabilities->values()->map(fn ($slot) => [
                        'day_of_week' => $slot->day_of_week,
                        'start_time' => $slot->start_time ? substr($slot->start_time, 0, 5) : '',
                        'end_time' => $slot->end_time ? substr($slot->end_time, 0, 5) : '',
                    ])->all()));
                    $slotRows = max($slots->count(), 3);
                @endphp
                <div id="availability-slots">
                    @for($i = 0; $i < $slotRows; $i++)
                        @php $slot = $slots->get($i, []); @endphp
                        <div class="border rounded p-2 mb-2 availability-slot-row">
                            <select name="slots[{{ $i }}][day_of_week]" class="form-select form-select-sm mb-2">
                                <option value="">Day</option>
                                @foreach($days as $day)
                                    <option value="{{ $day }}" @selected(($slot['day_of_week'] ?? null) === $day)>{{ $day }}</option>
                                @endforeach
                            </select>
                            <div class="d-flex gap-2">
                                <input type="time" name="slots[{{ $i }}][start_time]" value="{{ $slot['start_time'] ?? '' }}" class="form-control form-control-sm">
                                <input type="time" name="slots[{{ $i }}][end_time]" value="{{ $slot['end_time'] ?? '' }}" class="form-control form-control-sm">
                            </div>
                        </div>
                    @endfor
                </div>
                <button id="add-availability-slot" class="btn btn-outline-success btn-sm w-100 mb-2" type="button">
                    <i class="bi bi-plus-lg"></i> Add Availability
                </button>
                <div id="availability-error" class="alert alert-danger py-2 small d-none"></div>
                <button class="btn btn-success w-100" type="submit">Save Availability</button>
            </form>
        </div>
    </div>
</div>

<template id="availability-slot-template">
    <div class="border rounded p-2 mb-2 availability-slot-row">
        <select class="form-select form-select-sm mb-2" data-field="day_of_week">
            <option value="">Day</option>
            @foreach($days as $day)
                <option value="{{ $day }}">{{ $day }}</option>
            @endforeach
        </select>
        <div class="d-flex gap-2">
            <input type="time" data-field="start_time" class="form-control form-control-sm">
            <input type="time" data-field="end_time" class="form-control form-control-sm">
        </div>
    </div>
</template>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const slotsWrapper = document.getElementById('availability-slots');
        const addButton = document.getElementById('add-availability-slot');
        const form = document.getElementById('availability-form');
        const error = document.getElementById('availability-error');
        const template = document.getElementById('availability-slot-template');

        addButton.addEventListener('click', function () {
            const index = slotsWrapper.querySelectorAll('.availability-slot-row').length;
            const row = template.content.firstElementChild.cloneNode(true);

            row.querySelectorAll('[data-field]').forEach((input) => {
                input.name = `slots[${index}][${input.dataset.field}]`;
            });

            slotsWrapper.appendChild(row);
        });

        function timeToMinutes(time) {
            const parts = time.split(':').map(Number);
            return parts[0] * 60 + parts[1];
        }

        form.addEventListener('submit', function (event) {
            const slotsByDay = {};
            let hasInvalidRange = false;
            let hasOverlap = false;

            slotsWrapper.querySelectorAll('.availability-slot-row').forEach((row) => {
                const day = row.querySelector('[name$="[day_of_week]"]')?.value;
                const start = row.querySelector('[name$="[start_time]"]')?.value;
                const end = row.querySelector('[name$="[end_time]"]')?.value;

                if (!day && !start && !end) {
                    return;
                }

                if (!day || !start || !end) {
                    return;
                }

                const startMinutes = timeToMinutes(start);
                const endMinutes = timeToMinutes(end);

                if (endMinutes <= startMinutes) {
                    hasInvalidRange = true;
                    return;
                }

                slotsByDay[day] = slotsByDay[day] || [];
                slotsByDay[day].push({
                    start: startMinutes,
                    end: endMinutes,
                });
            });

            Object.values(slotsByDay).forEach((daySlots) => {
                daySlots
                    .sort((first, second) => first.start - second.start)
                    .forEach((slot, index, sortedSlots) => {
                        if (index > 0 && slot.start < sortedSlots[index - 1].end) {
                            hasOverlap = true;
                        }
                    });
            });

            if (hasInvalidRange) {
                event.preventDefault();
                error.textContent = 'Availability end time must be after the start time.';
                error.classList.remove('d-none');
            } else if (hasOverlap) {
                event.preventDefault();
                error.textContent = 'Availability slots on the same day cannot overlap. Please adjust repeated or overlapping time ranges.';
                error.classList.remove('d-none');
            } else {
                error.classList.add('d-none');
            }
        });
    });
</script>
@endsection
