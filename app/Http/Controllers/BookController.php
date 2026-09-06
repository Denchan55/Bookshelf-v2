<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use Illuminate\Session\Store;

class BookController extends Controller
{
    /**
     * 書籍一覧
     */
    public function index()
    {
        $books = Book::with('genres')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('books.index', compact('books'));
    }

    /**
     * 書籍登録フォーム
     */
    public function create()
    {
        $genres = Genre::all();
        $bookGenreIds = []; // 新規作成なので空

        return view('books.create', compact('genres', 'bookGenreIds'));
    }

    /**
     * 書籍登録処理
     */
public function store(StoreBookRequest $request)
{
    $book = Book::create([
        'title' => $request->title,
        'author' => $request->author,
        'isbn' => $request->isbn,
        'published_at' => $request->published_at,
        'description' => $request->description,
        'image_url' => $request->image_url,
        'user_id' => auth()->id(),
    ]);

    // ★複数ジャンルを保存（これが絶対に必要）
    $book->genres()->sync($request->genres);

    return redirect()
        ->route('books.show', $book)
        ->with('success', '書籍を登録しました。');
}


    /**
     * 書籍詳細
     */
    public function show(Book $book)
    {
        $book->load([
            'genres',
            'reviews.user',
        ]);

        return view('books.show', compact('book'));
    }

    /**
     * 書籍編集フォーム
     */
    public function edit(Book $book)
    {
        $this->authorize('update', $book);

        $genres = Genre::all();
        $bookGenreIds = $book->genres->pluck('id')->toArray();

        return view('books.edit', compact('book', 'genres', 'bookGenreIds'));
    }

    /**
     * 書籍更新処理
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        $this->authorize('update', $book);
        $book->update($request->validated());

        $book->genres()->sync($request->genres);

        return redirect()->route('books.show', $book)
                ->with('success', '書籍を更新しました。');
    }

    /**
     * 書籍削除
     */
    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->genres()->detach();
        $book->delete();

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を削除しました。');
    }
}
