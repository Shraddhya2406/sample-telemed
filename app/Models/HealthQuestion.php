<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthQuestion extends Model
{
    use HasFactory;

    protected $fillable = ['question', 'order', 'is_active'];

    public function healthOptions()
    {
        return $this->hasMany(HealthOption::class);
    }
}