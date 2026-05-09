<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorAvailability;
use App\Models\Prescription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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

        return view('doctor.dashboard', compact('stats', 'appointments'));
    }

    public function profile(Request $request): View
    {
        $doctorId = $request->user()->id;

        $request->user()->load('doctorProfile');

        $availabilities = DoctorAvailability::where('doctor_id', $doctorId)
            ->orderByRaw("FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->orderBy('start_time')
            ->get();

        return view('doctor.profile', compact('availabilities'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'specialization' => ['required', 'string', 'max:255'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:80'],
            'license_number' => ['required', 'string', 'max:255'],
            'consultation_fee' => ['nullable', 'numeric', 'min:1', 'max:999999.99'],
            'bio' => ['nullable', 'string', 'max:3000'],
        ]);

        DB::transaction(function () use ($request, $validated) {
            $request->user()->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            $request->user()->doctorProfile()->updateOrCreate(
                ['user_id' => $request->user()->id],
                [
                'specialization' => $validated['specialization'],
                'qualification' => $validated['qualification'] ?? null,
                'experience_years' => $validated['experience_years'],
                'license_number' => $validated['license_number'],
                'consultation_fee' => $validated['consultation_fee'] ?? null,
                'bio' => $validated['bio'] ?? null,
                ]
            );
        });

        return back()->with('success', 'Profile updated.');
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
