<?php

use App\Http\Controllers\Auth\RegistrationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;

// Route::apiResource('tasks', TaskController::class);

Route::post('/register', [RegistrationController::class, 'register']);
Route::post('/verify-otp', [RegistrationController::class, 'verifyOtp']);
Route::post('/login', [RegistrationController::class, 'login']);
Route::post('/forgot-password', [RegistrationController::class, 'forgotPassword']);
Route::post('/reset-password', [RegistrationController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tasks', TaskController::class);
});
