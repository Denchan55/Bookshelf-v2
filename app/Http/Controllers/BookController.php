<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Support\Facades\Auth;

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
    $genres = Genre::all(); // ← ジャンル一覧を取得
    $bookGenreIds = [];     // ← 新規登録なので空配列

    return view('books.create', compact('genres', 'bookGenreIds'));
}

public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'author' => 'required|string|max:255',
        'description' => 'required|string|max:2000',
        'image_url' => 'nullable|url',
        'genres' => 'required|array', // ← ジャンル必須
        'isbn' => 'required|string|size:13',
        'published_at' => 'required|date', // ← 必須
    ]);

    $book = Book::create([
        'title' => $request->title,
        'author' => $request->author,
        'description' => $request->description,
        'image_url' => $request->image_url,
        'isbn' => $request->isbn,   // ← 必須
        'published_at' => $request->published_at, // ← 必須
        'user_id' => Auth::id(),
    ]);

    // 中間テーブルにジャンルを紐付ける
    $book->genres()->sync($request->genres);

    return redirect()->route('books.show', $book)
                    ->with('success', '書籍を登録しました！');
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
public function edit(Book $book)
{
    // 自分の書籍かどうか認可チェック（必須）
    $this->authorize('update', $book);

    // ジャンル一覧
    $genres = Genre::all();

    // 書籍が持っているジャンルID（チェックボックスの初期値用）
    $bookGenreIds = $book->genres->pluck('id')->toArray();

    return view('books.edit', compact('book', 'genres', 'bookGenreIds'));
}
public function update(Request $request, Book $book)
{
    // 認可チェック
    $this->authorize('update', $book);

    // バリデーション
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'author' => 'required|string|max:255',
        'published_at' => 'required|date',
        'isbn' => 'required|string|max:13',
        'genres' => 'required|array',
    ]);

    // 書籍情報の更新
    $book->update($validated);

    // ジャンルの紐付け
    $book->genres()->sync($validated['genres']);

    // 詳細ページへ戻る
    return redirect()->route('books.show', $book)
        ->with('success', '書籍情報を更新しました');
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
