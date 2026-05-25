@extends('layouts.patient')

@section('title', 'Book Appointment')
@section('page_title', 'Book Appointment')
@section('eyebrow', 'Doctor consultation')

@section('content')
@php
    $prefillSymptoms = old('symptoms', $aiPrefill['symptoms'] ?? null);
    $prefillNotes = old('notes', $aiPrefill['notes'] ?? null);
    $prefillAIConversationId = old('ai_conversation_id', $aiPrefill['conversation_id'] ?? null);
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

<div class="mx-auto max-w-6xl space-y-4 pb-24 lg:pb-0">
    <a href="{{ route('patient.appointments.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-blue-700 transition hover:text-blue-800 dark:text-blue-300 dark:hover:text-blue-200">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19 8 12l7-7" />
        </svg>
        Back to appointments
    </a>

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="grid gap-4 p-4 lg:grid-cols-[1fr_18rem] lg:p-5">
            <div class="flex min-w-0 gap-3">
                <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-blue-600 text-white shadow-lg shadow-blue-600/20">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 2v3M17 2v3M4 9h16M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" />
                    </svg>
                </span>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-300">New visit</span>
                        @if($prefillAIConversationId)
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">AI summary attached</span>
                        @endif
                    </div>
                    <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-950 dark:text-white">Book a doctor appointment</h2>
                    <p class="mt-1 max-w-2xl text-sm leading-5 text-slate-500 dark:text-slate-400">Choose a doctor, reserve an available time, and complete secure online payment.</p>
                </div>
            </div>

            <div class="rounded-lg bg-slate-50 p-4 dark:bg-slate-950">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Consultation fee</p>
                <div id="appointment_fee_label" class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">Rs. {{ number_format($defaultAppointmentFee, 2) }}</div>
                <p class="mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400">Final fee updates after doctor selection.</p>
            </div>
        </div>
    </section>

    <form id="appointment_form" method="POST" action="{{ route('patient.appointments.store') }}" class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_20rem]">
        @csrf
        <input type="hidden" name="appointment_date" id="appointment_date" value="{{ old('appointment_date') }}" required>
        <input type="hidden" name="appointment_time" id="appointment_time" value="{{ old('appointment_time') }}" required>
        <input type="hidden" name="ai_conversation_id" value="{{ $prefillAIConversationId }}">

        <section class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                <h3 class="font-bold text-slate-950 dark:text-white">Visit details</h3>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Keep it brief. The doctor can review more details during consultation.</p>
            </div>

            <div class="space-y-4 p-4 lg:p-5">
                <div>
                    <label for="doctor_id" class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">Doctor</label>
                    <select name="doctor_id" id="doctor_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-blue-700 dark:focus:ring-blue-950" required>
                        <option value="">Select doctor</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" @selected(old('doctor_id') == $doctor->id)>
                                Dr. {{ $doctor->name }} - {{ $doctor->doctorProfile?->specialization ?? 'General Medicine' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-200">Appointment slot</label>
                        <span class="text-xs font-semibold text-slate-400">30 min</span>
                    </div>
                    <div class="flex flex-col gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <div id="selected_slot_label" class="truncate font-bold text-slate-950 dark:text-white">No slot selected</div>
                            <div id="slot_hint" class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Select a doctor first, then choose a date and time.</div>
                        </div>
                        <button id="open_slot_modal" type="button" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-500 dark:disabled:bg-slate-800 dark:disabled:text-slate-500 sm:w-auto" disabled>
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M7 21h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z" />
                            </svg>
                            Choose Slot
                        </button>
                    </div>
                    <div id="slot_empty" class="mt-3 hidden rounded-lg border border-slate-200 bg-slate-50 p-3 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400"></div>
                    <div id="slot_error" class="mt-3 hidden rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm font-semibold text-rose-700 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200"></div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">Symptoms</label>
                        <textarea name="symptoms" rows="5" class="w-full resize-none rounded-lg border border-slate-200 bg-white px-3 py-3 text-sm leading-6 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-blue-700 dark:focus:ring-blue-950" placeholder="Symptoms, duration, severity">{{ $prefillSymptoms }}</textarea>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700 dark:text-slate-200">Notes</label>
                        <textarea name="notes" rows="5" class="w-full resize-none rounded-lg border border-slate-200 bg-white px-3 py-3 text-sm leading-6 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-400 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-blue-700 dark:focus:ring-blue-950" placeholder="Anything else the doctor should know">{{ $prefillNotes }}</textarea>
                    </div>
                </div>
            </div>
        </section>

        <aside class="space-y-4">
            <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Booking summary</h3>
                <div class="mt-4 space-y-3">
                    <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-950">
                        <p class="text-xs font-semibold text-slate-500">Payment</p>
                        <p class="mt-1 text-sm font-bold text-slate-950 dark:text-white">Online appointment payment</p>
                    </div>
                    <div class="rounded-lg bg-emerald-50 p-3 text-sm leading-5 text-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-100">
                        Your appointment request is created after payment is verified.
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <button id="pay_book_button" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60" type="submit">
                    <svg id="payment_spinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                    <span id="payment_button_text">Pay & Book Appointment</span>
                </button>
                <a href="{{ route('patient.appointments.index') }}" class="mt-2 inline-flex w-full items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</a>
            </section>
        </aside>
    </form>
</div>

<div id="slot_modal" class="fixed inset-0 z-50 hidden">
    <div id="slot_modal_backdrop" class="absolute inset-0 bg-slate-950/60"></div>
    <div class="relative flex min-h-screen items-end justify-center p-3 sm:items-center sm:p-4">
        <div class="max-h-[92svh] w-full max-w-xl overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-start justify-between gap-3 border-b border-slate-100 px-4 py-3 dark:border-slate-800 sm:px-5 sm:py-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-950 dark:text-white">Choose appointment slot</h2>
                    <p id="slot_modal_subtitle" class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Pick a date and time.</p>
                </div>
                <button id="close_slot_modal" type="button" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 transition hover:bg-slate-100 hover:text-slate-800 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-white" aria-label="Close slot picker">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>

            <div class="max-h-[calc(92svh-9.5rem)] space-y-5 overflow-y-auto p-4 sm:p-5">
                <div>
                    <div class="mb-2 text-sm font-bold text-slate-950 dark:text-white">Available dates</div>
                    <div id="slot_dates" class="flex gap-2 overflow-x-auto pb-1"></div>
                </div>

                <div>
                    <div id="selected_date_label" class="mb-2 text-sm font-bold text-slate-700 dark:text-slate-300">Time Slots</div>
                    <div id="slot_times" class="grid grid-cols-2 gap-2 min-[420px]:grid-cols-3"></div>
                    <div id="slot_time_empty" class="hidden rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400"></div>
                </div>
            </div>

            <div class="flex flex-col gap-3 border-t border-slate-100 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-950 sm:flex-row sm:items-center sm:justify-between sm:px-5 sm:py-4">
                <div id="modal_selected_label" class="min-w-0 truncate text-sm font-semibold text-slate-600 dark:text-slate-400">No slot selected</div>
                <button id="confirm_slot" type="button" class="inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-500 dark:disabled:bg-slate-800 dark:disabled:text-slate-500 sm:w-auto" disabled>
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
            window.showPatientToast?.(text, type === 'error' ? 'error' : 'success');
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
                ai_conversation_id: form.querySelector('[name="ai_conversation_id"]').value,
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
            window.showPatientToast?.(message, 'error');
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
                    ? 'rounded-lg border border-emerald-600 bg-emerald-600 px-3 py-2.5 text-sm font-bold text-white'
                    : 'rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:border-emerald-800 dark:hover:bg-emerald-950';
                button.textContent = time;
                button.addEventListener('click', () => {
                    pendingSlot = { date: day.isoDate, time };
                    modalSelectedLabel.textContent = selectedText(day.isoDate, time);
                    confirmButton.disabled = false;

                    timeList.querySelectorAll('[data-slot-button]').forEach((slotButton) => {
                        slotButton.className = 'rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-bold text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:border-emerald-800 dark:hover:bg-emerald-950';
                    });

                    button.className = 'rounded-lg border border-emerald-600 bg-emerald-600 px-3 py-2.5 text-sm font-bold text-white';
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
                    ? 'shrink-0 rounded-lg border border-blue-600 bg-blue-600 px-3 py-2 text-sm font-bold text-white'
                    : 'shrink-0 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:border-blue-800 dark:hover:bg-blue-950';
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
