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
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::middleware('auth')->group(function () {
    Route::get('/books', [BookController::class, 'index'])->name('books.index');

    // ⭐ 書籍登録画面（Create）
    Route::get('/books/create', [BookController::class, 'create'])->name('books.create');

    // ⭐ 書籍登録処理（Store）
    Route::post('/books', [BookController::class, 'store'])->name('books.store');

    Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');
});

Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');

Route::get('/', function () {
    return view('welcome');
});
Route::middleware('auth')->group(function () {
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/genres', [GenreController::class, 'index']);
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::get('/ranking', [RankingController::class, 'index']);
});
