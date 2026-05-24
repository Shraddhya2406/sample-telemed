@extends('doctor.layout')

@section('title', 'Profile')
@section('page-title', 'Profile')

@section('content')
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
    $defaultFee = (float) config('services.appointments.fee', 500);
@endphp

<div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_25rem]">
    <section class="space-y-4">
        <div class="overflow-hidden rounded-2xl border border-emerald-100 bg-white shadow-sm">
            <div class="bg-emerald-50/80 px-4 py-3">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex min-w-0 items-center gap-4">
                        <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-2xl font-bold text-white shadow-lg shadow-emerald-600/20">
                            {{ str($doctor->name ?? 'D')->substr(0, 1)->upper() }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Doctor Profile</p>
                            <h2 class="truncate text-2xl font-bold text-slate-950">Dr. {{ $doctor->name }}</h2>
                            <p class="mt-1 truncate text-sm text-slate-600">{{ $profile?->specialization ?: 'Clinical specialist' }}</p>
                        </div>
                    </div>

                    <span class="inline-flex w-fit items-center gap-2 rounded-full {{ $profile?->is_verified ? 'bg-emerald-600 text-white' : 'bg-white text-amber-700 ring-1 ring-amber-200' }} px-3 py-1.5 text-xs font-semibold shadow-sm">
                        <i data-lucide="{{ $profile?->is_verified ? 'badge-check' : 'clock' }}" class="h-4 w-4"></i>
                        {{ $profile?->is_verified ? 'Verified' : 'Verification pending' }}
                    </span>
                </div>
            </div>

            <form method="POST" action="{{ route('doctor.profile.update') }}" class="p-4">
                @csrf

                <div class="grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">Name</span>
                        <input type="text" name="name" id="name" value="{{ old('name', $doctor->name) }}" class="mt-1 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" required>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">Email</span>
                        <input type="email" name="email" id="email" value="{{ old('email', $doctor->email) }}" class="mt-1 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" required>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">Specialization</span>
                        <input type="text" name="specialization" id="specialization" value="{{ old('specialization', $profile?->specialization) }}" class="mt-1 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" required>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">Qualification</span>
                        <input type="text" name="qualification" id="qualification" value="{{ old('qualification', $profile?->qualification) }}" class="mt-1 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" placeholder="MBBS, MD">
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">Experience</span>
                        <span class="mt-1 flex h-11 overflow-hidden rounded-lg border border-slate-200 bg-white transition focus-within:border-emerald-500 focus-within:ring-4 focus-within:ring-emerald-100">
                            <input type="number" min="0" max="80" name="experience_years" id="experience_years" value="{{ old('experience_years', $profile?->experience_years) }}" class="min-w-0 flex-1 border-0 px-3 text-sm outline-none" required>
                            <span class="inline-flex items-center border-l border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-500">years</span>
                        </span>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">License Number</span>
                        <input type="text" name="license_number" id="license_number" value="{{ old('license_number', $profile?->license_number) }}" class="mt-1 h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" required>
                    </label>

                    <label class="block">
                        <span class="text-xs font-semibold text-slate-600">Consultation Fee</span>
                        <span class="mt-1 flex h-11 overflow-hidden rounded-lg border border-slate-200 bg-white transition focus-within:border-emerald-500 focus-within:ring-4 focus-within:ring-emerald-100">
                            <span class="inline-flex items-center border-r border-slate-200 bg-slate-50 px-3 text-xs font-semibold text-slate-500">Rs.</span>
                            <input type="number" step="0.01" min="1" max="999999.99" name="consultation_fee" id="consultation_fee" value="{{ old('consultation_fee', $profile?->consultation_fee) }}" class="min-w-0 flex-1 border-0 px-3 text-sm outline-none" placeholder="{{ number_format($defaultFee, 2, '.', '') }}">
                        </span>
                        <span class="mt-1 block text-xs text-slate-500">Leave blank to use Rs. {{ number_format($defaultFee, 2) }}.</span>
                    </label>

                    <label class="block md:col-span-2">
                        <span class="text-xs font-semibold text-slate-600">Bio</span>
                        <textarea name="bio" id="bio" rows="5" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" placeholder="Briefly describe your practice, clinical interests, and consultation approach.">{{ old('bio', $profile?->bio) }}</textarea>
                    </label>
                </div>

                <div class="mt-4 flex justify-end">
                    <button class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:bg-emerald-700" type="submit">
                        <i data-lucide="save" class="h-4 w-4"></i>
                        Save Profile
                    </button>
                </div>
            </form>
        </div>
    </section>

    <aside class="space-y-4">
        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">
                        Availability
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">  {{ $availabilities->count() }} slots</span>
                    </p>
                    <h2 class="mt-1 text-lg font-bold text-slate-950">Weekly Schedule</h2>
                    <p class="mt-1 text-sm text-slate-500">Set consultation windows patients can book.</p>
                </div>
            </div>

            <form id="availability-form" method="POST" action="{{ route('doctor.availability.update') }}" class="mt-4">
                @csrf

                <div id="availability-slots" class="space-y-2">
                    @for($i = 0; $i < $slotRows; $i++)
                        @php $slot = $slots->get($i, []); @endphp
                        <div class="availability-slot-row rounded-xl border border-slate-200 bg-slate-50/70 p-3">
                            <div class="flex gap-2">
                                <select name="slots[{{ $i }}][day_of_week]" class="h-10 min-w-0 flex-1 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                    <option value="">Day</option>
                                    @foreach($days as $day)
                                        <option value="{{ $day }}" @selected(($slot['day_of_week'] ?? null) === $day)>{{ $day }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-red-200 bg-white text-red-600 transition hover:bg-red-50" data-remove-availability aria-label="Remove availability">
                                    <i data-lucide="trash-2" class="h-4 w-4"></i>
                                </button>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <input type="time" name="slots[{{ $i }}][start_time]" value="{{ $slot['start_time'] ?? '' }}" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                                <input type="time" name="slots[{{ $i }}][end_time]" value="{{ $slot['end_time'] ?? '' }}" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                            </div>
                        </div>
                    @endfor
                </div>

                <button id="add-availability-slot" class="mt-3 inline-flex h-10 w-full items-center justify-center gap-2 rounded-lg border border-emerald-200 bg-white text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50" type="button">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    Add Availability
                </button>
                <div id="availability-error" class="mt-3 hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-medium text-red-800"></div>
                <button class="mt-3 inline-flex h-10 w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-3 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:bg-emerald-700" type="submit">
                    <i data-lucide="calendar-check" class="h-4 w-4"></i>
                    Save Availability
                </button>
            </form>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-4 text-sm shadow-sm">
            <h2 class="font-semibold text-slate-950">Profile Snapshot</h2>
            <dl class="mt-3 space-y-2">
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Specialization</dt>
                    <dd class="min-w-0 truncate text-right font-semibold text-slate-900">{{ $profile?->specialization ?: 'N/A' }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Experience</dt>
                    <dd class="font-semibold text-slate-900">{{ filled($profile?->experience_years) ? $profile->experience_years.' years' : 'N/A' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Fee</dt>
                    <dd class="font-semibold text-slate-900">Rs. {{ number_format((float) ($profile?->consultation_fee ?? $defaultFee), 2) }}</dd>
                </div>
            </dl>
        </section>
    </aside>
</div>

<template id="availability-slot-template">
    <div class="availability-slot-row rounded-xl border border-slate-200 bg-slate-50/70 p-3">
        <div class="flex gap-2">
            <select class="h-10 min-w-0 flex-1 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100" data-field="day_of_week">
                <option value="">Day</option>
                @foreach($days as $day)
                    <option value="{{ $day }}">{{ $day }}</option>
                @endforeach
            </select>
            <button type="button" class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-red-200 bg-white text-red-600 transition hover:bg-red-50" data-remove-availability aria-label="Remove availability">
                <i data-lucide="trash-2" class="h-4 w-4"></i>
            </button>
        </div>
        <div class="mt-2 grid grid-cols-2 gap-2">
            <input type="time" data-field="start_time" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
            <input type="time" data-field="end_time" class="h-10 rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
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

        function reindexSlots() {
            slotsWrapper.querySelectorAll('.availability-slot-row').forEach((row, index) => {
                row.querySelectorAll('[data-field], [name$="[day_of_week]"], [name$="[start_time]"], [name$="[end_time]"]').forEach((input) => {
                    const field = input.dataset.field || input.name.match(/\[([^\]]+)\]$/)?.[1];

                    if (field) {
                        input.name = `slots[${index}][${field}]`;
                    }
                });
            });
        }

        addButton.addEventListener('click', function () {
            const index = slotsWrapper.querySelectorAll('.availability-slot-row').length;
            const row = template.content.firstElementChild.cloneNode(true);

            row.querySelectorAll('[data-field]').forEach((input) => {
                input.name = `slots[${index}][${input.dataset.field}]`;
            });

            slotsWrapper.appendChild(row);
            if (window.lucide) window.lucide.createIcons();
        });

        slotsWrapper.addEventListener('click', function (event) {
            const removeButton = event.target.closest('[data-remove-availability]');

            if (!removeButton) {
                return;
            }

            removeButton.closest('.availability-slot-row')?.remove();
            reindexSlots();
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
                error.classList.remove('hidden');
            } else if (hasOverlap) {
                event.preventDefault();
                error.textContent = 'Availability slots on the same day cannot overlap. Please adjust repeated or overlapping time ranges.';
                error.classList.remove('hidden');
            } else {
                error.classList.add('hidden');
            }
        });
    });
</script>
@endsection
