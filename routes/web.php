<?php

use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

//Route::get('/test-redis', function () {
//    $value = Cache::get('redis');
//
//    if(!$value){
//        Cache::put('redis', 'Redis');
//    }
//    else{
//        return $value.' in cache';
//    }
//    return $value;
//});

Route::get('', [DashboardController::class, 'index'])->name('dashboard');
Route::middleware(['auth'])->group(function () {
//Route::middleware(['auth'])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('advertisements', AdvertisementController::class);
    Route::resource('news', NewsController::class);
    Route::resource('reviews', ReviewController::class)->only(['index', 'show','destroy']);
    Route::resource('comments', CommentController::class);
});
Auth::routes(['register' => false]);

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
