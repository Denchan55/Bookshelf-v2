<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewLikeController;
use App\Http\Controllers\ReadingPlanController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MyReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/


Route::get('/', function () {
    return redirect('/books');
});

Route::get('/books', [BookController::class, 'index'])->name('books.index');
Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');

Route::middleware('auth')->group(function () {

    Route::get('/books/create', [BookController::class, 'create'])
        ->name('books.create');
    Route::post('/books', [BookController::class, 'store'])
        ->name('books.store');
        // ISBN検索（応用で新規追加）
Route::post('/books/isbn-search', [BookController::class, 'isbnSearch'])->name('books.isbnSearch');

    Route::get('/genres', [GenreController::class, 'index'])->name('genres.index');
    Route::resource('genres', GenreController::class);


    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{book}/toggle', [FavoriteController::class, 'toggle'])
        ->name('favorites.toggle');
    
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

    Route::get('/books/{book}/edit', [BookController::class, 'edit'])
    ->name('books.edit');
    Route::put('/books/{book}', [BookController::class, 'update'])
    ->name('books.update');

    Route::delete('/books/{book}', [BookController::class, 'destroy'])
    ->name('books.destroy');

Route::get('/reading-plans', [ReadingPlanController::class, 'index'])->name('reading-plans.index');
Route::get('/reading-plans/create', [ReadingPlanController::class, 'create'])->name('reading-plans.create');
Route::post('/reading-plans', [ReadingPlanController::class, 'store'])->name('reading-plans.store');
Route::get('/reading-plans/{id}/edit', [ReadingPlanController::class, 'edit'])->name('reading-plans.edit');
Route::put('/reading-plans/{id}', [ReadingPlanController::class, 'update'])->name('reading-plans.update');

Route::get('/reports', [MyReportController::class, 'index'])
    ->name('reports.index');

Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

});
Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');