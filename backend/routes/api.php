<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('verification.verify');

Route::middleware('auth:sanctum')
    ->apiResource('users', UserController::class);
