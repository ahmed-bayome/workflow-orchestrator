<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    // Check if role can be deleted (not used in any workflow)
    public function canBeDeleted(): bool
    {
        $workflowsUsingRole = WorkflowDefinition::where('is_active', true)->get()->filter(function ($workflow) {
            return $workflow->usesRole($this->id);
        });

        return $workflowsUsingRole->isEmpty();
    }

    // Get active users with this role
    public function activeUsers()
    {
        return $this->users()->where('is_active', true)->get();
    }
}
