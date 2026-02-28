<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_definition_id')->constrained()->onDelete('cascade');
            $table->json('workflow_snapshot'); // Snapshot of workflow at creation
            $table->foreignId('requester_id')->constrained('users');
            $table->json('payload'); // User's form data
            $table->enum('status', ['pending', 'in_progress', 'approved', 'rejected', 'failed'])->default('pending');
            $table->string('current_execution_group')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['requester_id', 'status', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_requests');
    }
};
