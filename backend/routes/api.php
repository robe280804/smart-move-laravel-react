<?php

use App\Enums\TokenAbility;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Middleware\SetBearerTokenFromCookie;
use Illuminate\Http\Request;
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
            });


        // REFRESH TOKEN Route
        Route::post('refresh-token', [AuthController::class, 'refreshToken'])
            ->middleware([SetBearerTokenFromCookie::class, 'auth:sanctum', 'ability:' . TokenAbility::ISSUE_ACCESS_TOKEN->value])
            ->name('refresh');

        // PROTECTED Routes
        Route::middleware(['auth:sanctum', 'ability:' . TokenAbility::ACCESS_API->value])->group(function () {
            Route::apiResource('users', UserController::class);
        });
    });
