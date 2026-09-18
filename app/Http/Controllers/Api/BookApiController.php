<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBookRequest;
use App\Http\Requests\Api\UpdateBookRequest;
use App\Http\Requests\BookIndexRequest;
use App\Http\Resources\BookListResource;
use App\Http\Resources\BookResource;
use App\Models\Book;

class BookApiController extends Controller
{
    public function index(BookIndexRequest $request)
    {

        $keyword = $request->query('keyword');
        $genreId = $request->query('genre_id');
        $perPage = min($request->integer('per_page', 20), 100);

        $query = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if (! empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if (! empty($genreId)) {
            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genres.id', $genreId);
            });
        }

        return BookListResource::collection(
            $query->orderBy('created_at', 'desc')->paginate($perPage)
        );
    }

    public function show(Book $book)
    {
        return new BookResource($book->load('genres', 'reviews.user'));
    }

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

        $book->genres()->sync($request->genres);

        $book->load(['genres', 'reviews']);

        return (new BookResource($book))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $book->update([
            'title' => $request->title,
            'author' => $request->author,
            'isbn' => $request->isbn,
            'published_date' => $request->published_date,
            'description' => $request->description,
            'image_url' => $request->image_url,
        ]);

        $book->genres()->sync($request->genres);

        $book->load('genres');

        return new BookResource($book);
    }

    public function destroy(Book $book)
    {

        $this->authorize('delete', $book);

        $book->genres()->detach();

        $book->delete();

        return response()->noContent();

    }
}
