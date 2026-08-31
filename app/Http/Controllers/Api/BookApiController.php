<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Http\Resources\BookResource;
use Illuminate\Http\Request;
use App\Http\Requests\Api\StoreBookRequest;
use App\Http\Requests\Api\UpdateBookRequest;
use App\Http\Requests\Api\BookIndexRequest;

class BookApiController extends Controller
{
    // 書籍一覧（検索・絞り込み・ページネーション対応）
public function index(BookIndexRequest $request)
{
    $validated = $request->validated();

    $query = Book::with('genres'); 

    if (!empty($validated['keyword'])) {
        $query->where('title', 'like', '%' . $validated['keyword'] . '%');
    }

    $perPage = $validated['per_page'] ?? 10;

    return BookResource::collection(
        $query->orderBy('created_at', 'desc')->paginate($perPage)
    );
}


    // 書籍詳細（ジャンル・レビュー含む）
    public function show(Book $book)
    {
        return new BookResource($book);
    }

    // 書籍登録（バリデーション通過時に登録）
public function store(StoreBookRequest $request)
{ 

    $book = Book::create([
        'title' => $request->title,
        'author' => $request->author,
        'isbn' => $request->isbn,
        'published_at' => $request->published_at,
        'description' => $request->description,
        'image_url' => $request->image_url,
        'user_id' => 1,//auth()->id(), 
    ]);


    // 複数ジャンルを紐づける
    $book->genres()->sync($request->genres);
$book->load('genres');
    return new BookResource($book);
}

public function update(UpdateBookRequest $request, Book $book)
{
    // 書籍情報を更新
    $book->update([
        'title' => $request->title,
        'author' => $request->author,
        'isbn' => $request->isbn,
        'published_at' => $request->published_at,
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
    // 中間テーブルの紐付きを削除
    $book->genres()->detach();

    // 書籍本体を削除
    $book->delete();

    // 削除成功レスポンス（204 No Content）
    return response()->json(null, 204);
}


}
