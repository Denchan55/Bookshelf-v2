<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\RankingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

//
// 公開ページ（認証不要）
//
Route::get('/', function () {
    return redirect('/books');
});

Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

//
// 認証必須ページ
//
Route::middleware('auth')->group(function () {

    // 書籍登録
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');
    Route::post('/books', [BookController::class, 'store'])->name('books.store');

    // ジャンル
    Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');

    // お気に入り一覧
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');

    // ランキング
    Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');
});
