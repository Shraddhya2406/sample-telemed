<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'caller_id',
        'receiver_id',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function caller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function hasParticipant(User $user): bool
    {
        return in_array($user->id, [$this->caller_id, $this->receiver_id], true);
    }

    public function otherParticipant(User $user): ?User
    {
        if ($user->id === $this->caller_id) {
            return $this->receiver;
        }

        if ($user->id === $this->receiver_id) {
            return $this->caller;
        }

        return null;
    }
}
