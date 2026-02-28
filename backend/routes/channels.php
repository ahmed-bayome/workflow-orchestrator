<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('requests', function ($user) {
    return true; // All authenticated users can listen
});

Broadcast::channel('request.{requestId}', function ($user, $requestId) {
    // User can listen if they're requester or approver
    $request = \App\Models\WorkflowRequest::find($requestId);
    
    if (!$request) {
        return false;
    }

    if ($request->requester_id === $user->id) {
        return true;
    }

    $roleIds = $user->roles->pluck('id');
    $hasApprovalRole = $request->steps()
        ->whereIn('role_id', $roleIds)
        ->whereJsonContains('required_approvers_snapshot', (int) $user->id)
        ->exists();

    return $hasApprovalRole;
});
