<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'ai_conversation_id',
        'appointment_date',
        'appointment_time',
        'status',
        'notes',
        'symptoms',
        'diagnosis',
        'advice',
        'consultation_fee',
        'payment_status',
        'payment_method',
        'payment_id',
        'razorpay_order_id',
        'paid_at',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'consultation_fee' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function prescription()
    {
        return $this->hasOne(Prescription::class);
    }

    public function healthConversation()
    {
        return $this->belongsTo(HealthConversation::class, 'ai_conversation_id');
    }

    public function scopeForDoctor($query, int $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }
}
