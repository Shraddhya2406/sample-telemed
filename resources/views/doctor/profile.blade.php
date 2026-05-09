@extends('doctor.layout')

@section('title', 'Profile')
@section('page-title', 'Profile')

@section('content')
@include('doctor.partials')
@php
    $doctor = auth()->user();
    $profile = $doctor->doctorProfile;
    $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
    $slots = collect(old('slots', $availabilities->values()->map(fn ($slot) => [
        'day_of_week' => $slot->day_of_week,
        'start_time' => $slot->start_time ? substr($slot->start_time, 0, 5) : '',
        'end_time' => $slot->end_time ? substr($slot->end_time, 0, 5) : '',
    ])->all()));
    $slotRows = max($slots->count(), 3);
@endphp

<div class="row g-4">
    <div class="col-xl-7">
        <div class="doctor-card p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <h2 class="h5 mb-0">Professional Details</h2>
                <span class="badge text-bg-light border">{{ $profile?->is_verified ? 'Verified' : 'Verification pending' }}</span>
            </div>

            <form method="POST" action="{{ route('doctor.profile.update') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $doctor->name) }}" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $doctor->email) }}" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label for="specialization" class="form-label">Specialization</label>
                        <input type="text" name="specialization" id="specialization" value="{{ old('specialization', $profile?->specialization) }}" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label for="qualification" class="form-label">Qualification</label>
                        <input type="text" name="qualification" id="qualification" value="{{ old('qualification', $profile?->qualification) }}" class="form-control" placeholder="MBBS, MD">
                    </div>

                    <div class="col-md-6">
                        <label for="experience_years" class="form-label">Experience</label>
                        <div class="input-group">
                            <input type="number" min="0" max="80" name="experience_years" id="experience_years" value="{{ old('experience_years', $profile?->experience_years) }}" class="form-control" required>
                            <span class="input-group-text">years</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="license_number" class="form-label">License Number</label>
                        <input type="text" name="license_number" id="license_number" value="{{ old('license_number', $profile?->license_number) }}" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label for="consultation_fee" class="form-label">Consultation Fee</label>
                        <div class="input-group">
                            <span class="input-group-text">Rs.</span>
                            <input
                                type="number"
                                step="0.01"
                                min="1"
                                max="999999.99"
                                name="consultation_fee"
                                id="consultation_fee"
                                value="{{ old('consultation_fee', $profile?->consultation_fee) }}"
                                class="form-control"
                                placeholder="{{ number_format((float) config('services.appointments.fee', 500), 2, '.', '') }}"
                            >
                        </div>
                        <div class="small text-secondary mt-1">Leave blank to use Rs. {{ number_format((float) config('services.appointments.fee', 500), 2) }}.</div>
                    </div>

                    <div class="col-12">
                        <label for="bio" class="form-label">Bio</label>
                        <textarea name="bio" id="bio" rows="5" class="form-control" placeholder="Briefly describe your practice, clinical interests, and consultation approach.">{{ old('bio', $profile?->bio) }}</textarea>
                    </div>
                </div>

                <button class="btn btn-success mt-3" type="submit">Save Profile</button>
            </form>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="doctor-card p-3">
            <h2 class="h5 mb-3">Availability</h2>
            <form id="availability-form" method="POST" action="{{ route('doctor.availability.update') }}">
                @csrf

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
