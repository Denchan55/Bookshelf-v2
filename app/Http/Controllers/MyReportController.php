<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Http\Request;
use App\Models\ReadingPlan;


class MyReportController extends Controller
{
    public function index()
    {
    $userId = auth()->id();

    // --- 基本統計 summary ---
    $totalReviews = Review::where('user_id', $userId)->count();

    // 読了冊数（ユーザーがレビューした書籍数）
    $booksRead = ReadingPlan::where('user_id', $userId)
    ->where('status', 'completed')
    ->distinct('book_id')
    ->count('book_id');


    // 平均評価
    $averageRating = Review::where('user_id', $userId)->avg('rating') ?: 0;

    $summary = [
        'total_reviews' => $totalReviews,
        'books_read' => $booksRead,
        'average_rating' => $averageRating,
    ];

    // --- 評価分布 rating_distribution ---
    $ratingDistribution = collect([
        Review::where('user_id', $userId)->where('rating', 1)->count(),
        Review::where('user_id', $userId)->where('rating', 2)->count(),
        Review::where('user_id', $userId)->where('rating', 3)->count(),
        Review::where('user_id', $userId)->where('rating', 4)->count(),
        Review::where('user_id', $userId)->where('rating', 5)->count(),
    ]);

    // --- 高評価書籍 TOP5（ユーザー専用） ---
    $topRatedBooks = Book::whereHas('reviews', function ($q) use ($userId) {
        $q->where('user_id', $userId);
    })
    ->withAvg(['reviews' => function ($q) use ($userId) {
        $q->where('user_id', $userId);
    }], 'rating')
      ->having('reviews_avg_rating', '>=', 4)   // ★4以上だけ
    ->orderByDesc('reviews_avg_rating')
    ->take(5)
    ->get()
    ->map(function ($book) {
        $rating = round($book->reviews_avg_rating); // 平均評価を整数化

        return [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'reviews_avg_rating' => $book->reviews_avg_rating,
            'stars' => str_repeat('★', $rating) . str_repeat('☆', 5 - $rating),
            'rating' => $rating, 
        ];
    });


    // --- ジャンル別評価傾向（ユーザー専用） ---
    $genreRatings = Genre::get()
    ->map(function ($genre) use ($userId) {

        // ログインユーザーのレビューのうち、
        // このジャンルに属する本のレビューだけを取得
        $ratings = Review::where('user_id', $userId)
            ->whereHas('book.genres', function ($q) use ($genre) {
                $q->where('genres.id', $genre->id);
            })
            ->pluck('rating');

        return [
            'id' => $genre->id,
            'name' => $genre->name,
            'count' => $ratings->count(),
            'average_rating' => $ratings->avg() ?: 0,
        ];
    })
    ->filter(fn($g) => $g['count'] > 0)
    ->sortByDesc('average_rating')
    ->values() // ← ここが重要！連番を振り直す
    ->map(function ($genre, $index) {
        $genre['rank'] = $index + 1; // ← 順位をここで生成
        return $genre;
    })
    ->take(5);

    // --- Blade に渡す stats ---
    $stats = [
        'summary' => $summary,
        'rating_distribution' => $ratingDistribution,
        'top_rated_books' => $topRatedBooks,
        'genre_ratings' => $genreRatings,
    ];

    return view('reports.index', compact('stats'));

    }
}
