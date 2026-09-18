<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::query()
            ->with('genres')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre')) {
            $genreId = $request->genre;
            $query->whereHas('genres', function ($q) use ($genreId) {
                $q->where('genres.id', $genreId);
            });
        }

        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'oldest':
                    $query->orderBy('published_date', 'asc');
                    break;

                case 'rating':

                    $query->orderBy('reviews_avg_rating', 'desc');
                    break;

                case 'title':
                    $query->orderBy('title', 'asc');
                    break;

                default:
                    $query->orderBy('published_date', 'desc');
            }
        } else {
            $query->orderBy('published_date', 'desc');
        }

        $books = $query->paginate(10)->appends($request->query());

        $genres = Genre::all();

        return view('books.index', compact('books', 'genres'));
    }

    public function create()
    {
        $genres = Genre::all();
        $bookGenreIds = [];

        return view('books.create', compact('genres', 'bookGenreIds'));
    }

    public function searchIsbn(string $isbn)
    {

        $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
            'q' => 'isbn:'.$isbn,
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

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍を登録しました。');
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
        $this->authorize('update', $book);

        $book->update($request->validated());

        $book->genres()->sync($request->genres);

        return redirect()->route('books.show', $book)
            ->with('success', '書籍を更新しました。');
    }

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
