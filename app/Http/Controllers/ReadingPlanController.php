<?php

namespace App\Http\Controllers;

use App\Models\ReadingPlan;
use Illuminate\Http\Request;

class ReadingPlanController extends Controller
{
    public function index()
    {
        // 応用では後で検索・フィルタを追加
        return view('reading_plans.index');
    }

    public function create()
    {
        return view('reading_plans.create');
    }

    public function store(Request $request)
    {
        // 応用では後でバリデーションと保存処理を追加
    }

    public function edit($id)
    {
        $plan = ReadingPlan::findOrFail($id);
        return view('reading_plans.edit', compact('plan'));
    }

    public function update(Request $request, $id)
    {
        // 応用では後で更新処理を追加
    }
}
