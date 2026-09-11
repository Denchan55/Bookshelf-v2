<?php

namespace App\Http\Controllers;

use App\Models\ReadingPlan;
use Illuminate\Http\Request;
use App\Models\Book;
use App\Enums\ReadingPlanStatus;
use App\Http\Requests\ReadingPlanStoreRequest;
use App\Http\Requests\ReadingPlanUpdateRequest;



class ReadingPlanController extends Controller
{
    public function index(Request $request)
{
    // 現在のステータス（フィルタ）
    $currentStatus = $request->input('status');

    // 読書計画一覧（フィルタあり）
    $readingPlans = ReadingPlan::when($currentStatus, function ($query) use ($currentStatus) {
        $query->where('status', $currentStatus);
    })->get();

    return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
}


    public function create()
{
    $books = Book::all();

    return view('reading-plans.create', compact('books'));
}



    public function store(ReadingPlanStoreRequest $request)
{
    ReadingPlan::create([
        'user_id' => auth()->id(),
        'book_id' => $request->book_id,
        'target_date' => $request->target_date,
        'status' => ReadingPlanStatus::NOT_STARTED,
    ]);

    return redirect()
        ->route('reading-plans.index')
        ->with('success', '読書計画を作成しました！');
}

    public function edit(ReadingPlan $readingPlan)
    {
        return view('reading-plans.edit', compact('readingPlan'));

        
    }

    public function update(ReadingPlanUpdateRequest $request, ReadingPlan $readingPlan)
{
    $readingPlan->update([
        'target_date' => $request->target_date,
    ]);

    return redirect()
        ->route('reading-plans.index')
        ->with('success', '読書計画を更新しました！');
}

    public function destroy(ReadingPlan $readingPlan)
{
    $readingPlan->delete();

    return redirect()
        ->route('reading-plans.index')
        ->with('success', '読書計画を削除しました。');
}
    
public function complete(ReadingPlan $readingPlan)
{
    // 状態を完了に変更
    $readingPlan->status = \App\Enums\ReadingPlanStatus::Completed;

    // 読了日を記録
    $readingPlan->completed_at = now();

    // 保存
    $readingPlan->save();

    return redirect()
        ->route('reading-plans.index')
        ->with('success', '読書計画を完了しました！');
}

}
