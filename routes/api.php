<?php

use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
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
    //Category CRUD routes
    Route::apiResource('categories', CategoryController::class);
    //Blog CRUD routes
    Route::apiResource('blogs', BlogController::class);
    // Blog list with search + filter + pagination in a single API
    Route::get('blogs', [BlogController::class, 'index']);
    // Like Toggle
    Route::post('blogs/{id}/like', [LikeController::class, 'toggleLike']);
    Route::get('blogs/{id}/likes/users', [LikeController::class, 'users']);
    // Comment CRUD
    Route::get('blogs/{id}/comments', [CommentController::class, 'index']);
    Route::post('blogs/{id}/comments', [CommentController::class, 'store']);
    Route::delete('comments/{id}', [CommentController::class, 'destroy']);
    Route::get('blogs/{id}/comments/users', [CommentController::class, 'users']);
    Route::get('blogs/{id}/comments/contents', [CommentController::class, 'contents']);
});
