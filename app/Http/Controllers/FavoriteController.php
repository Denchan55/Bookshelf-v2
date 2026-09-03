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
    $books = Auth::user()
        ->favoriteBooks()
        ->with('genres')
        ->paginate(10);

    return view('favorites.index', compact('books'));
}
    public function toggle(Book $book)
    {
    $user = Auth::user();

    if ($user->favoriteBooks()->where('book_id', $book->id)->exists()) {

        $user->favoriteBooks()->detach($book->id);
    } else {
        $user->favoriteBooks()->attach($book->id);
    }

    return back();
    }
}
