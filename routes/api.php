<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookApiController;
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

    // 書籍登録（バリデーション通過時に登録）
    Route::post('/books', [BookApiController::class, 'store']);

    // 書籍一覧（検索・絞り込み・ページネーション対応）
    Route::get('/books', [BookApiController::class, 'index']);

    // 書籍詳細（ジャンル・レビュー含む）
    Route::get('/books/{book}', [BookApiController::class, 'show']);



    // 書籍更新（存在しないIDはエラー）
    Route::put('/books/{book}', [BookApiController::class, 'update']);

    // 書籍削除（関連データも削除）
    Route::delete('/books/{book}', [BookApiController::class, 'destroy']);
});

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });