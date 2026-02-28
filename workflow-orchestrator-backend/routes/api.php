<?php

use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WorkflowController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RequestController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:api')->group(function () {
    // Auth
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    // Requests (Employee + Approvers)
    Route::get('/requests', [RequestController::class, 'index']);
    Route::post('/requests', [RequestController::class, 'store']);
    Route::get('/requests/{id}', [RequestController::class, 'show']);

    // Approvals
    Route::get('/approvals/pending', [ApprovalController::class, 'pendingApprovals']);
    Route::post('/requests/{requestId}/steps/{stepId}/approve', [ApprovalController::class, 'approve']);
    Route::post('/requests/{requestId}/steps/{stepId}/reject', [ApprovalController::class, 'reject']);

    // Admin routes
    Route::prefix('admin')->middleware('role:Admin')->group(function () {
        // Users
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::post('/users/{id}/restore', [UserController::class, 'restore']);
        Route::post('/users/{id}/activate', [UserController::class, 'activate']);
        Route::post('/users/{id}/deactivate', [UserController::class, 'deactivate']);

        // Roles
        Route::get('/roles', [RoleController::class, 'index']);
        Route::post('/roles', [RoleController::class, 'store']);
        Route::put('/roles/{id}', [RoleController::class, 'update']);
        Route::delete('/roles/{id}', [RoleController::class, 'destroy']);

        // Workflows
        Route::get('/workflows', [WorkflowController::class, 'index']);
        Route::post('/workflows', [WorkflowController::class, 'store']);
        Route::get('/workflows/{id}', [WorkflowController::class, 'show']);
        Route::put('/workflows/{id}', [WorkflowController::class, 'update']);
        Route::delete('/workflows/{id}', [WorkflowController::class, 'destroy']);
        Route::post('/workflows/{id}/activate', [WorkflowController::class, 'activate']);
        Route::post('/workflows/{id}/deactivate', [WorkflowController::class, 'deactivate']);
    });
});

// Broadcasting auth
Broadcast::routes(['middleware' => ['auth:api']]);
