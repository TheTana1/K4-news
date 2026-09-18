<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('jwt')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/me', [AuthController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::resource('users', \App\Http\Controllers\Api\UserController::class)
        ->names('api.users');
    Route::resource('advertisements', \App\Http\Controllers\Api\AdvertisementController::class)
        ->names('api.advertisements');
    Route::resource('news', \App\Http\Controllers\Api\NewsController::class)
        ->names('api.news');
    Route::resource('reviews', \App\Http\Controllers\Api\ReviewController::class)
        ->names('api.reviews');
});

