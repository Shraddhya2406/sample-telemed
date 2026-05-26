<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $request->user()->load('patientProfile');

        return view('patient.profile');
    }

    public function edit(Request $request): View
    {
        $request->user()->load('patientProfile');

        return view('patient.profile-edit');
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $user->patientProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'age' => $validated['age'],
                'gender' => $validated['gender'],
                'medical_history' => $validated['medical_history'] ?? null,
            ]
        );

        return redirect()
            ->route('patient.profile')
            ->with('success', 'Profile updated successfully.');
    }
}
