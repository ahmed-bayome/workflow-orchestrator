<?php

namespace App\Jobs;

use App\Events\RequestUpdated;
use App\Models\WorkflowRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdvanceWorkflowJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [3, 10, 30];

    public function __construct(
        public int $requestId
    ) {}

    public function handle(): void
    {
        DB::transaction(function () {
            // Lock the request row to prevent concurrent advancement
            $request = WorkflowRequest::where('id', $this->requestId)
                ->lockForUpdate()
                ->firstOrFail();

            // Check if any step in current group was rejected
            $hasRejection = $request->steps()
                ->where('execution_group', $request->current_execution_group)
                ->where('status', 'rejected')
                ->exists();

            if ($hasRejection) {
                // Check if already rejected to avoid duplicate broadcasting
                if ($request->status === 'rejected') {
                    return;
                }

                $request->update([
                    'status' => 'rejected',
                    'completed_at' => now(),
                ]);
                
                Log::info("Request {$request->id} rejected");
                if (class_exists('App\Events\RequestUpdated')) {
                    broadcast(new RequestUpdated($request->fresh()));
                }
                return;
            }

            // Check if current execution group is fully complete
            if (!$request->canAdvance()) {
                Log::info("Request {$request->id} cannot advance yet");
                return;
            }

            // Get next execution group
            $nextGroup = $request->getNextExecutionGroup();

            if ($nextGroup === null) {
                // Workflow complete!
                if ($request->status === 'approved') {
                    return; // Already approved
                }

                $request->update([
                    'status' => 'approved',
                    'completed_at' => now(),
                ]);
                
                Log::info("Request {$request->id} fully approved!");
            } else {
                // If we already advanced (idempotency check)
                if ($request->current_execution_group === $nextGroup) {
                    return;
                }

                // Advance to next group
                $request->update([
                    'current_execution_group' => $nextGroup,
                ]);
                
                Log::info("Request {$request->id} advanced to group {$nextGroup}");
            }

            if (class_exists('App\Events\RequestUpdated')) {
                broadcast(new RequestUpdated($request->fresh()))->toOthers();
            }
        });
    }
}
