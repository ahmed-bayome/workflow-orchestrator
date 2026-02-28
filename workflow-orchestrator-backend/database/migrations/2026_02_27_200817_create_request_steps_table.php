<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('workflow_requests')->onDelete('cascade');
            $table->json('step_definition'); // Snapshot from workflow
            $table->foreignId('role_id')->constrained('roles');
            $table->string('execution_group');
            $table->integer('order');
            $table->enum('approval_mode', ['any', 'all']);
            $table->enum('status', ['pending', 'approved', 'rejected', 'skipped'])->default('pending');
            $table->json('required_approvers_snapshot'); // User IDs at creation
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['request_id', 'status']);
            $table->index(['role_id', 'status']);
            $table->index(['execution_group', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_steps');
    }
};
