<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewLikeController;
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
    Route::post('/favorites/{book}/toggle', [FavoriteController::class, 'toggle'])
        ->name('favorites.toggle');
    // ランキング
    Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');

    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])
    ->name('reviews.store');
    Route::post('/reviews/{review}/like', [ReviewLikeController::class, 'toggle'])
    ->name('reviews.like');

    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])
    ->name('reviews.edit');

Route::put('/reviews/{review}', [ReviewController::class, 'update'])
    ->name('reviews.update');
Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])
    ->name('reviews.destroy');
});
