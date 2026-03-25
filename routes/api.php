<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\DetailTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')
    ->as('api.v1.')
    ->group(function () {

        // Public auth routes
        Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');

        // Protected routes
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
            Route::apiResource('users', UserController::class);
            Route::apiResource('contacts', ContactController::class);
            Route::apiResource('detail-types', DetailTypeController::class);
        });
    });
