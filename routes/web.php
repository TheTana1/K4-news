<?php

use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\Auth\TelegramResetPasswordController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ReviewController;

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::get('', [DashboardController::class, 'index'])->name('dashboard');
Route::middleware(['auth'])->group(function () {
    Route::get('trashed-users',[UserController::class, 'indexTrashed'])->name('trashed-users.index');
    Route::patch('trashed-users/{id}/restore', [UserController::class, 'restore'])
        ->name('users.restore');
    Route::delete('trashed-users/{id}/force-delete', [UserController::class, 'forceDelete'])
        ->name('users.forceDelete');

    Route::resource('users', UserController::class);
    Route::resource('advertisements', AdvertisementController::class);
    Route::resource('news', NewsController::class);
    Route::resource('reviews', ReviewController::class)->only(['index', 'show','destroy']);
    Route::resource('comments', CommentController::class);


});
Auth::routes(['register' => false, 'reset' => false]);
Route::get('password/reset', [TelegramResetPasswordController::class, 'showLinkRequestForm'])
    ->name('password.request');
Route::post('password/email', [TelegramResetPasswordController::class, 'send'])
    ->name('password.email');

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
