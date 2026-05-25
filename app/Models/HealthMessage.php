<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HealthMessage extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'conversation_id',
        'sender_type',
        'message',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(HealthConversation::class, 'conversation_id');
    }
}
