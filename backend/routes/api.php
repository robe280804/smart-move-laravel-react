<?php

use App\Enums\TokenAbility;
use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlockExerciseController;
use App\Http\Controllers\Api\ChangePasswordController;
use App\Http\Controllers\Api\FitnessInfoController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WorkoutPlanController;
use App\Http\Middleware\SetBearerTokenFromCookie;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->group(function () {

        // ── Public auth ───────────────────────────────────────────────────────
        Route::prefix('auth')
            ->middleware('throttle:auth')
            ->group(function () {
                Route::post('/register', [AuthController::class, 'register']);
                Route::post('/login', [AuthController::class, 'login']);
                Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
                    ->middleware('signed')
                    ->name('verification.verify');
            });

        // ── Password reset (stricter limit — triggers email delivery) ─────────
        Route::prefix('auth')
            ->middleware('throttle:password-reset')
            ->group(function () {
                Route::post('/reset-password', [AuthController::class, 'resetPassword'])
                    ->name('password.reset');
                Route::post('/update-password', [AuthController::class, 'updatePassword'])
                    ->name('password.update');
            });

        // ── Token refresh ─────────────────────────────────────────────────────
        Route::post('refresh-token', [AuthController::class, 'refreshToken'])
            ->middleware([
                SetBearerTokenFromCookie::class,
                'auth:sanctum',
                'ability:'.TokenAbility::ISSUE_ACCESS_TOKEN->value,
                'throttle:token-refresh',
            ])
            ->name('refresh');

        // ── Protected routes ──────────────────────────────────────────────────
        Route::middleware([
            'auth:sanctum',
            'ability:'.TokenAbility::ACCESS_API->value,
        ])->group(function () {

            Route::post('auth/logout', [AuthController::class, 'logout'])
                ->middleware('throttle:api');

            // Standard CRUD — general API limit
            Route::middleware('throttle:api')->group(function () {
                Route::post('users/change-password', ChangePasswordController::class);

                Route::apiResource('users', UserController::class)->only(['show', 'update', 'destroy']);
                Route::apiResource('fitness-info', FitnessInfoController::class);

                Route::get('workout-plans', [WorkoutPlanController::class, 'index']);
                Route::get('workout-plans/{workoutPlan}', [WorkoutPlanController::class, 'show']);
                Route::delete('workout-plans/{workoutPlan}', [WorkoutPlanController::class, 'destroy']);

                Route::patch(
                    'workout-plans/{workoutPlan}/exercises/{blockExercise}',
                    [BlockExerciseController::class, 'update']
                );
            });

            // AI workout generation — tightest limit (expensive LLM call)
            Route::post('agent/generate-workout', [AgentController::class, 'generateWorkout'])
                ->middleware('throttle:ai-generation');

            // Payment endpoints — prevent checkout/billing-portal spam
            Route::middleware('throttle:payments')->group(function () {
                Route::get('payments/plan', [PaymentController::class, 'currentPlan']);
                Route::post('payments/checkout', [PaymentController::class, 'checkout']);
                Route::post('payments/billing-portal', [PaymentController::class, 'billingPortal']);
            });
        });
    });
