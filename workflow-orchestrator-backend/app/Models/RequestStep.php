<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'step_definition',
        'role_id',
        'execution_group',
        'order',
        'approval_mode',
        'status',
        'required_approvers_snapshot',
        'completed_at',
    ];

    protected $casts = [
        'step_definition' => 'array',
        'required_approvers_snapshot' => 'array',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function request()
    {
        return $this->belongsTo(WorkflowRequest::class, 'request_id');
    }

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class);
    }

    public function actions()
    {
        return $this->hasMany(StepAction::class, 'request_step_id');
    }

    // Check if user can approve this step
    public function canApprove(int $userId): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        // Must be in the current execution group of the request
        if ($this->request->current_execution_group !== $this->execution_group) {
            return false;
        }

        // Check if user is in required approvers
        return in_array($userId, $this->required_approvers_snapshot);
    }

    // Check if step is complete based on approval mode
    public function checkCompletion(): bool
    {
        if ($this->approval_mode === 'any') {
            // Any: First approval/rejection completes the step
            return $this->actions()->count() > 0;
        }

        if ($this->approval_mode === 'all') {
            // All: Need approvals from all required approvers
            $approvalCount = $this->actions()
                ->where('action', 'approve')
                ->count();
            
            return $approvalCount >= count($this->required_approvers_snapshot);
        }

        return false;
    }

    // Determine final status based on actions
    public function determineFinalStatus(): string
    {
        if ($this->approval_mode === 'any') {
            // Return status of first action
            $firstAction = $this->actions()->orderBy('created_at')->first();
            return $firstAction ? ($firstAction->action === 'approve' ? 'approved' : 'rejected') : 'pending';
        }

        if ($this->approval_mode === 'all') {
            // Check if any rejection exists
            if ($this->actions()->where('action', 'reject')->exists()) {
                return 'rejected';
            }
            
            // Check if all approved
            $approvalCount = $this->actions()->where('action', 'approve')->count();
            if ($approvalCount >= count($this->required_approvers_snapshot)) {
                return 'approved';
            }
        }

        return 'pending';
    }
}
