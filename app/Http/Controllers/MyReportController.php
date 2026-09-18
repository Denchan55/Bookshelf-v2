<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Genre;
use App\Models\ReadingPlan;
use App\Models\Review;

class MyReportController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $totalReviews = Review::where('user_id', $userId)->count();

        $booksRead = ReadingPlan::where('user_id', $userId)
            ->where('status', 'completed')
            ->distinct('book_id')
            ->count('book_id');

        $averageRating = Review::where('user_id', $userId)->avg('rating') ?: 0;

        $summary = [
            'total_reviews' => $totalReviews,
            'books_read' => $booksRead,
            'average_rating' => $averageRating,
        ];

        $ratingDistribution = collect([
            Review::where('user_id', $userId)->where('rating', 1)->count(),
            Review::where('user_id', $userId)->where('rating', 2)->count(),
            Review::where('user_id', $userId)->where('rating', 3)->count(),
            Review::where('user_id', $userId)->where('rating', 4)->count(),
            Review::where('user_id', $userId)->where('rating', 5)->count(),
        ]);

        $topRatedBooks = Book::whereHas('reviews', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->withAvg(['reviews' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }], 'rating')
            ->having('reviews_avg_rating', '>=', 4)
            ->orderByDesc('reviews_avg_rating')
            ->take(5)
            ->get()
            ->map(function ($book) {
                $rating = round($book->reviews_avg_rating);

                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'author' => $book->author,
                    'reviews_avg_rating' => $book->reviews_avg_rating,
                    'stars' => str_repeat('★', $rating).str_repeat('☆', 5 - $rating),
                    'rating' => $rating,
                ];
            });

        $genreRatings = Genre::get()
            ->map(function ($genre) use ($userId) {

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
            ->filter(fn ($g) => $g['count'] > 0)
            ->sortByDesc('average_rating')
            ->values()
            ->map(function ($genre, $index) {
                $genre['rank'] = $index + 1;

                return $genre;
            })
            ->take(5);

        $stats = [
            'summary' => $summary,
            'rating_distribution' => $ratingDistribution,
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $genreRatings,
        ];

        return view('reports.index', compact('stats'));

    }
}
