<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Medicine;
use App\Models\Prescription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DoctorPrescriptionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $prescriptions = Prescription::with(['patient', 'appointment', 'items.medicine'])
            ->where('doctor_id', $request->user()->id)
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters) {
                $search = trim($filters['search']);

                $query->where(function ($query) use ($search) {
                    $query
                        ->where('diagnosis', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('patient', function ($query) use ($search) {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('items.medicine', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(filled($filters['date_from'] ?? null), fn ($query) => $query->whereDate('created_at', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn ($query) => $query->whereDate('created_at', '<=', $filters['date_to']))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('doctor.prescriptions.index', compact('prescriptions', 'filters'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $doctorId = $request->user()->id;
        $selectedAppointment = null;

        if ($request->filled('appointment_id')) {
            $selectedAppointment = Appointment::forDoctor($doctorId)
                ->whereKey($request->integer('appointment_id'))
                ->firstOrFail();

            if (blank($selectedAppointment->diagnosis) || blank($selectedAppointment->advice)) {
                return redirect()
                    ->route('doctor.appointments.show', $selectedAppointment)
                    ->withErrors(['appointment' => 'Diagnosis and advice are required before creating a prescription.']);
            }
        }

        $appointments = Appointment::with('patient')
            ->forDoctor($doctorId)
            ->where('status', 'approved')
            ->whereNotNull('diagnosis')
            ->where('diagnosis', '<>', '')
            ->whereNotNull('advice')
            ->where('advice', '<>', '')
            ->latest('appointment_date')
            ->get();

        $medicines = Medicine::where('is_active', true)->orderBy('name')->get();

        return view('doctor.prescriptions.create', compact('appointments', 'medicines', 'selectedAppointment'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'items' => collect($request->input('items', []))
                ->filter(fn ($item) => filled($item['medicine_id'] ?? null) || filled($item['dosage'] ?? null) || filled($item['duration'] ?? null) || filled($item['instructions'] ?? null))
                ->values()
                ->all(),
        ]);

        $validated = $request->validate([
            'appointment_id' => ['required', 'exists:appointments,id'],
            'diagnosis' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:3000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.medicine_id' => ['required', 'exists:medicines,id'],
            'items.*.dosage' => ['required', 'string', 'max:255'],
            'items.*.duration' => ['required', 'string', 'max:255'],
            'items.*.instructions' => ['nullable', 'string', 'max:1000'],
        ]);

        $appointment = Appointment::findOrFail($validated['appointment_id']);
        abort_unless($appointment->doctor_id === $request->user()->id, 403);

        if ($appointment->status !== 'approved') {
            return back()
                ->withInput()
                ->withErrors(['appointment_id' => 'Prescriptions can only be created for accepted appointments.']);
        }

        if (blank($appointment->diagnosis) || blank($appointment->advice)) {
            return back()
                ->withInput()
                ->withErrors(['appointment_id' => 'Diagnosis and advice are required before creating a prescription.']);
        }

        $prescription = DB::transaction(function () use ($appointment, $request, $validated) {
            $prescription = Prescription::updateOrCreate(
                ['appointment_id' => $appointment->id],
                [
                    'doctor_id' => $request->user()->id,
                    'patient_id' => $appointment->patient_id,
                    'notes' => $validated['notes'] ?? null,
                    'diagnosis' => $validated['diagnosis'] ?? null,
                    'medicines' => collect($validated['items'])->pluck('medicine_id')->values()->all(),
                ]
            );

            $prescription->items()->delete();
            $prescription->items()->createMany($validated['items']);

            $appointment->update([
                'status' => 'completed',
                'diagnosis' => $validated['diagnosis'] ?? $appointment->diagnosis,
                'notes' => $validated['notes'] ?? $appointment->notes,
            ]);

            return $prescription;
        });

        return redirect()
            ->route('doctor.prescriptions.show', $prescription)
            ->with('success', 'Prescription created.');
    }

    public function show(Request $request, Prescription $prescription): View
    {
        abort_unless($prescription->doctor_id === $request->user()->id, 403);

        $prescription->load(['patient', 'doctor', 'appointment', 'items.medicine']);

        return view('doctor.prescriptions.show', compact('prescription'));
    }
}
