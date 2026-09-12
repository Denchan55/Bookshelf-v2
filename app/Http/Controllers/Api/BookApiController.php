<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Http\Resources\BookResource;
use Illuminate\Http\Request;
use App\Http\Requests\Api\StoreBookRequest;
use App\Http\Requests\Api\UpdateBookRequest;
use App\Http\Requests\BookIndexRequest;
use App\Http\Resources\BookListResource;

class BookApiController extends Controller
{
    // 書籍一覧（検索・絞り込み・ページネーション対応）
public function index(BookIndexRequest $request)
{
    // バリデーションは FormRequest が実行済み

    $keyword = $request->query('keyword');
    $genreId = $request->query('genre_id');
    $perPage = min($request->integer('per_page', 20), 100);

    $query = Book::with('genres');

    if (!empty($keyword)) {
        $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%{$keyword}%")
            ->orWhere('author', 'like', "%{$keyword}%")
            ->orWhere('description', 'like', "%{$keyword}%");
        });
    }

    if (!empty($genreId)) {
        $query->whereHas('genres', function ($q) use ($genreId) {
            $q->where('genres.id', $genreId);
        });
    }

    return BookListResource::collection(
        $query->orderBy('created_at', 'desc')->paginate($perPage)
    );
}

    // 書籍詳細（ジャンル・レビュー含む）
    public function show(Book $book)
{
    return new BookResource($book->load('genres', 'reviews.user'));
}


    // 書籍登録（バリデーション通過時に登録）
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

    // ジャンル紐付け
    $book->genres()->sync($request->genres);


    // ⭐ Resource が必要とする関連をロード
    $book->load(['genres', 'reviews']);

    // ⭐ 201 Created で返す
    return (new BookResource($book))
        ->response()
        ->setStatusCode(201);
}

public function update(UpdateBookRequest $request, Book $book)
{
    $this->authorize('update', $book);
    // 書籍情報を更新
    $book->update([
        'title' => $request->title,
        'author' => $request->author,
        'isbn' => $request->isbn,
        'published_date' => $request->published_date,
        'description' => $request->description,
        'image_url' => $request->image_url,
    ]);

    // ジャンルを更新
    $book->genres()->sync($request->genres);


    // リレーションをロード
    $book->load('genres');

    // 更新後の書籍情報を返す
    return new BookResource($book);
}

    // 書籍削除（関連データも削除）
    public function destroy(Book $book)
{
// ⭐ 認可チェック（これが必須）
    $this->authorize('delete', $book);

    // 中間テーブルの紐付きを削除
    $book->genres()->detach();

    // 書籍本体を削除
    $book->delete();

    // 削除成功レスポンス（204 No Content）
    return response()->json(null, 204);
}


}
