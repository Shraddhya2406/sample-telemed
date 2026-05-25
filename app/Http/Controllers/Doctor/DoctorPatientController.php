<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorPatientController extends Controller
{
    public function index(Request $request): View
    {
        $doctorId = $request->user()->id;
        $patientIds = Appointment::forDoctor($doctorId)->distinct()->pluck('patient_id');

        $patients = User::withCount(['patientAppointments as appointments_count' => fn ($query) => $query->where('doctor_id', $doctorId)])
            ->whereIn('id', $patientIds)
            ->orderBy('name')
            ->paginate(12);

        return view('doctor.patients.index', compact('patients'));
    }

    public function show(Request $request, User $patient): View
    {
        $doctorId = $request->user()->id;

        abort_unless(Appointment::forDoctor($doctorId)->where('patient_id', $patient->id)->exists(), 403);

        $patient->load([
            'quizAttempts.quizAnswers.healthQuestion',
            'quizAttempts.quizAnswers.healthOption',
            'healthConversations.messages',
            'patientAppointments' => fn ($query) => $query->where('doctor_id', $doctorId)->latest('appointment_date'),
            'patientPrescriptions' => fn ($query) => $query->where('doctor_id', $doctorId)->with('items.medicine')->latest(),
        ]);

        return view('doctor.patients.show', compact('patient'));
    }
}
