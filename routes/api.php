<?php

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
        Route::apiResource('contacts', ContactController::class);
        Route::apiResource('detail-types', DetailTypeController::class);
    });
