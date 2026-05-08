<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Message;
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

        $validated = $request->validate([
            'symptoms' => ['nullable', 'string', 'max:2000'],
            'diagnosis' => ['nullable', 'string', 'max:2000'],
            'advice' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $appointment->update($validated);

        return back()->with('success', 'Consultation notes saved.');
    }

    public function storeMessage(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeDoctorAppointment($request, $appointment);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        Message::create([
            'appointment_id' => $appointment->id,
            'sender_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        return back()->with('success', 'Message sent.');
    }

    private function authorizeDoctorAppointment(Request $request, Appointment $appointment): void
    {
        abort_unless($appointment->doctor_id === $request->user()->id, 403);
    }
}
