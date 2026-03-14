<?php

use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('users', UserController::class);
Route::resource('advertisements', AdvertisementController::class);
Route::resource('news', NewsController::class);
Route::resource('reviews', ReviewController::class);

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
