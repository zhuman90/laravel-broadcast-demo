<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UserUpdateEvent implements ShouldBroadcast
{
    use SerializesModels, Dispatchable;

    public function __construct(public User $user) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('public'),
        ];
    }

    public function broadcastWith(): array
    {
        return $this->user->toArray();
    }
}
