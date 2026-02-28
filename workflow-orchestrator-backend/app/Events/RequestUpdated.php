<?php

namespace App\Events;

use App\Models\WorkflowRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public WorkflowRequest $request
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('requests'),
            new PrivateChannel('request.' . $this->request->id),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'request' => $this->request->load(['requester', 'steps.actions.user']),
        ];
    }
}
