<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class RankingController extends Controller
{
    public function index()
    {
        $ranking = Book::withCount('favorites')
            ->orderBy('favorites_count', 'desc')
            ->get();

        return view('ranking.index', compact('ranking'));
    }
}
