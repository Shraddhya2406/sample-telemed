<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorAvailability;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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

        return view('patient.appointments.create', compact('doctors', 'bookedSlots'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:users,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'symptoms' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $doctor = User::where('id', $validated['doctor_id'])
            ->whereHas('role', fn ($query) => $query->where('name', 'doctor'))
            ->firstOrFail();

        $date = Carbon::parse($validated['appointment_date']);
        $time = $validated['appointment_time'];

        $hasAvailability = DoctorAvailability::where('doctor_id', $doctor->id)
            ->where('is_active', true)
            ->where('day_of_week', $date->format('l'))
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->exists();

        if (! $hasAvailability) {
            return back()
                ->withInput()
                ->withErrors(['appointment_time' => 'Please choose a time inside the selected doctor availability.']);
        }

        $slotTaken = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $date->toDateString())
            ->where('appointment_time', $time)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($slotTaken) {
            return back()
                ->withInput()
                ->withErrors(['appointment_time' => 'That appointment slot is already booked.']);
        }

        $appointment = Appointment::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $request->user()->id,
            'appointment_date' => $date->toDateString(),
            'appointment_time' => $time,
            'status' => 'pending',
            'symptoms' => $validated['symptoms'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('patient.appointments.show', $appointment)
            ->with('success', 'Appointment request sent to Dr. '.$doctor->name.'.');
    }

    public function show(Request $request, Appointment $appointment): View
    {
        $this->authorizePatientAppointment($request, $appointment);

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

    public function storeMessage(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizePatientAppointment($request, $appointment);

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

    private function authorizePatientAppointment(Request $request, Appointment $appointment): void
    {
        abort_unless($appointment->patient_id === $request->user()->id, 403);
    }
}
