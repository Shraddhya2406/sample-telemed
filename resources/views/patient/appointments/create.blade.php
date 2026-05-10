@extends('layouts.patient')

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
            'fee' => filled($doctor->doctorProfile?->consultation_fee)
                ? (float) $doctor->doctorProfile->consultation_fee
                : (float) $defaultAppointmentFee,
        ],
    ]);
@endphp

<div class="max-w-6xl mx-auto">
    <div class="bg-white p-6 rounded shadow mb-6 dark:bg-slate-900 dark:border dark:border-slate-800">
        <h1 class="text-2xl font-bold text-slate-950 dark:text-white">Book Appointment</h1>
        <p class="text-gray-600 dark:text-slate-300">Choose a doctor, select a consultation slot, and complete online payment.</p>
    </div>

    <form id="appointment_form" method="POST" action="{{ route('patient.appointments.store') }}" class="bg-white p-6 rounded shadow dark:bg-slate-900 dark:border dark:border-slate-800">
        @csrf
        <input type="hidden" name="appointment_date" id="appointment_date" value="{{ old('appointment_date') }}" required>
        <input type="hidden" name="appointment_time" id="appointment_time" value="{{ old('appointment_time') }}" required>

        <div id="payment_message" class="hidden mb-4 px-4 py-3 rounded"></div>

        <div class="grid grid-cols-1 gap-4">
            <div>
                <label class="block font-semibold mb-2 text-slate-950 dark:text-white">Doctor</label>
                <select name="doctor_id" id="doctor_id" class="w-full border rounded px-3 py-2 dark:bg-slate-950 dark:border-slate-700 dark:text-slate-100" required>
                    <option value="">Select doctor</option>
                    @foreach($doctors as $doctor)
                        <option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>
                            Dr. {{ $doctor->name }} - {{ $doctor->doctorProfile?->specialization ?? 'General Medicine' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold mb-2 text-slate-950 dark:text-white">Appointment Slot</label>
                <div class="border rounded p-4 bg-gray-50 flex flex-col md:flex-row md:items-center md:justify-between gap-3 dark:bg-slate-950 dark:border-slate-800">
                    <div>
                        <div id="selected_slot_label" class="font-semibold text-gray-900 dark:text-white">No slot selected</div>
                        <div id="slot_hint" class="text-sm text-gray-600 dark:text-slate-400">Select a doctor first, then choose a date and time.</div>
                    </div>
                    <button id="open_slot_modal" type="button" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-600" disabled>
                        Choose Slot
                    </button>
                </div>
                <div id="slot_empty" class="hidden mt-3 border rounded p-4 text-gray-600 bg-gray-50 dark:bg-slate-950 dark:border-slate-800 dark:text-slate-400"></div>
                <div id="slot_error" class="hidden mt-3 border border-red-200 rounded p-3 text-red-700 bg-red-50 text-sm dark:border-red-900 dark:bg-red-950 dark:text-red-200"></div>
            </div>

            <div>
                <label class="block font-semibold mb-2 text-slate-950 dark:text-white">Symptoms</label>
                <textarea name="symptoms" rows="4" class="w-full border rounded px-3 py-2 dark:bg-slate-950 dark:border-slate-700 dark:text-slate-100" placeholder="Describe symptoms, duration, and severity">{{ old('symptoms') }}</textarea>
            </div>

            <div>
                <label class="block font-semibold mb-2 text-slate-950 dark:text-white">Notes</label>
                <textarea name="notes" rows="3" class="w-full border rounded px-3 py-2 dark:bg-slate-950 dark:border-slate-700 dark:text-slate-100" placeholder="Anything else the doctor should know">{{ old('notes') }}</textarea>
            </div>

            <div class="border rounded p-4 bg-green-50 border-green-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3 dark:bg-green-950/40 dark:border-green-900">
                <div>
                    <div class="font-semibold text-gray-900 dark:text-green-100">Online Appointment Payment</div>
                    <div class="text-sm text-gray-600 dark:text-green-300">Your appointment request is created after payment is verified.</div>
                </div>
                <div id="appointment_fee_label" class="text-xl font-bold text-green-700">Rs. {{ number_format($defaultAppointmentFee, 2) }}</div>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button id="pay_book_button" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center gap-2 disabled:bg-gray-400 disabled:cursor-not-allowed" type="submit">
                <svg id="payment_spinner" class="w-4 h-4 animate-spin hidden" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                <span id="payment_button_text">Pay & Book Appointment</span>
            </button>
            <a href="{{ route('patient.appointments.index') }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">Cancel</a>
        </div>
    </form>
</div>

<div id="slot_modal" class="fixed inset-0 z-50 hidden">
    <div id="slot_modal_backdrop" class="absolute inset-0 bg-black bg-opacity-50"></div>
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded shadow-xl w-full max-w-xl overflow-hidden p-4 dark:bg-slate-900 dark:border dark:border-slate-800">
            <div class="px-5 py-4 border-b flex items-center justify-between gap-3 dark:border-slate-800">
                <div>
                    <h2 class="text-lg font-bold text-slate-950 dark:text-white">Choose Appointment Slot</h2>
                    <p id="slot_modal_subtitle" class="text-sm text-gray-600 dark:text-slate-400">Pick a date and time.</p>
                </div>
                <button id="close_slot_modal" type="button" class="text-gray-500 hover:text-gray-800 text-2xl leading-none dark:text-slate-400 dark:hover:text-white" aria-label="Close slot picker">&times;</button>
            </div>

            <div class="p-5 space-y-5">
                <div>
                    <div class="font-semibold mb-2 text-slate-950 dark:text-white">Available Dates</div>
                    <div id="slot_dates" class="flex gap-2 overflow-x-auto pb-1"></div>
                </div>

                <div>
                    <div id="selected_date_label" class="font-semibold mb-2 text-sm text-gray-700 dark:text-slate-300">Time Slots</div>
                    <div id="slot_times" class="flex flex-wrap gap-2"></div>
                    <div id="slot_time_empty" class="hidden border rounded p-4 text-gray-600 bg-gray-50 dark:bg-slate-950 dark:border-slate-800 dark:text-slate-400"></div>
                </div>
            </div>

            <div class="px-5 py-4 border-t bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 dark:bg-slate-950 dark:border-slate-800">
                <div id="modal_selected_label" class="text-sm text-gray-600 dark:text-slate-400">No slot selected</div>
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
        const razorpayCheckoutUrl = 'https://checkout.razorpay.com/v1/checkout.js';
        const csrfToken = @json(csrf_token());
        const keyId = @json(config('services.razorpay.key_id'));
        const createPaymentOrderUrl = @json(route('patient.appointments.payment.order'));
        const verifyPaymentUrl = @json(route('patient.appointments.payment.verify'));
        const userName = @json(auth()->user()->name ?? '');
        const userEmail = @json(auth()->user()->email ?? '');
        const userContact = @json(auth()->user()->phone ?? '');
        const form = document.getElementById('appointment_form');
        const doctorSelect = document.getElementById('doctor_id');
        const feeLabel = document.getElementById('appointment_fee_label');
        const payButton = document.getElementById('pay_book_button');
        const paymentSpinner = document.getElementById('payment_spinner');
        const paymentButtonText = document.getElementById('payment_button_text');
        const paymentMessage = document.getElementById('payment_message');
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

        function setPaymentLoading(isLoading, text) {
            payButton.disabled = isLoading;
            paymentSpinner.classList.toggle('hidden', !isLoading);
            paymentButtonText.textContent = text || 'Pay & Book Appointment';
        }

        function showPaymentMessage(text, type) {
            paymentMessage.textContent = text;
            paymentMessage.className = 'mb-4 px-4 py-3 rounded ' + (
                type === 'error'
                    ? 'bg-red-50 border border-red-200 text-red-800'
                    : 'bg-green-50 border border-green-200 text-green-800'
            );
        }

        function loadRazorpayCheckout() {
            if (window.Razorpay) {
                return Promise.resolve();
            }

            const existingScript = document.querySelector('script[data-razorpay-checkout]');
            if (existingScript) {
                return new Promise((resolve, reject) => {
                    existingScript.addEventListener('load', () => resolve(), { once: true });
                    existingScript.addEventListener('error', () => reject(new Error('Unable to load Razorpay Checkout. Check your connection and try again.')), { once: true });
                });
            }

            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = razorpayCheckoutUrl;
                script.async = true;
                script.dataset.razorpayCheckout = 'true';
                script.onload = () => {
                    if (window.Razorpay) {
                        resolve();
                        return;
                    }

                    reject(new Error('Razorpay Checkout did not initialize. Please refresh and try again.'));
                };
                script.onerror = () => reject(new Error('Unable to load Razorpay Checkout. Check your connection and try again.'));
                document.head.appendChild(script);
            });
        }

        async function postJson(url, payload) {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                const errors = data.errors ? Object.values(data.errors).flat().join(' ') : '';
                throw new Error(errors || data.message || 'Payment request failed.');
            }

            return data;
        }

        function appointmentPayload() {
            return {
                doctor_id: doctorSelect.value,
                appointment_date: dateInput.value,
                appointment_time: timeInput.value,
                symptoms: form.querySelector('[name="symptoms"]').value,
                notes: form.querySelector('[name="notes"]').value,
            };
        }

        async function startPayment() {
            if (!keyId) {
                showPaymentMessage('Payment gateway is not configured.', 'error');
                return;
            }

            try {
                setPaymentLoading(true, 'Loading checkout...');
                await loadRazorpayCheckout();

                setPaymentLoading(true, 'Creating payment...');
                const order = await postJson(createPaymentOrderUrl, appointmentPayload());

                const checkout = new Razorpay({
                    key: keyId,
                    amount: order.amount,
                    currency: order.currency,
                    name: @json(config('app.name')),
                    description: 'Doctor appointment booking',
                    order_id: order.order_id,
                    prefill: {
                        name: userName,
                        email: userEmail,
                        contact: userContact,
                    },
                    notes: {
                        source: 'sample-telemed-appointment',
                    },
                    theme: {
                        color: '#16a34a',
                    },
                    handler: async function (response) {
                        try {
                            setPaymentLoading(true, 'Verifying payment...');

                            const verification = await postJson(verifyPaymentUrl, {
                                razorpay_payment_id: response.razorpay_payment_id,
                                razorpay_order_id: response.razorpay_order_id,
                                razorpay_signature: response.razorpay_signature,
                            });

                            showPaymentMessage('Payment verified. Redirecting to your appointment...', 'success');
                            window.location.href = verification.redirect_url;
                        } catch (error) {
                            setPaymentLoading(false);
                            showPaymentMessage(error.message, 'error');
                        }
                    },
                    modal: {
                        ondismiss: function () {
                            setPaymentLoading(false);
                            showPaymentMessage('Payment was cancelled before booking the appointment.', 'error');
                        },
                    },
                });

                checkout.on('payment.failed', function (response) {
                    setPaymentLoading(false);
                    const error = response.error || {};
                    showPaymentMessage(error.description || 'Payment failed. Please try again.', 'error');
                    console.error('Appointment payment failed', response);
                });

                setPaymentLoading(false);
                checkout.open();
            } catch (error) {
                setPaymentLoading(false);
                showPaymentMessage(error.message, 'error');
            }
        }

        function pad(value) {
            return String(value).padStart(2, '0');
        }

        function formatCurrency(amount) {
            return 'Rs. ' + Number(amount || 0).toFixed(2);
        }

        function selectedDoctorFee() {
            const config = doctorSlots[doctorSelect.value];
            return config ? config.fee : @json((float) $defaultAppointmentFee);
        }

        function refreshFeeLabel() {
            feeLabel.textContent = formatCurrency(selectedDoctorFee());
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
                    : 'border rounded-full px-4 py-2 text-sm bg-white hover:bg-green-50 hover:border-green-500 dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700 dark:hover:bg-green-950 dark:hover:border-green-700';
                button.textContent = time;
                button.addEventListener('click', () => {
                    pendingSlot = { date: day.isoDate, time };
                    modalSelectedLabel.textContent = selectedText(day.isoDate, time);
                    confirmButton.disabled = false;

                    timeList.querySelectorAll('[data-slot-button]').forEach((slotButton) => {
                        slotButton.className = 'border rounded-full px-4 py-2 text-sm bg-white hover:bg-green-50 hover:border-green-500 dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700 dark:hover:bg-green-950 dark:hover:border-green-700';
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
                    : 'border rounded-full px-4 py-2 text-sm whitespace-nowrap bg-white hover:bg-green-50 hover:border-green-500 dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700 dark:hover:bg-green-950 dark:hover:border-green-700';
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
                refreshFeeLabel();
                resetSelectedSlot();
                return;
            }

            if (availableDays.length === 0) {
                openModalButton.disabled = true;
                slotHint.textContent = 'No available slots found for this doctor.';
                empty.textContent = 'No available slots found for this doctor.';
                empty.classList.remove('hidden');
                refreshFeeLabel();
                resetSelectedSlot();
                return;
            }

            openModalButton.disabled = false;
            slotHint.textContent = `${availableDays.length} upcoming date${availableDays.length === 1 ? '' : 's'} available.`;
            activeDate = availableDays.find((day) => day.isoDate === dateInput.value)?.isoDate || availableDays[0].isoDate;
            refreshFeeLabel();
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
            event.preventDefault();

            const selectedDay = availableDays.find((day) => day.isoDate === dateInput.value);
            const validDateFormat = /^\d{4}-\d{2}-\d{2}$/.test(dateInput.value);
            const validTimeFormat = /^\d{2}:\d{2}$/.test(timeInput.value);

            clearSlotError();

            if (!doctorSelect.value) {
                showSlotError('Please select a doctor first.');
                doctorSelect.focus();
                return;
            }

            if (!dateInput.value || !timeInput.value) {
                showSlotError('Please choose an appointment date and time slot.');
                openModal();
                return;
            }

            if (!validDateFormat || !validTimeFormat) {
                showSlotError('Please choose a valid appointment date and time slot.');
                openModal();
                return;
            }

            if (dateInput.value < todayDate()) {
                showSlotError('Appointment date cannot be in the past.');
                openModal();
                return;
            }

            if (!selectedDay || !selectedDay.times.includes(timeInput.value)) {
                showSlotError('Selected appointment slot is no longer available. Please choose another slot.');
                resetSelectedSlot();
                refreshSlotState();
                openModal();
                return;
            }

            startPayment();
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
