<?php

namespace App\Http\Controllers\Doctor;

use App\Events\AppointmentMessageSent;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorAppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $appointments = Appointment::with(['patient', 'prescription'])
            ->forDoctor($request->user()->id)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->paginate(12)
            ->withQueryString();

        return view('doctor.appointments.index', compact('appointments', 'status'));
    }

    public function show(Request $request, Appointment $appointment): View
    {
        $this->authorizeDoctorAppointment($request, $appointment);

        $appointment->load([
            'patient.quizAttempts.quizAnswers.healthQuestion',
            'patient.quizAttempts.quizAnswers.healthOption',
            'messages.sender',
            'prescription.items.medicine',
        ]);

        return view('doctor.appointments.show', compact('appointment'));
    }

    public function updateStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeDoctorAppointment($request, $appointment);

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected,completed'],
        ]);

        $appointment->update($validated);

        return back()->with('success', 'Appointment status updated.');
    }

    public function updateNotes(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeDoctorAppointment($request, $appointment);

        if (in_array($appointment->status, ['rejected', 'completed'], true)) {
            return back()->withErrors(['appointment' => 'Consultation notes cannot be edited after an appointment is rejected or completed.']);
        }

        $validated = $request->validate([
            'diagnosis' => ['required', 'string', 'max:2000'],
            'advice' => ['required', 'string', 'max:2000'],
        ]);

        $appointment->update($validated);

        if ($request->input('next') === 'prescription') {
            return redirect()
                ->route('doctor.prescriptions.create', ['appointment_id' => $appointment->id])
                ->with('success', 'Consultation notes saved. You can create the prescription now.');
        }

        return back()->with('success', 'Consultation notes saved.');
    }

    public function messages(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeDoctorAppointment($request, $appointment);

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
        $this->authorizeDoctorAppointment($request, $appointment);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $message = Message::create([
            'appointment_id' => $appointment->id,
            'sender_id' => $request->user()->id,
            'message' => $validated['message'],
        ])->load('sender');

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

    private function authorizeDoctorAppointment(Request $request, Appointment $appointment): void
    {
        abort_unless($appointment->doctor_id === $request->user()->id, 403);
    }
}
