<?php

namespace App\Events;

use App\Models\RequestStep;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StepUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public RequestStep $step
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('request.' . $this->step->request_id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'step' => $this->step->load(['actions.user']),
        ];
    }
}
