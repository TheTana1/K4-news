<?php

use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\Auth\TelegramResetPasswordController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ReviewController;
use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
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

Route::view('/privacy-policy', 'privacy-policy')->name('privacy.policy');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/users/'.auth()->id());
})->middleware(['auth', 'signed'])->name('verification.verify');
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Ссылка отправлена!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
