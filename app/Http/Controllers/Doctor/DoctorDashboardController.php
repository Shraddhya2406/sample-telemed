<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorAvailability;
use App\Models\Prescription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DoctorDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $doctorId = $request->user()->id;

        $stats = [
            'patients' => Appointment::forDoctor($doctorId)->distinct('patient_id')->count('patient_id'),
            'appointments' => Appointment::forDoctor($doctorId)->count(),
            'prescriptions' => Prescription::where('doctor_id', $doctorId)->count(),
            'pending' => Appointment::forDoctor($doctorId)->where('status', 'pending')->count(),
        ];

        $appointments = Appointment::with('patient')
            ->forDoctor($doctorId)
            ->latest('appointment_date')
            ->latest('appointment_time')
            ->take(6)
            ->get();

        $availabilities = DoctorAvailability::where('doctor_id', $doctorId)
            ->orderByRaw("FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->orderBy('start_time')
            ->get();

        return view('doctor.dashboard', compact('stats', 'appointments', 'availabilities'));
    }

    public function updateAvailability(Request $request): RedirectResponse
    {
        $request->merge([
            'slots' => collect($request->input('slots', []))
                ->filter(fn ($slot) => filled($slot['day_of_week'] ?? null) || filled($slot['start_time'] ?? null) || filled($slot['end_time'] ?? null))
                ->values()
                ->all(),
        ]);

        $validated = $request->validate([
            'slots' => ['array'],
            'slots.*.day_of_week' => ['required', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'slots.*.start_time' => ['required', 'date_format:H:i'],
            'slots.*.end_time' => ['required', 'date_format:H:i', 'after:slots.*.start_time'],
        ]);

        $overlappingSlot = collect($validated['slots'] ?? [])
            ->groupBy('day_of_week')
            ->contains(function ($daySlots) {
                $sortedSlots = $daySlots
                    ->sortBy('start_time')
                    ->values();

                for ($i = 1; $i < $sortedSlots->count(); $i++) {
                    if ($sortedSlots[$i]['start_time'] < $sortedSlots[$i - 1]['end_time']) {
                        return true;
                    }
                }

                return false;
            });

        if ($overlappingSlot) {
            return back()
                ->withInput()
                ->withErrors(['slots' => 'Availability slots on the same day cannot overlap. Please adjust repeated or overlapping time ranges.']);
        }

        $doctorId = $request->user()->id;

        DoctorAvailability::where('doctor_id', $doctorId)->delete();

        foreach ($validated['slots'] ?? [] as $slot) {
            DoctorAvailability::create([
                'doctor_id' => $doctorId,
                'day_of_week' => $slot['day_of_week'],
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'is_active' => true,
            ]);
        }

        return back()->with('success', 'Availability updated.');
    }
}
