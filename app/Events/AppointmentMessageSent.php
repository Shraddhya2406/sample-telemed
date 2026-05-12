<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentMessageSent implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Message $message)
    {
        $this->message->loadMissing(['sender', 'appointment']);
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('appointments.'.$this->message->appointment_id);
    }

    public function broadcastAs(): string
    {
        return 'appointment.message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'sender_id' => $this->message->sender_id,
                'sender_name' => $this->message->sender?->name ?? 'Unknown',
                'message' => $this->message->message,
                'created_at' => $this->message->created_at?->format('d M Y h:i A'),
            ],
        ];
    }
}
