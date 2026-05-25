<?php

namespace App\Http\Controllers\Patient;

use App\Events\AppointmentMessageSent;
use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Appointment;
use App\Models\DoctorAvailability;
use App\Models\HealthConversation;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\Error as RazorpayError;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $appointments = Appointment::with(['doctor.doctorProfile', 'prescription'])
            ->where('patient_id', $request->user()->id)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->paginate(10);

        return view('patient.appointments.index', compact('appointments'));
    }

    public function create(Request $request): View
    {
        $doctors = User::with(['doctorProfile', 'doctorAvailabilities' => fn ($query) => $query->where('is_active', true)->orderBy('day_of_week')->orderBy('start_time')])
            ->whereHas('role', fn ($query) => $query->where('name', 'doctor'))
            ->orderBy('name')
            ->get();

        $bookedSlots = Appointment::whereIn('doctor_id', $doctors->pluck('id'))
            ->whereIn('status', ['pending', 'approved'])
            ->whereDate('appointment_date', '>=', now()->toDateString())
            ->get(['doctor_id', 'appointment_date', 'appointment_time'])
            ->groupBy('doctor_id')
            ->map(fn ($appointments) => $appointments
                ->groupBy(fn (Appointment $appointment) => $appointment->appointment_date->toDateString())
                ->map(fn ($dayAppointments) => $dayAppointments
                    ->map(fn (Appointment $appointment) => substr($appointment->appointment_time, 0, 5))
                    ->values()
                )
            );

        $defaultAppointmentFee = $this->defaultAppointmentFee();
        $aiPrefill = $this->aiAppointmentPrefill($request);

        return view('patient.appointments.create', compact('doctors', 'bookedSlots', 'defaultAppointmentFee', 'aiPrefill'));
    }

    public function store(Request $request): RedirectResponse
    {
        return back()
            ->withInput()
            ->withErrors(['payment' => 'Please complete online payment to book an appointment.']);
    }

    public function createPaymentOrder(Request $request): JsonResponse
    {
        $validated = $this->validateAppointmentRequest($request);
        $doctor = $this->findBookableDoctor((int) $validated['doctor_id']);
        $date = Carbon::parse($validated['appointment_date']);
        $time = $validated['appointment_time'];

        $availabilityError = $this->validateSlot($doctor, $date, $time);

        if ($availabilityError) {
            return response()->json(['message' => $availabilityError], 422);
        }

        $appointmentFee = $this->doctorAppointmentFee($doctor);
        $amountInPaisa = (int) round($appointmentFee * 100);

        if ($amountInPaisa < 100) {
            return response()->json(['message' => 'Appointment fee is not configured correctly.'], 500);
        }

        $key = config('services.razorpay.key_id');
        $secret = config('services.razorpay.key_secret');

        if (! $key || ! $secret) {
            return response()->json(['message' => 'Payment gateway not configured.'], 500);
        }

        try {
            $this->disableProxyForRazorpay();

            $api = new Api($key, $secret);
            $razorpayOrder = $api->order->create([
                'receipt' => 'appt_'.$request->user()->id.'_'.now()->timestamp,
                'amount' => $amountInPaisa,
                'currency' => 'INR',
                'payment_capture' => 1,
            ]);

            $request->session()->put('appointment_payment_orders.'.$razorpayOrder['id'], [
                'amount' => (int) $razorpayOrder['amount'],
                'currency' => $razorpayOrder['currency'],
                'appointment' => $validated,
                'doctor_id' => $doctor->id,
            ]);

            return response()->json([
                'order_id' => $razorpayOrder['id'],
                'amount' => $razorpayOrder['amount'],
                'currency' => $razorpayOrder['currency'],
            ]);
        } catch (RazorpayError $e) {
            Log::error('Appointment Razorpay create order API error: '.$e->getMessage());

            return response()->json(['message' => 'Could not create payment order.'], 500);
        } catch (\Throwable $e) {
            Log::error('Appointment Razorpay create order error: '.$e->getMessage());

            return response()->json(['message' => 'Could not create payment order.'], 500);
        }
    }

    public function verifyPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $key = config('services.razorpay.key_id');
        $secret = config('services.razorpay.key_secret');

        if (! $key || ! $secret) {
            return response()->json(['message' => 'Payment gateway not configured.'], 500);
        }

        $orderId = $validated['razorpay_order_id'];
        $paymentId = $validated['razorpay_payment_id'];
        $signature = $validated['razorpay_signature'];
        $paymentOrder = $request->session()->get('appointment_payment_orders.'.$orderId);

        if (! $paymentOrder || empty($paymentOrder['appointment'])) {
            return response()->json(['message' => 'Payment session expired. Please try booking again.'], 422);
        }

        $generatedSignature = hash_hmac('sha256', $orderId.'|'.$paymentId, $secret);

        if (! hash_equals($generatedSignature, $signature)) {
            Log::warning('Appointment Razorpay signature mismatch', [
                'payment_id' => $paymentId,
                'razorpay_order_id' => $orderId,
            ]);

            return response()->json(['message' => 'Invalid payment signature.'], 400);
        }

        if (Appointment::where('payment_id', $paymentId)->exists()) {
            $appointment = Appointment::where('payment_id', $paymentId)->first();

            return response()->json([
                'message' => 'Payment already verified.',
                'appointment_id' => $appointment->id,
                'redirect_url' => route('patient.appointments.show', $appointment),
            ]);
        }

        $appointmentData = $paymentOrder['appointment'];
        $doctor = $this->findBookableDoctor((int) $appointmentData['doctor_id']);
        $date = Carbon::parse($appointmentData['appointment_date']);
        $time = $appointmentData['appointment_time'];
        $availabilityError = $this->validateSlot($doctor, $date, $time);

        if ($availabilityError) {
            return response()->json(['message' => $availabilityError], 422);
        }

        $appointmentFee = $this->doctorAppointmentFee($doctor);
        $amountInPaisa = (int) round($appointmentFee * 100);

        if ((int) ($paymentOrder['amount'] ?? 0) !== $amountInPaisa) {
            return response()->json(['message' => 'Payment amount mismatch.'], 400);
        }

        try {
            $appointment = DB::transaction(function () use ($appointmentData, $request, $doctor, $date, $time, $amountInPaisa, $paymentId, $orderId) {
                $slotTaken = Appointment::where('doctor_id', $doctor->id)
                    ->whereDate('appointment_date', $date->toDateString())
                    ->where('appointment_time', $time)
                    ->whereIn('status', ['pending', 'approved'])
                    ->lockForUpdate()
                    ->exists();

                if ($slotTaken) {
                    throw new \RuntimeException('That appointment slot is already booked.');
                }

                return Appointment::create([
                    'doctor_id' => $doctor->id,
                    'patient_id' => $request->user()->id,
                    'ai_conversation_id' => $appointmentData['ai_conversation_id'] ?? null,
                    'appointment_date' => $date->toDateString(),
                    'appointment_time' => $time,
                    'status' => 'pending',
                    'symptoms' => $appointmentData['symptoms'] ?? null,
                    'notes' => $appointmentData['notes'] ?? null,
                    'consultation_fee' => $amountInPaisa / 100,
                    'payment_status' => 'paid',
                    'payment_method' => 'razorpay',
                    'payment_id' => $paymentId,
                    'razorpay_order_id' => $orderId,
                    'paid_at' => now(),
                ]);
            });
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $request->session()->forget('appointment_payment_orders.'.$orderId);

        AppNotification::create([
            'user_id' => $appointment->doctor_id,
            'type' => 'appointment_booking',
            'title' => 'New appointment booking',
            'body' => $request->user()->name.' booked '.$appointment->appointment_date->format('d M Y').' at '.substr($appointment->appointment_time, 0, 5).'.',
            'url' => route('doctor.appointments.show', $appointment),
            'appointment_id' => $appointment->id,
        ]);

        return response()->json([
            'message' => 'Payment verified and appointment booked.',
            'appointment_id' => $appointment->id,
            'redirect_url' => route('patient.appointments.show', $appointment),
        ]);
    }

    public function show(Request $request, Appointment $appointment): View
    {
        $this->authorizePatientAppointment($request, $appointment);
        $this->markAppointmentNotificationsRead($request->user()->id, $appointment);

        $appointment->load(['doctor.doctorProfile', 'messages.sender', 'prescription.items.medicine']);

        return view('patient.appointments.show', compact('appointment'));
    }

    public function cancel(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizePatientAppointment($request, $appointment);

        if ($appointment->status !== 'pending') {
            return back()->withErrors(['appointment' => 'Only pending appointments can be cancelled.']);
        }

        $appointment->update(['status' => 'rejected']);

        return redirect()
            ->route('patient.appointments.index')
            ->with('success', 'Appointment request cancelled.');
    }

    public function messages(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizePatientAppointment($request, $appointment);

        $messages = $appointment->messages()
            ->with('sender')
            ->when($request->integer('after_id'), fn ($query, $afterId) => $query->where('id', '>', $afterId))
            ->orderBy('id')
            ->get()
            ->map(fn (Message $message) => $this->messageResource($message, $request->user()->id));

        return response()->json(['messages' => $messages]);
    }

    public function storeMessage(Request $request, Appointment $appointment): RedirectResponse|JsonResponse
    {
        $this->authorizePatientAppointment($request, $appointment);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $message = Message::create([
            'appointment_id' => $appointment->id,
            'sender_id' => $request->user()->id,
            'message' => $validated['message'],
        ])->load('sender');

        AppNotification::create([
            'user_id' => $appointment->doctor_id,
            'type' => 'appointment_message',
            'title' => 'New message from '.$request->user()->name,
            'body' => str($validated['message'])->limit(90)->toString(),
            'url' => route('doctor.appointments.show', $appointment).'#messages',
            'appointment_id' => $appointment->id,
        ]);

        broadcast(new AppointmentMessageSent($message))->toOthers();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Message sent.',
                'chat_message' => $this->messageResource($message, $request->user()->id),
            ], 201);
        }

        return back()->with('success', 'Message sent.');
    }

    private function messageResource(Message $message, int $currentUserId): array
    {
        return [
            'id' => $message->id,
            'is_own' => $message->sender_id === $currentUserId,
            'sender_name' => $message->sender?->name ?? 'Unknown',
            'message' => $message->message,
            'created_at' => $message->created_at?->format('d M Y h:i A'),
        ];
    }

    private function authorizePatientAppointment(Request $request, Appointment $appointment): void
    {
        abort_unless($appointment->patient_id === $request->user()->id, 403);
    }

    private function markAppointmentNotificationsRead(int $userId, Appointment $appointment): void
    {
        AppNotification::where('user_id', $userId)
            ->where('appointment_id', $appointment->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    private function validateAppointmentRequest(Request $request): array
    {
        return $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:users,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'ai_conversation_id' => [
                'nullable',
                'integer',
                Rule::exists('health_conversations', 'id')->where(fn ($query) => $query->where('user_id', $request->user()->id)),
            ],
            'symptoms' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function aiAppointmentPrefill(Request $request): array
    {
        $conversationId = $request->integer('ai_conversation');

        if (! $conversationId) {
            return ['conversation_id' => null, 'symptoms' => null, 'notes' => null];
        }

        $conversation = HealthConversation::with('messages')
            ->where('user_id', $request->user()->id)
            ->find($conversationId);

        if (! $conversation) {
            return ['conversation_id' => null, 'symptoms' => null, 'notes' => null];
        }

        $medicineSuggestions = collect($conversation->medicine_suggestions ?? [])
            ->map(fn ($suggestion) => trim(($suggestion['name'] ?? 'Medicine').': '.($suggestion['reason'] ?? 'Suggested from AI assessment.')))
            ->filter()
            ->values();

        $notes = collect([
            'AI medicine suggestions from available stock:',
            $medicineSuggestions->isNotEmpty()
                ? $medicineSuggestions->map(fn ($item) => '- '.$item)->join("\n")
                : 'No AI medicine suggestions were generated.',
            '',
            'Note: AI medicine suggestions are preliminary and need doctor review.',
        ])->filter(fn ($line) => $line !== null)->join("\n");

        $symptoms = collect([
            'AI preliminary assessment summary:',
            $conversation->summary ?: 'No AI summary generated yet.',
            '',
            'AI urgency level: '.str($conversation->urgency_level ?: 'low')->headline(),
        ])->join("\n");

        return [
            'conversation_id' => $conversation->id,
            'symptoms' => str($symptoms)->limit(2000, '')->toString(),
            'notes' => str($notes)->limit(1000, '')->toString(),
        ];
    }

    private function findBookableDoctor(int $doctorId): User
    {
        return User::where('id', $doctorId)
            ->whereHas('role', fn ($query) => $query->where('name', 'doctor'))
            ->firstOrFail();
    }

    private function validateSlot(User $doctor, Carbon $date, string $time): ?string
    {
        $hasAvailability = DoctorAvailability::where('doctor_id', $doctor->id)
            ->where('is_active', true)
            ->where('day_of_week', $date->format('l'))
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->exists();

        if (! $hasAvailability) {
            return 'Please choose a time inside the selected doctor availability.';
        }

        $slotTaken = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $date->toDateString())
            ->where('appointment_time', $time)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($slotTaken) {
            return 'That appointment slot is already booked.';
        }

        return null;
    }

    private function doctorAppointmentFee(User $doctor): float
    {
        $fee = $doctor->doctorProfile?->consultation_fee;

        return filled($fee) ? (float) $fee : $this->defaultAppointmentFee();
    }

    private function defaultAppointmentFee(): float
    {
        return (float) config('services.appointments.fee', 500);
    }

    private function disableProxyForRazorpay(): void
    {
        foreach (['HTTP_PROXY', 'HTTPS_PROXY', 'ALL_PROXY', 'http_proxy', 'https_proxy', 'all_proxy'] as $name) {
            putenv($name);
            unset($_ENV[$name], $_SERVER[$name]);
        }
    }
}
