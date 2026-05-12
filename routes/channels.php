<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Appointment;

Broadcast::channel('users.{userId}', function ($user, int $userId) {
    return (int) $user->id === $userId;
});

Broadcast::channel('appointments.{appointmentId}', function ($user, int $appointmentId) {
    return Appointment::whereKey($appointmentId)
        ->where(function ($query) use ($user) {
            $query->where('doctor_id', $user->id)
                ->orWhere('patient_id', $user->id);
        })
        ->exists();
});
