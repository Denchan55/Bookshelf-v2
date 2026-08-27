<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;

class BookController extends Controller
{
    public function index()
    {
    $books = Book::with('genres')
        ->orderBy('created_at', 'desc')
        ->paginate(10);

    return view('books.index', compact('books'));
}

public function create()
{
    $genres = Genre::all();
    $bookGenreIds = [];

    return view('books.create', compact('genres', 'bookGenreIds'));
}

public function store(StoreBookRequest $request)
{
    $book = Book::create($request->validated());
    $book->genres()->sync($request->genres);

    return redirect()->route('books.show', $book)
        ->with('success', '書籍を登録しました！');
}

    public function show(Book $book)
{

    $book->load([
        'genres',
        'reviews.user',
    ]);

    return view('books.show', compact('book'));
}
public function edit(Book $book)
{
    $this->authorize('update', $book);

    $genres = Genre::all();

    $bookGenreIds = $book->genres->pluck('id')->toArray();

    return view('books.edit', compact('book', 'genres', 'bookGenreIds'));
}
public function update(UpdateBookRequest $request, Book $book)
{
    $book->update($request->validated());
    $book->genres()->sync($request->genres);

    return redirect()->route('books.show', $book)
        ->with('success', '書籍を更新しました！');
}

public function destroy(Book $book)
{
    $this->authorize('update', $book);

    $book->genres()->detach();
    $book->delete();

    return redirect()->route('books.index')
        ->with('success', '書籍を削除しました');
}

}
