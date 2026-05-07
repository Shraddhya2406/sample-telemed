<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'image_path',
        'is_thumbnail',
    ];

    protected $casts = [
        'is_thumbnail' => 'boolean',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}
