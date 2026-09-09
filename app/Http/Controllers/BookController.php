<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use Illuminate\Session\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BookController extends Controller
{
    /**
     * 書籍一覧
     */
    public function index(Request $request)
{
    $query = Book::query()
        ->with('genres')
        ->withCount('reviews'); // 評価順ソート用

    // キーワード検索
    if ($request->filled('keyword')) {
        $keyword = $request->keyword;
        $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
                ->orWhere('author', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%");
        });
    }

    // ジャンルフィルタ（複数選択）
    if ($request->filled('genre')) {
    $genreId = $request->genre;
    $query->whereHas('genres', function ($q) use ($genreId) {
        $q->where('genres.id', $genreId);
    });
}


    // ソート（新着順 / 古い順 / 評価順）
    if ($request->filled('sort')) {
        switch ($request->sort) {
            case 'oldest':
                $query->orderBy('published_date', 'asc');
                break;

            case 'rating':
                $query->orderBy('reviews_count', 'desc');
                break;

            default:
                $query->orderBy('published_date', 'desc');
        }
    } else {
        // デフォルト：新着順
        $query->orderBy('published_date', 'desc');
    }

    // ページネーション（検索条件維持）
    $books = $query->paginate(10)->appends($request->query());

    // ジャンル一覧（検索フォーム用）
    $genres = Genre::all();

    return view('books.index', compact('books', 'genres'));
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
public function searchIsbn(string $isbn)
{
    $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
        'q' => 'isbn:' . $isbn,
    ]);

    if ($response->failed() || empty($response['items'])) {
        return response()->json(['error' => '書籍が見つかりませんでした。'], 404);
    }

    $book = $response['items'][0]['volumeInfo'];

    return [
        'title' => $book['title'] ?? null,
        'author' => $book['authors'][0] ?? null,
        'published_date' => $book['publishedDate'] ?? null,
        'description' => $book['description'] ?? null,
        'image_url' => $book['imageLinks']['thumbnail'] ?? null,
    ];
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
        'published_date' => $request->published_date,
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
