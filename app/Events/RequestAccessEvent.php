<?php

namespace App\Events;

use App\Models\RequestAccess;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestAccessEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public User $user, public $requestAccessId, public bool $delete)
    {
        //
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("request-access.{$this->user->id}"),
        ];
    }

    public function broadcastWith(): array
    {
        $requestAccessData = RequestAccess::with([
            'user:id,firstName,lastName',
        ])
            ->find($this->requestAccessId);
        return [
            'requestAccess'     => $requestAccessData,
            'is_delete'         => $this->delete
        ];
    }
}
