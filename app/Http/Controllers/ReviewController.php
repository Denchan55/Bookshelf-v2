<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, Book $book)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:1000',
        ]);

        Review::create([
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'レビューを投稿しました！');
    }
public function update(Request $request, Review $review)
{
    if ($review->user_id !== Auth::id()) {
        abort(403);
    }

    $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'required|string|max:1000',
    ]);

    $review->update([
        'rating' => $request->rating,
        'comment' => $request->comment,
    ]);

    return redirect()->route('books.show', $review->book_id)
                    ->with('success', 'レビューを更新しました！');
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
                    ->with('success', 'レビューを削除しました！');
}

    }
