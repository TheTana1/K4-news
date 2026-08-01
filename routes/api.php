<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::name('api')->group(function () {
    Route::resource('users', \App\Http\Controllers\API\UserController::class);
});
