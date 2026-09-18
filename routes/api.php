<?php

use App\Http\Controllers\Api\AuthTokenController;
use App\Http\Controllers\Api\BookApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::post('/auth/token', [AuthTokenController::class, 'store']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/books', [BookApiController::class, 'store']);
        Route::put('/books/{book}', [BookApiController::class, 'update']);
        Route::delete('/books/{book}', [BookApiController::class, 'destroy']);

    });

    Route::get('/books', [BookApiController::class, 'index']);
    Route::get('/books/{book}', [BookApiController::class, 'show']);
});
