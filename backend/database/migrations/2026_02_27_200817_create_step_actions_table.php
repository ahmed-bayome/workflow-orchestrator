<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('step_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_step_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->enum('action', ['approve', 'reject']);
            $table->text('comment')->nullable();
            $table->timestamp('created_at');
            
            $table->index(['request_step_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('step_actions');
    }
};
