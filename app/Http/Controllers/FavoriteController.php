<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
    $favorites = Auth::user()->favoriteBooks()->get();
    return view('favorites.index', compact('favorites'));
}

    public function toggle(Book $book)
    {
    $user = Auth::user();

    if ($user->favoriteBooks()->where('book_id', $book->id)->exists()) {
        // すでにお気に入り → 削除
        $user->favoriteBooks()->detach($book->id);
    } else {
        // お気に入り追加
        $user->favoriteBooks()->attach($book->id);
    }

    return back();
    }
}
