<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Genre;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::all();
        return view('genres.index', compact('genres'));
    }
public function create()
{
    return view('genres.create');
}
public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
    ]);

    Genre::create($validated);

    return redirect()->route('genres.index')
        ->with('success', 'ジャンルを登録しました');
}

public function show(Genre $genre)
{
    // このジャンルに属する書籍一覧
    $books = $genre->books()->paginate(10);
    return view('genres.show', compact('genre', 'books'));
}
public function edit(Genre $genre)
{
    return view('genres.edit', compact('genre'));
}
public function update(Request $request, Genre $genre)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
    ]);

    $genre->update($validated);

    return redirect()->route('genres.show', $genre)
        ->with('success', 'ジャンルを更新しました');
}
public function destroy(Genre $genre)
{
    // 書籍との紐付けがある場合は削除禁止
    if ($genre->books()->exists()) {
        return redirect()->route('genres.index')
            ->with('error', 'このジャンルには書籍が紐づいているため削除できません。');
    }

    // 紐づきがない場合のみ削除
    $genre->delete();

    return redirect()->route('genres.index')
        ->with('success', 'ジャンルを削除しました。');
}





}
