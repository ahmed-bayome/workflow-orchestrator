<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'form_schema',
        'steps',
        'is_active',
        'version',
        'created_by',
    ];

    protected $casts = [
        'form_schema' => 'array',
        'steps' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function requests()
    {
        return $this->hasMany(WorkflowRequest::class);
    }

    // Validate workflow structure
    public function validateStructure(): array
    {
        $errors = [];

        // Validate form schema
        if (empty($this->form_schema['fields'])) {
            $errors[] = 'Form schema must have at least one field';
        }

        // Validate steps
        if (empty($this->steps)) {
            $errors[] = 'Workflow must have at least one step';
        }

        foreach ($this->steps as $step) {
            if (empty($step['role_id'])) {
                $errors[] = "Step {$step['name']} must have a role_id";
            }
            
            if (!in_array($step['approval_mode'] ?? null, ['any', 'all'])) {
                $errors[] = "Step {$step['name']} must have approval_mode: any or all";
            }
        }

        return $errors;
    }

    // Check if any roles are used in this workflow
    public function usesRole(int $roleId): bool
    {
        foreach ($this->steps as $step) {
            if (($step['role_id'] ?? null) == $roleId) {
                return true;
            }
        }
        return false;
    }
}
