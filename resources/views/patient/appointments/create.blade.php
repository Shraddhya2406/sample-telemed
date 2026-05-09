@extends('layouts.app')

@section('title', 'Book Appointment')

@section('content')
@php
    $doctorSlots = $doctors->mapWithKeys(fn ($doctor) => [
        $doctor->id => [
            'availability' => $doctor->doctorAvailabilities
                ?->map(fn ($availability) => [
                    'day' => $availability->day_of_week,
                    'start' => substr($availability->start_time, 0, 5),
                    'end' => substr($availability->end_time, 0, 5),
                ])
                ->values()
                ->all() ?? [],
            'booked' => $bookedSlots->get($doctor->id, collect())->toArray(),
        ],
    ]);
@endphp

<div class="max-w-6xl mx-auto">
    <div class="bg-white p-6 rounded shadow mb-6">
        <h1 class="text-2xl font-bold">Book Appointment</h1>
        <p class="text-gray-600">Choose a doctor and request a consultation slot.</p>
    </div>

    <form id="appointment_form" method="POST" action="{{ route('patient.appointments.store') }}" class="bg-white p-6 rounded shadow">
        @csrf
        <input type="hidden" name="appointment_date" id="appointment_date" value="{{ old('appointment_date') }}" required>
        <input type="hidden" name="appointment_time" id="appointment_time" value="{{ old('appointment_time') }}" required>

        <div class="grid grid-cols-1 gap-4">
            <div>
                <label class="block font-semibold mb-2">Doctor</label>
                <select name="doctor_id" id="doctor_id" class="w-full border rounded px-3 py-2" required>
                    <option value="">Select doctor</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>
                            Dr. {{ $doctor->name }} - {{ $doctor->doctorProfile?->specialization ?? 'General Medicine' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-2">Appointment Slot</label>
                <div class="border rounded p-4 bg-gray-50 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <div id="selected_slot_label" class="font-semibold text-gray-900">No slot selected</div>
                        <div id="slot_hint" class="text-sm text-gray-600">Select a doctor first, then choose a date and time.</div>
                    </div>
                    <button id="open_slot_modal" type="button" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-600" disabled>
                        Choose Slot
                    </button>
                </div>
                <div id="slot_empty" class="hidden mt-3 border rounded p-4 text-gray-600 bg-gray-50"></div>
                <div id="slot_error" class="hidden mt-3 border border-red-200 rounded p-3 text-red-700 bg-red-50 text-sm"></div>
            </div>

            <div>
                <label class="block font-semibold mb-2">Symptoms</label>
                <textarea name="symptoms" rows="4" class="w-full border rounded px-3 py-2" placeholder="Describe symptoms, duration, and severity">{{ old('symptoms') }}</textarea>
            </div>

            <div>
                <label class="block font-semibold mb-2">Notes</label>
                <textarea name="notes" rows="3" class="w-full border rounded px-3 py-2" placeholder="Anything else the doctor should know">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700" type="submit">Send Request</button>
            <a href="{{ route('patient.appointments.index') }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300">Cancel</a>
        </div>
    </form>
</div>

<div id="slot_modal" class="fixed inset-0 z-50 hidden">
    <div id="slot_modal_backdrop" class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded shadow-xl w-full max-w-xl overflow-hidden p-4">
            <div class="px-5 py-4 border-b flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold">Choose Appointment Slot</h2>
                    <p id="slot_modal_subtitle" class="text-sm text-gray-600">Pick a date and time.</p>
                </div>
                <button id="close_slot_modal" type="button" class="text-gray-500 hover:text-gray-800 text-2xl leading-none" aria-label="Close slot picker">&times;</button>
            </div>

            <div class="p-5 space-y-5">
                <div>
                    <div class="font-semibold mb-2">Available Dates</div>
                    <div id="slot_dates" class="flex gap-2 overflow-x-auto pb-1"></div>
                </div>

                <div>
                    <div id="selected_date_label" class="font-semibold mb-2 text-sm text-gray-700">Time Slots</div>
                    <div id="slot_times" class="flex flex-wrap gap-2"></div>
                    <div id="slot_time_empty" class="hidden border rounded p-4 text-gray-600 bg-gray-50"></div>
                </div>
            </div>

            <div class="px-5 py-4 border-t bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div id="modal_selected_label" class="text-sm text-gray-600">No slot selected</div>
                <button id="confirm_slot" type="button" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-600" disabled>
                    Confirm Slot
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const doctorSlots = @json($doctorSlots);
        const form = document.getElementById('appointment_form');
        const doctorSelect = document.getElementById('doctor_id');
        const empty = document.getElementById('slot_empty');
        const slotError = document.getElementById('slot_error');
        const dateInput = document.getElementById('appointment_date');
        const timeInput = document.getElementById('appointment_time');
        const selectedLabel = document.getElementById('selected_slot_label');
        const slotHint = document.getElementById('slot_hint');
        const openModalButton = document.getElementById('open_slot_modal');
        const modal = document.getElementById('slot_modal');
        const modalBackdrop = document.getElementById('slot_modal_backdrop');
        const closeModalButton = document.getElementById('close_slot_modal');
        const modalSubtitle = document.getElementById('slot_modal_subtitle');
        const dateList = document.getElementById('slot_dates');
        const timeList = document.getElementById('slot_times');
        const timeEmpty = document.getElementById('slot_time_empty');
        const selectedDateLabel = document.getElementById('selected_date_label');
        const modalSelectedLabel = document.getElementById('modal_selected_label');
        const confirmButton = document.getElementById('confirm_slot');
        const oldDate = dateInput.value;
        const oldTime = timeInput.value ? timeInput.value.substring(0, 5) : '';
        let availableDays = [];
        let activeDate = '';
        let pendingSlot = null;

        function pad(value) {
            return String(value).padStart(2, '0');
        }

        function formatDate(date) {
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
        }

        function todayDate() {
            return formatDate(new Date());
        }

        function showSlotError(message) {
            slotError.textContent = message;
            slotError.classList.remove('hidden');
        }

        function clearSlotError() {
            slotError.textContent = '';
            slotError.classList.add('hidden');
        }

        function timeToMinutes(time) {
            const parts = time.split(':').map(Number);
            return parts[0] * 60 + parts[1];
        }

        function minutesToTime(minutes) {
            return `${pad(Math.floor(minutes / 60))}:${pad(minutes % 60)}`;
        }

        function dayName(date) {
            return date.toLocaleDateString('en-US', { weekday: 'long' });
        }

        function displayDate(date) {
            return date.toLocaleDateString('en-US', { weekday: 'short', day: '2-digit', month: 'short' });
        }

        function chipDate(date) {
            return date.toLocaleDateString('en-US', { weekday: 'short', day: '2-digit' });
        }

        function longDisplayDate(date) {
            return date.toLocaleDateString('en-US', { weekday: 'long', day: '2-digit', month: 'long' });
        }

        function selectedText(date, time) {
            const day = availableDays.find((item) => item.isoDate === date);
            return day ? `${longDisplayDate(day.date)} at ${time}` : `${date} at ${time}`;
        }

        function resetSelectedSlot() {
            dateInput.value = '';
            timeInput.value = '';
            pendingSlot = null;
            selectedLabel.textContent = 'No slot selected';
            modalSelectedLabel.textContent = 'No slot selected';
            confirmButton.disabled = true;
        }

        function setSelected(date, time) {
            dateInput.value = date;
            timeInput.value = time;
            selectedLabel.textContent = selectedText(date, time);
            slotHint.textContent = 'You can change this slot before sending the request.';
            modalSelectedLabel.textContent = selectedText(date, time);
            clearSlotError();
        }

        function buildAvailableDays() {
            const doctorId = doctorSelect.value;
            const config = doctorSlots[doctorId];
            const days = [];

            if (!config) {
                return days;
            }

            const today = new Date();

            for (let offset = 0; offset < 21; offset++) {
                const date = new Date(today);
                date.setDate(today.getDate() + offset);
                const isoDate = formatDate(date);
                const availability = config.availability.filter((slot) => slot.day === dayName(date));
                const bookedTimes = config.booked[isoDate] || [];
                const availableTimes = [];

                availability.forEach((slot) => {
                    for (let minutes = timeToMinutes(slot.start); minutes < timeToMinutes(slot.end); minutes += 30) {
                        const time = minutesToTime(minutes);

                        if (!bookedTimes.includes(time)) {
                            availableTimes.push(time);
                        }
                    }
                });

                if (availableTimes.length > 0) {
                    days.push({ date, isoDate, times: availableTimes });
                }
            }

            return days;
        }

        function renderTimeSlots(day) {
            timeList.innerHTML = '';
            timeEmpty.classList.add('hidden');
            timeEmpty.textContent = '';

            if (!day) {
                selectedDateLabel.textContent = 'Time Slots';
                timeEmpty.textContent = 'Select a date to view time slots.';
                timeEmpty.classList.remove('hidden');
                return;
            }

            modalSubtitle.textContent = longDisplayDate(day.date);
            selectedDateLabel.textContent = `Time Slots for ${displayDate(day.date)}`;

            day.times.forEach((time) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.dataset.slotButton = 'true';
                button.className = day.isoDate === dateInput.value && time === timeInput.value
                    ? 'border rounded-full px-4 py-2 text-sm bg-green-600 text-white border-green-600'
                    : 'border rounded-full px-4 py-2 text-sm bg-white hover:bg-green-50 hover:border-green-500';
                button.textContent = time;
                button.addEventListener('click', () => {
                    pendingSlot = { date: day.isoDate, time };
                    modalSelectedLabel.textContent = selectedText(day.isoDate, time);
                    confirmButton.disabled = false;

                    timeList.querySelectorAll('[data-slot-button]').forEach((slotButton) => {
                        slotButton.className = 'border rounded-full px-4 py-2 text-sm bg-white hover:bg-green-50 hover:border-green-500';
                    });

                    button.className = 'border rounded-full px-4 py-2 text-sm bg-green-600 text-white border-green-600';
                });

                timeList.appendChild(button);
            });
        }

        function renderDatePicker() {
            dateList.innerHTML = '';
            activeDate = activeDate || availableDays[0]?.isoDate || '';

            availableDays.forEach((day) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.dataset.dateButton = 'true';
                button.className = day.isoDate === activeDate
                    ? 'border rounded-full px-4 py-2 text-sm whitespace-nowrap bg-green-600 text-white border-green-600'
                    : 'border rounded-full px-4 py-2 text-sm whitespace-nowrap bg-white hover:bg-green-50 hover:border-green-500';
                button.textContent = chipDate(day.date);
                button.addEventListener('click', () => {
                    activeDate = day.isoDate;
                    pendingSlot = null;
                    modalSelectedLabel.textContent = dateInput.value && timeInput.value ? selectedText(dateInput.value, timeInput.value) : 'No slot selected';
                    confirmButton.disabled = true;
                    renderDatePicker();
                    renderTimeSlots(day);
                });

                dateList.appendChild(button);
            });

            renderTimeSlots(availableDays.find((day) => day.isoDate === activeDate));
        }

        function refreshSlotState() {
            availableDays = buildAvailableDays();
            empty.classList.add('hidden');
            empty.textContent = '';
            clearSlotError();

            if (!doctorSelect.value) {
                openModalButton.disabled = true;
                slotHint.textContent = 'Select a doctor first, then choose a date and time.';
                empty.textContent = 'Select a doctor to view available slots.';
                empty.classList.remove('hidden');
                resetSelectedSlot();
                return;
            }

            if (availableDays.length === 0) {
                openModalButton.disabled = true;
                slotHint.textContent = 'No available slots found for this doctor.';
                empty.textContent = 'No available slots found for this doctor.';
                empty.classList.remove('hidden');
                resetSelectedSlot();
                return;
            }

            openModalButton.disabled = false;
            slotHint.textContent = `${availableDays.length} upcoming date${availableDays.length === 1 ? '' : 's'} available.`;
            activeDate = availableDays.find((day) => day.isoDate === dateInput.value)?.isoDate || availableDays[0].isoDate;
            renderDatePicker();
        }

        function openModal() {
            if (openModalButton.disabled) {
                return;
            }

            pendingSlot = null;
            modalSelectedLabel.textContent = dateInput.value && timeInput.value ? selectedText(dateInput.value, timeInput.value) : 'No slot selected';
            confirmButton.disabled = true;
            renderDatePicker();
            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        doctorSelect.addEventListener('change', function () {
            activeDate = '';
            resetSelectedSlot();
            refreshSlotState();
        });

        form.addEventListener('submit', function (event) {
            const selectedDay = availableDays.find((day) => day.isoDate === dateInput.value);
            const validDateFormat = /^\d{4}-\d{2}-\d{2}$/.test(dateInput.value);
            const validTimeFormat = /^\d{2}:\d{2}$/.test(timeInput.value);

            clearSlotError();

            if (!doctorSelect.value) {
                event.preventDefault();
                showSlotError('Please select a doctor first.');
                doctorSelect.focus();
                return;
            }

            if (!dateInput.value || !timeInput.value) {
                event.preventDefault();
                showSlotError('Please choose an appointment date and time slot.');
                openModal();
                return;
            }

            if (!validDateFormat || !validTimeFormat) {
                event.preventDefault();
                showSlotError('Please choose a valid appointment date and time slot.');
                openModal();
                return;
            }

            if (dateInput.value < todayDate()) {
                event.preventDefault();
                showSlotError('Appointment date cannot be in the past.');
                openModal();
                return;
            }

            if (!selectedDay || !selectedDay.times.includes(timeInput.value)) {
                event.preventDefault();
                showSlotError('Selected appointment slot is no longer available. Please choose another slot.');
                resetSelectedSlot();
                refreshSlotState();
                openModal();
            }
        });

        openModalButton.addEventListener('click', openModal);
        closeModalButton.addEventListener('click', closeModal);
        modalBackdrop.addEventListener('click', closeModal);
        confirmButton.addEventListener('click', function () {
            if (!pendingSlot) {
                return;
            }

            setSelected(pendingSlot.date, pendingSlot.time);
            closeModal();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });

        refreshSlotState();

        if (oldDate && oldTime && availableDays.some((day) => day.isoDate === oldDate && day.times.includes(oldTime))) {
            setSelected(oldDate, oldTime);
            activeDate = oldDate;
            renderDatePicker();
        }
    });
</script>
@endsection
