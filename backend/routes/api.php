<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    // PUBLIC Routes

    Route::get('/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])
        ->middleware('signed')
        ->name('verification.verify');

    // Register
    Route::post('/users/register', [UserController::class, 'store']);

    // PROTECTED Routes
    Route::middleware('auth:sanctum')
        ->apiResource('users', UserController::class)
        ->except(['store']);
});
