<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenreStoreRequest;
use App\Http\Requests\GenreUpdateRequest;
use App\Models\Genre;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::withCount('books')->get();

        return view('genres.index', compact('genres'));
    }

    public function create()
    {
        return view('genres.create');
    }

    public function store(GenreStoreRequest $request)
    {
        $validated = $request->validated();

        Genre::create($validated);

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを登録しました');
    }

    public function show(Genre $genre)
    {

        $books = $genre->books()->paginate(10);

        return view('genres.show', compact('genre', 'books'));
    }

    public function edit(Genre $genre)
    {
        return view('genres.edit', compact('genre'));
    }

    public function update(GenreUpdateRequest $request, Genre $genre)
    {
        $validated = $request->validated();

        $genre->update($validated);

        return redirect()->route('genres.show', $genre)
            ->with('success', 'ジャンルを更新しました');
    }

    public function destroy(Genre $genre)
    {

        if ($genre->books()->exists()) {
            return redirect()->route('genres.index')
                ->with('error', 'このジャンルには書籍が紐づいているため削除できません。');
        }

        $genre->delete();

        return redirect()->route('genres.index')
            ->with('success', 'ジャンルを削除しました。');
    }
}
