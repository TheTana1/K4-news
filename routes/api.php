<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;

//Route::prefix('api')->middleware('auth:api')->group(function () {
Route::prefix('api')->middleware('auth:api')->group(function () {
    Route::resource('users', UserController::class);
});
