<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowRequest;
use App\Models\RequestStep;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParallelWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed --class=RoleSeeder');
    }

    /** @test */
    public function parallel_group_both_steps_must_complete_before_advancing()
    {
        // 1. Setup
        $managerRole = Role::where('name', 'Manager')->first();
        $financeRole = Role::where('name', 'Finance')->first();
        $legalRole = Role::where('name', 'Legal')->first();
        $employeeRole = Role::where('name', 'Employee')->first();

        $employee = User::factory()->create(['is_active' => true]);
        $employee->assignRole($employeeRole);

        $manager = User::factory()->create(['is_active' => true]);
        $manager->assignRole($managerRole);

        $finance = User::factory()->create(['is_active' => true]);
        $finance->assignRole($financeRole);

        $legal = User::factory()->create(['is_active' => true]);
        $legal->assignRole($legalRole);

        // Create workflow with 3 steps: 1 sequential, then 2 parallel
        $workflow = WorkflowDefinition::create([
            'name' => 'Parallel Workflow',
            'is_active' => true,
            'form_schema' => ['fields' => [['name' => 'amount', 'type' => 'number']]],
            'steps' => [
                [
                    'name' => 'Manager Approval',
                    'execution_group' => 'group_1',
                    'role_id' => $managerRole->id,
                    'approval_mode' => 'any',
                    'order' => 1,
                ],
                [
                    'name' => 'Finance Approval',
                    'execution_group' => 'group_2',
                    'role_id' => $financeRole->id,
                    'approval_mode' => 'any',
                    'order' => 2,
                ],
                [
                    'name' => 'Legal Approval',
                    'execution_group' => 'group_2',
                    'role_id' => $legalRole->id,
                    'approval_mode' => 'any',
                    'order' => 2,
                ],
            ],
            'created_by' => $employee->id,
        ]);

        // 2. Employee submits request
        $token = auth('api')->login($employee);
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/requests', [
                'workflow_definition_id' => $workflow->id,
                'payload' => ['amount' => 5000],
            ]);

        $response->assertStatus(201);
        $requestId = $response->json('id');
        $request = WorkflowRequest::find($requestId);

        // Get step IDs
        $step1 = $request->steps()->where('execution_group', 'group_1')->first();
        $step2 = $request->steps()->where('execution_group', 'group_2')->where('role_id', $financeRole->id)->first();
        $step3 = $request->steps()->where('execution_group', 'group_2')->where('role_id', $legalRole->id)->first();

        // 3. Manager approves step 1 -> advances to group_2
        $managerToken = auth('api')->login($manager);
        $this->withHeader('Authorization', 'Bearer ' . $managerToken)
            ->postJson("/api/requests/{$requestId}/steps/{$step1->id}/approve");

        $request->refresh();
        $this->assertEquals('group_2', $request->current_execution_group);
        $this->assertEquals('in_progress', $request->status);

        // 4. Finance approves step 2 (group_2)
        $financeToken = auth('api')->login($finance);
        $this->withHeader('Authorization', 'Bearer ' . $financeToken)
            ->postJson("/api/requests/{$requestId}/steps/{$step2->id}/approve");

        // ASSERT: request is still "in_progress" because legal step in same group is not done yet
        $request->refresh();
        $this->assertEquals('in_progress', $request->status);
        $this->assertEquals('group_2', $request->current_execution_group);
        
        $this->assertDatabaseHas('request_steps', [
            'id' => $step2->id,
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('request_steps', [
            'id' => $step3->id,
            'status' => 'pending',
        ]);

        // 5. Legal approves step 3 (group_2)
        $legalToken = auth('api')->login($legal);
        $this->withHeader('Authorization', 'Bearer ' . $legalToken)
            ->postJson("/api/requests/{$requestId}/steps/{$step3->id}/approve");

        // ASSERT: request status = "approved" (now both parallel steps in group_2 are done)
        $request->refresh();
        $this->assertEquals('approved', $request->status);
        $this->assertNotNull($request->completed_at);
    }
}
