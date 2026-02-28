<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Relationships
    public function createdWorkflows()
    {
        return $this->hasMany(WorkflowDefinition::class, 'created_by');
    }

    public function requests()
    {
        return $this->hasMany(WorkflowRequest::class, 'requester_id');
    }

    public function stepActions()
    {
        return $this->hasMany(StepAction::class);
    }

    // Helper method to get pending approvals for this user
    public function pendingApprovals()
    {
        $roleIds = $this->roles->pluck('id');
        
        return RequestStep::whereIn('role_id', $roleIds)
            ->where('status', 'pending')
            ->whereJsonContains('required_approvers_snapshot', (int) $this->id)
            ->whereHas('request', function($query) {
                $query->whereColumn('current_execution_group', 'request_steps.execution_group');
            })
            ->with(['request.workflowDefinition', 'request.requester'])
            ->get();
    }
}
