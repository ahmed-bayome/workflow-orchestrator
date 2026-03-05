<?php

namespace App\Events;

use App\Models\WorkflowRequest;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public WorkflowRequest $request
    ) {}

    public function broadcastOn(): array
    {
        // Broadcast on the shared 'requests' channel so all authenticated
        // users (managers, admins) who are subscribed receive it instantly.
        return [
            new PrivateChannel('requests'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'request' => $this->request->load(['requester', 'workflowDefinition']),
        ];
    }
}
