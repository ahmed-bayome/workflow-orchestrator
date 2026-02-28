<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_definition_id',
        'workflow_snapshot',
        'requester_id',
        'payload',
        'status',
        'current_execution_group',
        'completed_at',
    ];

    protected $casts = [
        'workflow_snapshot' => 'array',
        'payload' => 'array',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function workflowDefinition()
    {
        return $this->belongsTo(WorkflowDefinition::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function steps()
    {
        return $this->hasMany(RequestStep::class, 'request_id');
    }

    // Get current pending steps
    public function currentSteps()
    {
        return $this->steps()
            ->where('execution_group', $this->current_execution_group)
            ->where('status', 'pending')
            ->get();
    }

    // Check if request can advance to next execution group
    public function canAdvance(): bool
    {
        if (!$this->current_execution_group) {
            return false;
        }

        // Check if all steps in current execution group are complete
        $pendingCount = $this->steps()
            ->where('execution_group', $this->current_execution_group)
            ->where('status', 'pending')
            ->count();

        return $pendingCount === 0;
    }

    // Get next execution group
    public function getNextExecutionGroup(): ?string
    {
        $currentOrder = null;
        
        // Find current order
        foreach ($this->workflow_snapshot['steps'] as $step) {
            if ($step['execution_group'] === $this->current_execution_group) {
                $currentOrder = $step['order'];
                break;
            }
        }

        if ($currentOrder === null) {
            return null;
        }

        // Find next order
        $nextOrder = null;
        foreach ($this->workflow_snapshot['steps'] as $step) {
            if ($step['order'] > $currentOrder && ($nextOrder === null || $step['order'] < $nextOrder)) {
                $nextOrder = $step['order'];
            }
        }

        if ($nextOrder === null) {
            return null;
        }

        // Find execution group for next order
        foreach ($this->workflow_snapshot['steps'] as $step) {
            if ($step['order'] === $nextOrder) {
                return $step['execution_group'];
            }
        }

        return null;
    }
}
