<?php

namespace App\Events;

use App\Models\User;
use App\Models\VideoCall;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoCallSignal implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public VideoCall $videoCall,
        public User $from,
        public int $toUserId,
        public string $type,
        public array $payload = []
    ) {
        $this->videoCall->loadMissing(['caller.role', 'receiver.role']);
        $this->from->loadMissing('role');
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('users.'.$this->toUserId);
    }

    public function broadcastAs(): string
    {
        return 'video-call.signal';
    }

    public function broadcastWith(): array
    {
        return [
            'video_call_id' => $this->videoCall->id,
            'type' => $this->type,
            'payload' => $this->payload,
            'from' => $this->userResource($this->from),
            'caller' => $this->userResource($this->videoCall->caller),
            'receiver' => $this->userResource($this->videoCall->receiver),
            'status' => $this->videoCall->status,
            'call_url' => route('call.show', $this->videoCall),
        ];
    }

    private function userResource(?User $user): array
    {
        return [
            'id' => $user?->id,
            'name' => $user?->name ?? 'Unknown',
            'role' => $user?->role?->name ?? 'user',
        ];
    }
}
