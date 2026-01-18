<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthOption extends Model
{
    use HasFactory;

    protected $fillable = ['health_question_id', 'option_text', 'score'];

    public function healthQuestion()
    {
        return $this->belongsTo(HealthQuestion::class);
    }
}