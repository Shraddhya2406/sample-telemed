<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAnswer extends Model
{
    use HasFactory;

    protected $fillable = ['quiz_attempt_id', 'health_question_id', 'health_option_id'];

    public function quizAttempt()
    {
        return $this->belongsTo(QuizAttempt::class);
    }

    public function healthQuestion()
    {
        return $this->belongsTo(HealthQuestion::class);
    }

    public function healthOption()
    {
        return $this->belongsTo(HealthOption::class);
    }
}