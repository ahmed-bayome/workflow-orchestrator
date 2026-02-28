<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessStepActionJob;
use App\Models\RequestStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApprovalController extends Controller
{
    public function pendingApprovals()
    {
        $user = auth()->user();
        $pendingSteps = $user->pendingApprovals();

        return response()->json($pendingSteps);
    }

    public function approve(Request $request, $requestId, $stepId)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $step = RequestStep::where('id', $stepId)
            ->where('request_id', $requestId)
            ->firstOrFail();

        $user = auth()->user();

        if (!$step->canApprove($user->id)) {
            return response()->json(['error' => 'You cannot approve this step'], 403);
        }

        // Dispatch job to process approval
        ProcessStepActionJob::dispatch(
            $step->id,
            $user->id,
            'approve',
            $request->input('comment')
        );

        return response()->json(['message' => 'Approval submitted successfully']);
    }

    public function reject(Request $request, $requestId, $stepId)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $step = RequestStep::where('id', $stepId)
            ->where('request_id', $requestId)
            ->firstOrFail();

        $user = auth()->user();

        if (!$step->canApprove($user->id)) {
            return response()->json(['error' => 'You cannot reject this step'], 403);
        }

        // Dispatch job to process rejection
        ProcessStepActionJob::dispatch(
            $step->id,
            $user->id,
            'reject',
            $request->input('comment')
        );

        return response()->json(['message' => 'Rejection submitted successfully']);
    }
}
