<?php

namespace App\Jobs;

use App\Events\StepUpdated;
use App\Models\RequestStep;
use App\Models\StepAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessStepActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [3, 10, 30];

    public function __construct(
        public int $stepId,
        public int $userId,
        public string $action,
        public ?string $comment = null
    ) {}

    public function handle(): void
    {
        DB::transaction(function () {
            // Lock the step row
            $step = RequestStep::where('id', $this->stepId)
                ->lockForUpdate()
                ->firstOrFail();

            // Idempotency check
            if ($step->status !== 'pending') {
                Log::info("Step {$this->stepId} already processed, skipping");
                return;
            }

            // Check if user already acted on this step (for 'all' mode)
            $existingAction = $step->actions()
                ->where('user_id', $this->userId)
                ->first();

            if ($existingAction) {
                Log::info("User {$this->userId} already acted on step {$this->stepId}");
                return;
            }

            // Record the action
            StepAction::create([
                'request_step_id' => $step->id,
                'user_id' => $this->userId,
                'action' => $this->action,
                'comment' => $this->comment,
            ]);

            // Check if step is complete
            $step->refresh();
            
            if ($step->checkCompletion()) {
                $finalStatus = $step->determineFinalStatus();
                
                $step->update([
                    'status' => $finalStatus,
                    'completed_at' => now(),
                ]);

                Log::info("Step {$this->stepId} completed with status: {$finalStatus}");

                // Dispatch workflow advancement
                AdvanceWorkflowJob::dispatch($step->request_id);
            }

            // Broadcast step update
            if (class_exists('App\Events\StepUpdated')) {
                broadcast(new StepUpdated($step->fresh()))->toOthers();
            }
        });
    }
}
