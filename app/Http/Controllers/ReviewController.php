<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ReviewStoreRequest;
use App\Http\Requests\ReviewUpdateRequest;

class ReviewController extends Controller
{
    public function store(ReviewStoreRequest $request, Book $book)
    {
        Review::create([
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'レビューを投稿しました。');
    }
public function update(ReviewUpdateRequest $request, Review $review)
{
    if ($review->user_id !== Auth::id()) {
        abort(403);
    }
    $review->update([
        'rating' => $request->rating,
        'comment' => $request->comment,
    ]);

    return redirect()->route('books.show', $review->book_id)
                    ->with('success', 'レビューを更新しました。');
}

    public function edit(Review $review)
{
    // 自分のレビュー以外は編集させない
    if ($review->user_id !== Auth::id()) {
        abort(403);
    }

    return view('reviews.edit', compact('review'));
}

public function destroy(Review $review)
{
    if ($review->user_id !== Auth::id()) {
        abort(403);
    }

    $review->delete();

    return redirect()->route('books.show', $review->book_id)
                    ->with('success', 'レビューを削除しました。');
}

    }
