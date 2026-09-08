<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MyReportController extends Controller
{
    public function index()
    {
        // 応用では後で集計ロジックを追加
        return view('reports.index');
    }
}
