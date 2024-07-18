<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessageEvent implements ShouldBroadcast
{
    use SerializesModels, Dispatchable;

    public function __construct(public User $fromUser, public User $toUser, public $message) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('users.' . $this->toUser->id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'from_user' => $this->fromUser->toArray(),
            'to_user' => $this->toUser->toArray(),
            'message' => $this->message,
        ];
    }
}
