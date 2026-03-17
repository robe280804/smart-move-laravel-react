<?php

use App\Enums\TokenAbility;
use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FitnessInfoController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkoutPlanController;
use App\Http\Middleware\SetBearerTokenFromCookie;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->group(function () {

        // PUBLIC Routes
        Route::prefix('auth')
            ->group(function () {
                Route::post('/register', [AuthController::class, 'register']);
                Route::post('/login', [AuthController::class, 'login']);
                Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
                    ->middleware('signed')
                    ->name('verification.verify');
                Route::post('/reset-password', [AuthController::class, 'resetPassword'])
                    ->name('password.reset');
                Route::post('/update-password', [AuthController::class, 'updatePassword'])
                    ->name('password.update');
            });

        // REFRESH TOKEN Route
        Route::post('refresh-token', [AuthController::class, 'refreshToken'])
            ->middleware([SetBearerTokenFromCookie::class, 'auth:sanctum', 'ability:' . TokenAbility::ISSUE_ACCESS_TOKEN->value])
            ->name('refresh');

        // PROTECTED Routes
        Route::middleware(['auth:sanctum', 'ability:' . TokenAbility::ACCESS_API->value])->group(function () {
            Route::post('auth/logout', [AuthController::class, 'logout']);
            Route::apiResource('users', UserController::class);
            Route::apiResource('fitness-info', FitnessInfoController::class);

            Route::get('workout-plans', [WorkoutPlanController::class, 'index']);
            Route::get('workout-plans/{workoutPlan}', [WorkoutPlanController::class, 'show']);
            Route::delete('workout-plans/{workoutPlan}', [WorkoutPlanController::class, 'destroy']);

            Route::post('agent/generate-workout', [AgentController::class, 'generateWorkout']);
        });
    });
