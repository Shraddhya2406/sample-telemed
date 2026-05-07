<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'description',
        'composition',
        'manufacturer',
        'price',
        'stock_quantity',
        'expiry_date',
        'sku',
        'category_id',
        'image',
        'category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function medicineCategory(): BelongsTo
    {
        return $this->belongsTo(MedicineCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(MedicineImage::class)->orderByDesc('is_thumbnail')->orderBy('id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getCategoryNameAttribute(): string
    {
        return $this->medicineCategory?->name ?? ($this->category ?: 'Uncategorized');
    }

    public function getImageUrlAttribute(): string
    {
        $imagePath = $this->images->firstWhere('is_thumbnail', true)?->image_path
            ?? $this->images->first()?->image_path
            ?? $this->image;

        if (! $imagePath) {
            return asset('images/medicine-default.svg');
        }

        if (Str::startsWith($imagePath, ['http://', 'https://', '/'])) {
            return $imagePath;
        }

        if (Str::startsWith($imagePath, ['images/', 'storage/'])) {
            return asset($imagePath);
        }

        return route('media.public', ['path' => $imagePath]);
    }
}
