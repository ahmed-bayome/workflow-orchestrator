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

        // 1. Purchase Request Workflow
        WorkflowDefinition::updateOrCreate(
            ['name' => 'Purchase Request'],
            [
                'description' => 'Standard workflow for equipment and service procurement over $500.',
                'form_schema' => [
                    'fields' => [
                        [
                            'id' => 'item_name',
                            'label' => 'Item Name',
                            'type' => 'text',
                            'required' => true,
                            'placeholder' => 'e.g. Dell Monitor, AWS Credits',
                        ],
                        [
                            'id' => 'amount',
                            'label' => 'Total Amount (USD)',
                            'type' => 'number',
                            'required' => true,
                            'placeholder' => '0.00',
                        ],
                        [
                            'id' => 'category',
                            'label' => 'Expense Category',
                            'type' => 'select',
                            'required' => true,
                            'options' => [
                                ['value' => 'hardware', 'label' => 'IT Hardware'],
                                ['value' => 'software', 'label' => 'Software Subscription'],
                                ['value' => 'services', 'label' => 'Professional Services'],
                            ],
                        ],
                    ],
                ],
                'steps' => [
                    [
                        'id' => 'step_1',
                        'name' => 'Managerial Approval',
                        'role_id' => $managerRole->id,
                        'execution_group' => 'group_1',
                        'approval_mode' => 'any',
                        'order' => 1,
                    ],
                    [
                        'id' => 'step_2',
                        'name' => 'Finance Budget Review',
                        'role_id' => $financeRole->id,
                        'execution_group' => 'group_2',
                        'approval_mode' => 'all',
                        'order' => 2,
                    ],
                    [
                        'id' => 'step_3',
                        'name' => 'Executive Final Sign-off',
                        'role_id' => $ceoRole->id,
                        'execution_group' => 'group_3',
                        'approval_mode' => 'any',
                        'order' => 3,
                    ],
                ],
                'is_active' => true,
                'version' => 1,
                'created_by' => $admin->id,
            ]
        );

        // 2. Leave Request Workflow
        WorkflowDefinition::updateOrCreate(
            ['name' => 'Leave Request'],
            [
                'description' => 'Formal process for requesting annual vacation, sick leave, or personal time off.',
                'form_schema' => [
                    'fields' => [
                        [
                            'id' => 'leave_type',
                            'label' => 'Type of Leave',
                            'type' => 'select',
                            'required' => true,
                            'options' => [
                                ['value' => 'vacation', 'label' => 'Annual Vacation'],
                                ['value' => 'sick', 'label' => 'Sick Leave'],
                                ['value' => 'personal', 'label' => 'Personal Business'],
                            ],
                        ],
                        [
                            'id' => 'start_date',
                            'label' => 'First Day of Absence',
                            'type' => 'date',
                            'required' => true,
                        ],
                        [
                            'id' => 'end_date',
                            'label' => 'Last Day of Absence',
                            'type' => 'date',
                            'required' => true,
                        ],
                        [
                            'id' => 'reason',
                            'label' => 'Additional Context/Reason',
                            'type' => 'textarea',
                            'required' => false,
                        ],
                    ],
                ],
                'steps' => [
                    [
                        'id' => 'step_1',
                        'name' => 'Direct Manager Approval',
                        'role_id' => $managerRole->id,
                        'execution_group' => 'group_1',
                        'approval_mode' => 'any',
                        'order' => 1,
                    ],
                    [
                        'id' => 'step_2',
                        'name' => 'HR Compliance Check',
                        'role_id' => $hrRole->id,
                        'execution_group' => 'group_2',
                        'approval_mode' => 'any',
                        'order' => 2,
                    ],
                ],
                'is_active' => true,
                'version' => 1,
                'created_by' => $admin->id,
            ]
        );

        // 3. New: Hiring Requisition Workflow
        WorkflowDefinition::updateOrCreate(
            ['name' => 'Hiring Requisition'],
            [
                'description' => 'Request to open a new headcount or backfill a vacant position.',
                'form_schema' => [
                    'fields' => [
                        [
                            'id' => 'job_title',
                            'label' => 'Job Title',
                            'type' => 'text',
                            'required' => true,
                            'placeholder' => 'e.g. Senior Backend Engineer',
                        ],
                        [
                            'id' => 'department',
                            'label' => 'Target Department',
                            'type' => 'select',
                            'required' => true,
                            'options' => [
                                ['value' => 'engineering', 'label' => 'Engineering'],
                                ['value' => 'marketing', 'label' => 'Marketing'],
                                ['value' => 'sales', 'label' => 'Sales'],
                                ['value' => 'customer_success', 'label' => 'Customer Success'],
                            ],
                        ],
                        [
                            'id' => 'salary_range',
                            'label' => 'Expected Salary Range',
                            'type' => 'text',
                            'required' => true,
                            'placeholder' => '$80k - $110k',
                        ],
                    ],
                ],
                'steps' => [
                    [
                        'id' => 'step_1',
                        'name' => 'Department Head Approval',
                        'role_id' => $managerRole->id,
                        'execution_group' => 'group_1',
                        'approval_mode' => 'any',
                        'order' => 1,
                    ],
                    [
                        'id' => 'step_2',
                        'name' => 'Finance Headcount Validation',
                        'role_id' => $financeRole->id,
                        'execution_group' => 'group_2',
                        'approval_mode' => 'any',
                        'order' => 2,
                    ],
                    [
                        'id' => 'step_3',
                        'name' => 'HR Recruitment Review',
                        'role_id' => $hrRole->id,
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
            ]
        );
    }
}
