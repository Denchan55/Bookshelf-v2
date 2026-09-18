<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\ReadingPlanStoreRequest;
use App\Http\Requests\ReadingPlanUpdateRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\Request;

class ReadingPlanController extends Controller
{
    public function index(Request $request)
    {
        $currentStatus = $request->input('status');

        $readingPlans = ReadingPlan::where('user_id', auth()->id())
            ->when($currentStatus, function ($query) use ($currentStatus) {
                $query->where('status', $currentStatus);
            })
            ->get();

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
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'book_id' => $request->book_id,
            'target_date' => $request->target_date,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を更新しました！');
    }

    public function destroy(ReadingPlan $readingPlan)
    {
        $this->authorize('delete', $readingPlan);
        $readingPlan->delete();

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を削除しました。');
    }

    public function complete(ReadingPlan $readingPlan)
    {

        $readingPlan->status = ReadingPlanStatus::Completed;

        $readingPlan->completed_at = now();

        $readingPlan->save();

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を完了しました！');
    }

    public function updateStatus(Request $request, ReadingPlan $readingPlan)
    {
        $this->authorize('update', $readingPlan);

        $request->validate([
            'status' => ['required', 'string'],
        ]);

        if ($request->status === ReadingPlanStatus::Completed->value) {
            $readingPlan->completed_at = now();
        }

        $readingPlan->status = $request->status;
        $readingPlan->save();

        return redirect()->route('reading-plans.index')
            ->with('success', 'ステータスを更新しました！');
    }
}
