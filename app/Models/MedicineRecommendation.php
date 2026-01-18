<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineRecommendation extends Model
{
    use HasFactory;

    protected $fillable = ['min_score', 'max_score', 'disease_name', 'medicine_name', 'advice'];
}