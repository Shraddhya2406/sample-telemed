<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorProfile extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'specialization', 'experience_years', 'license_number', 'qualification', 'bio', 'is_verified'];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'doctor_id', 'user_id');
    }

    public function availabilities()
    {
        return $this->hasMany(DoctorAvailability::class, 'doctor_id', 'user_id');
    }
}
