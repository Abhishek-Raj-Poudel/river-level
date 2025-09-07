<?php

namespace App\Events;

use App\Models\RiverLevel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RiverLevelExceeded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $river;

    /**
     * Create a new event instance.
     */
    public function __construct(RiverLevel $river)
    {
        $this->river = $river;

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('river-levels'),
        ];
    }

    public function broadcastAs()
    {
        return 'river.level.exceeded';
    }

public function broadcastWith()
    {
        return [
            'river' => [
                'id' => $this->river->id,
                'river_name' => $this->river->river_name,
                'lat' => $this->river->lat,
                'lng' => $this->river->lng,
                'level' => $this->river->level,
                'threshold' => $this->river->threshold,
                'exceeded_by' => $this->river->level - $this->river->threshold,
            ],
            'timestamp' => now()->toISOString(),
        ];
    }

}
