<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowRequest;
use App\Models\RequestStep;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed --class=RoleSeeder');
    }

    /** @test */
    public function first_approval_wins_for_any_mode()
    {
        // Setup
        $managerRole = Role::where('name', 'Manager')->first();
        $employee = User::factory()->create(['is_active' => true]);
        
        $manager1 = User::factory()->create(['is_active' => true]);
        $manager1->assignRole($managerRole);
        
        $manager2 = User::factory()->create(['is_active' => true]);
        $manager2->assignRole($managerRole);

        $workflow = WorkflowDefinition::create([
            'name' => 'Concurrency Workflow',
            'is_active' => true,
            'form_schema' => ['fields' => [['name' => 'reason', 'type' => 'text']]],
            'steps' => [
                [
                    'name' => 'Manager Approval',
                    'execution_group' => 'group_1',
                    'role_id' => $managerRole->id,
                    'approval_mode' => 'any',
                    'order' => 1,
                ],
            ],
            'created_by' => $employee->id,
        ]);

        $token = auth('api')->login($employee);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/requests', [
                'workflow_definition_id' => $workflow->id,
                'payload' => ['reason' => 'test'],
            ]);

        $requestId = $response->json('id');
        $step = RequestStep::where('request_id', $requestId)->first();

        // Manager 1 approves
        $m1Token = auth('api')->login($manager1);
        $this->withHeader('Authorization', 'Bearer ' . $m1Token)
            ->postJson("/api/requests/{$requestId}/steps/{$step->id}/approve");

        $this->assertDatabaseHas('request_steps', [
            'id' => $step->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseCount('step_actions', 1);

        // Manager 2 tries to approve the same step
        $m2Token = auth('api')->login($manager2);
        $response = $this->withHeader('Authorization', 'Bearer ' . $m2Token)
            ->postJson("/api/requests/{$requestId}/steps/{$step->id}/approve");

        // Should return 403 or success but do nothing? 
        // Controller calls canApprove(), which returns false if status !== 'pending'
        $response->assertStatus(403); 
        
        // Assert still only 1 StepAction record
        $this->assertDatabaseCount('step_actions', 1);
    }

    /** @test */
    public function approval_mode_all_needs_all_approvers()
    {
        // Setup
        $managerRole = Role::where('name', 'Manager')->first();
        $employee = User::factory()->create(['is_active' => true]);
        
        $manager1 = User::factory()->create(['is_active' => true]);
        $manager1->assignRole($managerRole);
        
        $manager2 = User::factory()->create(['is_active' => true]);
        $manager2->assignRole($managerRole);

        $workflow = WorkflowDefinition::create([
            'name' => 'Mode All Workflow',
            'is_active' => true,
            'form_schema' => ['fields' => [['name' => 'reason', 'type' => 'text']]],
            'steps' => [
                [
                    'name' => 'Managers Approval',
                    'execution_group' => 'group_1',
                    'role_id' => $managerRole->id,
                    'approval_mode' => 'all',
                    'order' => 1,
                ],
            ],
            'created_by' => $employee->id,
        ]);

        $token = auth('api')->login($employee);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/requests', [
                'workflow_definition_id' => $workflow->id,
                'payload' => ['reason' => 'test'],
            ]);

        $requestId = $response->json('id');
        $step = RequestStep::where('request_id', $requestId)->first();

        // Manager 1 approves
        $m1Token = auth('api')->login($manager1);
        $this->withHeader('Authorization', 'Bearer ' . $m1Token)
            ->postJson("/api/requests/{$requestId}/steps/{$step->id}/approve");

        // Assert step status = "pending" (not done yet)
        $this->assertDatabaseHas('request_steps', [
            'id' => $step->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseCount('step_actions', 1);

        // Manager 2 approves
        $m2Token = auth('api')->login($manager2);
        $this->withHeader('Authorization', 'Bearer ' . $m2Token)
            ->postJson("/api/requests/{$requestId}/steps/{$step->id}/approve");

        // Assert step status = "approved" (now all approved)
        $this->assertDatabaseHas('request_steps', [
            'id' => $step->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseCount('step_actions', 2);
    }

    /** @test */
    public function duplicate_call_from_same_user_is_ignored()
    {
        // Setup
        $managerRole = Role::where('name', 'Manager')->first();
        $employee = User::factory()->create(['is_active' => true]);
        
        $manager1 = User::factory()->create(['is_active' => true]);
        $manager1->assignRole($managerRole);
        
        $manager2 = User::factory()->create(['is_active' => true]);
        $manager2->assignRole($managerRole);

        $workflow = WorkflowDefinition::create([
            'name' => 'Duplicate Test Workflow',
            'is_active' => true,
            'form_schema' => ['fields' => [['name' => 'reason', 'type' => 'text']]],
            'steps' => [
                [
                    'name' => 'Managers Approval',
                    'execution_group' => 'group_1',
                    'role_id' => $managerRole->id,
                    'approval_mode' => 'all',
                    'order' => 1,
                ],
            ],
            'created_by' => $employee->id,
        ]);

        $token = auth('api')->login($employee);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/requests', [
                'workflow_definition_id' => $workflow->id,
                'payload' => ['reason' => 'test'],
            ]);

        $requestId = $response->json('id');
        $step = RequestStep::where('request_id', $requestId)->first();

        // Manager 1 approves
        $m1Token = auth('api')->login($manager1);
        $this->withHeader('Authorization', 'Bearer ' . $m1Token)
            ->postJson("/api/requests/{$requestId}/steps/{$step->id}/approve");

        $this->assertDatabaseCount('step_actions', 1);

        // Manager 1 approves again (same user, same step)
        // Controller calls canApprove(), but it doesn't check if user already acted!
        // Wait, ProcessStepActionJob DOES check idempotency.
        // But the controller also checks canApprove().
        // Let's re-read canApprove in RequestStep.php.
        // It only checks status === 'pending', execution_group match, and userId in snapshot.
        // It does NOT check if user already acted.
        // So the controller will dispatch the job again.
        // But the JOB should ignore it.
        
        $response = $this->withHeader('Authorization', 'Bearer ' . $m1Token)
            ->postJson("/api/requests/{$requestId}/steps/{$step->id}/approve");

        $response->assertStatus(200); // Controller returns success because it just dispatches the job
        
        // Assert still only 1 StepAction record (second call was ignored by job's idempotency check)
        $this->assertDatabaseCount('step_actions', 1);
        
        $step->refresh();
        $this->assertEquals('pending', $step->status);
    }
}
