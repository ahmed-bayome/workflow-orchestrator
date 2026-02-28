<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkflowDefinition;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@test.com')->first();
        if (!$admin) return;

        $managerRole = Role::where('name', 'Manager')->first();
        $financeRole = Role::where('name', 'Finance')->first();
        $legalRole = Role::where('name', 'Legal')->first();
        $ceoRole = Role::where('name', 'CEO')->first();
        $hrRole = Role::where('name', 'HR')->first();

        // Purchase Request Workflow
        WorkflowDefinition::create([
            'name' => 'Purchase Request',
            'description' => 'Workflow for purchase approvals over $500',
            'form_schema' => [
                'fields' => [
                    [
                        'id' => 'item_name',
                        'label' => 'Item Name',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Enter item name',
                    ],
                    [
                        'id' => 'amount',
                        'label' => 'Amount (USD)',
                        'type' => 'number',
                        'required' => true,
                        'placeholder' => '0.00',
                    ],
                    [
                        'id' => 'category',
                        'label' => 'Category',
                        'type' => 'select',
                        'required' => true,
                        'options' => [
                            ['value' => 'hardware', 'label' => 'Hardware'],
                            ['value' => 'software', 'label' => 'Software'],
                            ['value' => 'services', 'label' => 'Services'],
                        ],
                    ],
                ],
            ],
            'steps' => [
                [
                    'id' => 'step_1',
                    'name' => 'Manager Approval',
                    'role_id' => $managerRole->id,
                    'execution_group' => 'group_1',
                    'approval_mode' => 'any',
                    'order' => 1,
                ],
                [
                    'id' => 'step_2',
                    'name' => 'Finance Review',
                    'role_id' => $financeRole->id,
                    'execution_group' => 'group_2',
                    'approval_mode' => 'all',
                    'order' => 2,
                ],
                [
                    'id' => 'step_3',
                    'name' => 'Legal Review',
                    'role_id' => $legalRole->id,
                    'execution_group' => 'group_2',
                    'approval_mode' => 'any',
                    'order' => 2,
                ],
                [
                    'id' => 'step_4',
                    'name' => 'CEO Final Approval',
                    'role_id' => $ceoRole->id,
                    'execution_group' => 'group_3',
                    'approval_mode' => 'any',
                    'order' => 3,
                ],
            ],
            'is_active' => true,
            'version' => 1,
            'created_by' => $admin->id,
        ]);

        // Leave Request Workflow
        WorkflowDefinition::create([
            'name' => 'Leave Request',
            'description' => 'Workflow for vacation and sick leave',
            'form_schema' => [
                'fields' => [
                    [
                        'id' => 'leave_type',
                        'label' => 'Leave Type',
                        'type' => 'select',
                        'required' => true,
                        'options' => [
                            ['value' => 'vacation', 'label' => 'Vacation'],
                            ['value' => 'sick', 'label' => 'Sick Leave'],
                            ['value' => 'personal', 'label' => 'Personal Day'],
                        ],
                    ],
                    [
                        'id' => 'start_date',
                        'label' => 'Start Date',
                        'type' => 'date',
                        'required' => true,
                    ],
                    [
                        'id' => 'end_date',
                        'label' => 'End Date',
                        'type' => 'date',
                        'required' => true,
                    ],
                    [
                        'id' => 'reason',
                        'label' => 'Reason',
                        'type' => 'textarea',
                        'required' => false,
                    ],
                ],
            ],
            'steps' => [
                [
                    'id' => 'step_1',
                    'name' => 'Manager Approval',
                    'role_id' => $managerRole->id,
                    'execution_group' => 'group_1',
                    'approval_mode' => 'any',
                    'order' => 1,
                ],
                [
                    'id' => 'step_2',
                    'name' => 'HR Processing',
                    'role_id' => $hrRole->id,
                    'execution_group' => 'group_2',
                    'approval_mode' => 'any',
                    'order' => 2,
                ],
            ],
            'is_active' => true,
            'version' => 1,
            'created_by' => $admin->id,
        ]);
    }
}
