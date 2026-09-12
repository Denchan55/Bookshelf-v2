<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookApiController;
use App\Http\Controllers\Api\AuthTokenController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::prefix('v1')->group(function () {

    // ⭐ 認証不要（トークン発行）
    Route::post('/auth/token', [AuthTokenController::class, 'store']);

    // ⭐ 認証必須の書き込み系
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/books', [BookApiController::class, 'store']);
        Route::put('/books/{book}', [BookApiController::class, 'update']);
        Route::delete('/books/{book}', [BookApiController::class, 'destroy']);

    });

    // ⭐ 読み取り系（認証不要）
    Route::get('/books', [BookApiController::class, 'index']);
    Route::get('/books/{book}', [BookApiController::class, 'show']);
});


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });