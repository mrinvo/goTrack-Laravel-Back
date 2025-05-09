<?php

use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\PermissionController;
use App\Http\Controllers\API\V1\RoleController;
use App\Http\Controllers\API\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// API routes with general rate limiting
Route::middleware('throttle:api')->group(function () {
    Route::prefix('v1')->group(function () {
        // Public routes with auth rate limiting
        Route::middleware('throttle:auth')->group(function () {
            Route::post('/login', [AuthController::class, 'login']);
            Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
            Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        });

        // Protected routes
        Route::middleware(['auth:sanctum', 'check.status'])->group(function () {
            // Auth routes
            Route::get('/user', [AuthController::class, 'user']);
            Route::post('/logout', [AuthController::class, 'logout']);

            // User management - requires admin role
            Route::middleware('role:admin')->group(function () {
                Route::apiResource('users', UserController::class);
                Route::post('/users/{id}/roles', [UserController::class, 'assignRoles']);
                Route::post('/users/{id}/permissions', [UserController::class, 'assignPermissions']);
                Route::patch('/users/{id}/status', [UserController::class, 'changeStatus']);
            });

            // Role management - requires admin role with manage-roles permission
            Route::middleware(['role:admin', 'permission:manage-roles'])->group(function () {
                Route::apiResource('roles', RoleController::class);
                Route::post('/roles/{id}/permissions', [RoleController::class, 'assignPermissions']);
            });

            // Permission management - requires super-admin role
            Route::middleware('role:super-admin')->group(function () {
                Route::apiResource('permissions', PermissionController::class);
            });
        });
    });
});