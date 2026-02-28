<?php

namespace App\Jobs;

use App\Events\RequestUpdated;
use App\Models\RequestStep;
use App\Models\WorkflowRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateRequestStepsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [3, 10, 30];

    public function __construct(
        public int $requestId
    ) {}

    public function handle(): void
    {
        $request = WorkflowRequest::findOrFail($this->requestId);

        DB::transaction(function () use ($request) {
            // Create step instances from workflow snapshot
            $steps = $request->workflow_snapshot['steps'];

            foreach ($steps as $stepDef) {
                // Get users in this role (snapshot)
                $role = \App\Models\Role::find($stepDef['role_id']);
                $approverIds = $role ? $role->activeUsers()->pluck('id')->toArray() : [];

                RequestStep::create([
                    'request_id' => $request->id,
                    'step_definition' => $stepDef,
                    'role_id' => $stepDef['role_id'],
                    'execution_group' => $stepDef['execution_group'],
                    'order' => $stepDef['order'],
                    'approval_mode' => $stepDef['approval_mode'],
                    'status' => 'pending',
                    'required_approvers_snapshot' => $approverIds,
                ]);
            }

            // Set current execution group to first group
            $firstStep = collect($steps)->sortBy('order')->first();
            $request->update([
                'current_execution_group' => $firstStep['execution_group'],
                'status' => 'in_progress',
            ]);
        });

        // Broadcast update
        if (class_exists('App\Events\RequestUpdated')) {
             broadcast(new RequestUpdated($request->fresh()))->toOthers();
        }

        Log::info("Created steps for request {$request->id}");
    }
}
