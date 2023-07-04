<?php

use App\Http\Middleware\UserLastActiveMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('/auth')->group(function () {
    Route::post('/register');
    Route::post('/login');
    Route::post('/forgot-password')->name('auth.forgotPassword');
    Route::post('/reset-password')->name('auth.resetPassword');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout');
    });
});

Route::middleware(['auth:sanctum', UserLastActiveMiddleware::class])->group(function () {
    Route::prefix('/me')->group(function () {
        Route::get('/');
        Route::patch('/');
        Route::delete('/');
        Route::post('/export');
    });

    Route::apiResource('games')->except(['update']);
});
