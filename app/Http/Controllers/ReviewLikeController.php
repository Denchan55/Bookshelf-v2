<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewLikeController extends Controller
{
    public function toggle(Review $review)
    {
        $user = Auth::user();

        if ($user->likedReviews()->where('review_id', $review->id)->exists()) {
            // すでにいいね → 削除
            $user->likedReviews()->detach($review->id);
        } else {
            // いいね追加
            $user->likedReviews()->attach($review->id);
        }

        return back();
    }

    public function edit(Review $review)
{
    // 自分のレビュー以外は編集させない
    if ($review->user_id !== Auth::id()) {
        abort(403);
    }

    return view('reviews.edit', compact('review'));
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

}
