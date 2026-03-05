<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowRequest;
use App\Models\RequestStep;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SequentialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed roles using RoleSeeder
        $this->artisan('db:seed --class=RoleSeeder');
    }

    /** @test */
    public function sequential_happy_path()
    {
        // 1. Setup
        $managerRole = Role::where('name', 'Manager')->first();
        $financeRole = Role::where('name', 'Finance')->first();
        $employeeRole = Role::where('name', 'Employee')->first();

        $employee = User::factory()->create(['is_active' => true]);
        $employee->assignRole($employeeRole);

        $manager = User::factory()->create(['is_active' => true]);
        $manager->assignRole($managerRole);

        $finance = User::factory()->create(['is_active' => true]);
        $finance->assignRole($financeRole);

        // Create workflow definition
        $workflow = WorkflowDefinition::create([
            'name' => 'Sequential Workflow',
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
                [
                    'name' => 'Finance Approval',
                    'execution_group' => 'group_2',
                    'role_id' => $financeRole->id,
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
                'payload' => ['reason' => 'Need a new laptop'],
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('workflow_requests', [
            'workflow_definition_id' => $workflow->id,
            'requester_id' => $employee->id,
            'status' => 'in_progress', // Job runs sync, so it moves from pending to in_progress immediately
            'current_execution_group' => 'group_1',
        ]);

        $requestId = $response->json('id');
        $request = WorkflowRequest::find($requestId);

        // Verify steps were created
        $this->assertDatabaseCount('request_steps', 2);
        $step1 = $request->steps()->where('execution_group', 'group_1')->first();
        $step2 = $request->steps()->where('execution_group', 'group_2')->first();

        $this->assertEquals('pending', $step1->status);
        $this->assertEquals('pending', $step2->status);
        $this->assertContains($manager->id, $step1->required_approvers_snapshot);
        $this->assertContains($finance->id, $step2->required_approvers_snapshot);

        // 3. Manager approves step 1
        $managerToken = auth('api')->login($manager);
        $response = $this->withHeader('Authorization', 'Bearer ' . $managerToken)
            ->postJson("/api/requests/{$requestId}/steps/{$step1->id}/approve", [
                'comment' => 'Approved by manager',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('request_steps', [
            'id' => $step1->id,
            'status' => 'approved',
        ]);

        // Verify request advanced to group_2
        $request->refresh();
        $this->assertEquals('group_2', $request->current_execution_group);
        $this->assertEquals('in_progress', $request->status);

        // 4. Finance approves step 2
        $financeToken = auth('api')->login($finance);
        $response = $this->withHeader('Authorization', 'Bearer ' . $financeToken)
            ->postJson("/api/requests/{$requestId}/steps/{$step2->id}/approve", [
                'comment' => 'Approved by finance',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('request_steps', [
            'id' => $step2->id,
            'status' => 'approved',
        ]);

        // 5. Final assertion: Request status = "approved"
        $request->refresh();
        $this->assertEquals('approved', $request->status);
        $this->assertNotNull($request->completed_at);
    }
}
