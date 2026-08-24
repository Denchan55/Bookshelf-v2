<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

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
        return view('books.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'isbn' => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
        ]);

        Book::create($validated);

        return redirect()->route('books.index')->with('success', '書籍を登録しました');
    }
    public function show(Book $book)
{
    // 書籍に紐づくジャンルとレビューを読み込む
    $book->load([
        'genres',
        'reviews.user', // レビュー投稿者の名前表示が必要なため
    ]);

    return view('books.show', compact('book'));
}

}
