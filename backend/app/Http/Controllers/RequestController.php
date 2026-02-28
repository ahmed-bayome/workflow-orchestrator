<?php

namespace App\Http\Controllers;

use App\Jobs\CreateRequestStepsJob;
use App\Models\WorkflowRequest;
use App\Models\WorkflowDefinition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RequestController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get only requests created by the user
        $requests = WorkflowRequest::with(['requester', 'workflowDefinition', 'steps'])
            ->where('requester_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests);
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Creating request', $request->all());
        $validator = Validator::make($request->all(), [
            'workflow_definition_id' => 'required|exists:workflow_definitions,id',
            'payload' => 'required|array',
        ]);

        if ($validator->fails()) {
            \Illuminate\Support\Facades\Log::warning('Validation failed', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $workflow = WorkflowDefinition::findOrFail($request->workflow_definition_id);

            if (!$workflow->is_active) {
                return response()->json(['error' => 'Workflow is not active'], 400);
            }

            // Create request with snapshot
            \Illuminate\Support\Facades\Log::info('Authenticated user ID', ['id' => auth()->id()]);
            $workflowRequest = WorkflowRequest::create([
                'workflow_definition_id' => $workflow->id,
                'workflow_snapshot' => [
                    'name' => $workflow->name,
                    'form_schema' => $workflow->form_schema,
                    'steps' => $workflow->steps,
                ],
                'requester_id' => auth()->id(),
                'payload' => $request->payload,
                'status' => 'pending',
            ]);

            // Dispatch job to create steps
            CreateRequestStepsJob::dispatch($workflowRequest->id);

            return response()->json($workflowRequest->load('workflowDefinition'), 201);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Request creation failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $request = WorkflowRequest::with([
            'requester',
            'workflowDefinition',
            'steps.role',
            'steps.actions.user'
        ])->findOrFail($id);

        return response()->json($request);
    }
}
