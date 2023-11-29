<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LayoutNotifyEvent implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public string $id;

    public string $message;
    public string $broadcast;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($broadcast, $id, $message)
    {
        $this->id = $id;
        $this->broadcast = $broadcast;
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return PrivateChannel|array
     */
    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('LayoutNotify.'.$this->id);
    }

    public function broadcastAs(): string
    {
        return "{$this->broadcast}";
    }
}
