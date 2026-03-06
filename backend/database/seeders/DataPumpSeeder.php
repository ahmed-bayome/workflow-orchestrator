<?php

namespace Database\Seeders;

use App\Jobs\CreateRequestStepsJob;
use App\Jobs\ProcessStepActionJob;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class DataPumpSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $this->command->info('Starting Realistic Data Pump...');

        // 1. Ensure Base Data
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(WorkflowSeeder::class);

        $roles = Role::all();
        $definitions = WorkflowDefinition::where('is_active', true)->get();

        // 2. Create a small set of additional users (15)
        $this->command->info('Creating 15 additional users...');
        for ($i = 0; $i < 15; $i++) {
            $user = User::firstOrCreate(
                ['email' => "staff{$i}@company.com"],
                [
                    'name' => $faker->name,
                    'password' => Hash::make('password'),
                    'is_active' => true,
                ]
            );
            $user->assignRole($roles->where('name', '!=', 'Admin')->random());
        }

        $allUsers = User::all();

        // 3. Create 50 realistic requests
        $requestCount = 50;
        $this->command->info("Generating {$requestCount} realistic workflow requests...");

        for ($i = 0; $i < $requestCount; $i++) {
            $definition = $definitions->random();
            $requester = $allUsers->random();

            $payload = $this->generateRealisticPayload($definition->name, $faker);

            $request = WorkflowRequest::create([
                'workflow_definition_id' => $definition->id,
                'workflow_snapshot' => [
                    'name' => $definition->name,
                    'form_schema' => $definition->form_schema,
                    'steps' => $definition->steps,
                ],
                'requester_id' => $requester->id,
                'payload' => $payload,
                'status' => 'pending',
            ]);

            // Sync job to create steps
            (new CreateRequestStepsJob($request->id))->handle();

            // Randomly advance
            $this->randomlyProcessRequest($request, $faker);

            if ($i % 10 === 0) {
                $this->command->info("Progress: {$i}/{$requestCount}");
            }
        }

        $this->command->info('Data Pump finished with realistic English data!');
    }

    private function generateRealisticPayload($workflowName, $faker)
    {
        if ($workflowName === 'Purchase Request') {
            $items = [
                'MacBook Pro 14"', 'Dell XPS 15', 'Ergonomic Office Chair', 
                'Logitech MX Master 3S', 'UltraWide 34" Monitor', 'Standing Desk', 
                'AWS Cloud Credits', 'Adobe Creative Cloud Subscription', 
                'Office Supplies (Bulk)', 'Conference Room Projector'
            ];
            return [
                'item_name' => $faker->randomElement($items),
                'amount' => $faker->randomFloat(2, 50, 3500),
                'category' => $faker->randomElement(['hardware', 'software', 'services']),
            ];
        }

        if ($workflowName === 'Leave Request') {
            $reasons = [
                'Family vacation to the beach', 'Attending a wedding out of town',
                'Feeling unwell, need rest', 'Personal appointment',
                'Home renovation supervision', 'Mental health day',
                'Taking care of a family member', 'Short weekend trip'
            ];
            $start = $faker->dateTimeBetween('now', '+2 months');
            $end = clone $start;
            $end->modify('+' . rand(1, 10) . ' days');

            return [
                'leave_type' => $faker->randomElement(['vacation', 'sick', 'personal']),
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'reason' => $faker->randomElement($reasons),
            ];
        }

        return [];
    }

    private function randomlyProcessRequest(WorkflowRequest $request, $faker)
    {
        $progressLevel = $faker->numberBetween(0, 2);
        if ($progressLevel === 0) return;

        $request->refresh();
        $iteration = 0;

        while ($request->status === 'in_progress' && $iteration < 5) {
            $iteration++;
            $currentSteps = $request->currentSteps();
            if ($currentSteps->isEmpty()) break;

            foreach ($currentSteps as $step) {
                $isRejection = $faker->boolean(15); // 15% rejection rate
                $action = $isRejection ? 'reject' : 'approve';
                $comment = $isRejection 
                    ? $faker->randomElement(['Budget constraints', 'Missing documentation', 'Please provide more details', 'Not approved at this time'])
                    : $faker->randomElement(['Looks good', 'Approved for processing', 'Proceed with the next steps', 'Checked and verified']);

                $potentialApprovers = User::whereIn('id', $step->required_approvers_snapshot)->get();
                if ($potentialApprovers->isEmpty()) continue;

                if ($step->approval_mode === 'any') {
                    $approver = $potentialApprovers->random();
                    (new ProcessStepActionJob($step->id, $approver->id, $action, $comment))->handle();
                } else {
                    if ($isRejection) {
                        $approver = $potentialApprovers->random();
                        (new ProcessStepActionJob($step->id, $approver->id, 'reject', $comment))->handle();
                    } else {
                        foreach ($potentialApprovers as $approver) {
                            (new ProcessStepActionJob($step->id, $approver->id, 'approve', $comment))->handle();
                        }
                    }
                }
            }
            $request->refresh();
            if ($progressLevel === 1 && $faker->boolean(50)) break;
        }
    }
}
