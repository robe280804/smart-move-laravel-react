<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    // PUBLIC Routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])
            ->middleware('signed')
            ->name('verification.verify');
    });


    // PROTECTED Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::post('refresh-token', [AuthController::class, 'refreshToken'])
            ->name('refresh');
    });
});
