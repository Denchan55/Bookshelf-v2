<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\ReadingPlan;

class ReadingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        // 山田太郎（user_id = 1）
        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 1,
            'status' => 'in_progress',
            'due_date' => $today->copy()->addDays(3)->toDateString(), // 3日前リマインダー対象
            'completed_at' => null,
            'reminder_sent_at' => null,
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 2,
            'status' => 'in_progress',
            'due_date' => $today->copy()->toDateString(), // 当日リマインダー対象
            'completed_at' => null,
            'reminder_sent_at' => null,
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 3,
            'status' => 'in_progress',
            'due_date' => $today->copy()->subDays(3)->toDateString(), // Auto-expire + 再エンゲージメント対象
            'completed_at' => null,
            'reminder_sent_at' => null,
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 4,
            'status' => 'in_progress',
            'due_date' => $today->copy()->addDays(7)->toDateString(), // リマインダー対象外
            'completed_at' => null,
            'reminder_sent_at' => null,
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 5,
            'status' => 'completed',
            'due_date' => $today->copy()->subDays(10)->toDateString(),
            'completed_at' => $today->copy()->subDays(5),
            'reminder_sent_at' => null,
        ]);

        // 鈴木花子（user_id = 2）認可テスト用
        ReadingPlan::create([
            'user_id' => 2,
            'book_id' => 6,
            'status' => 'in_progress',
            'due_date' => $today->copy()->addDays(5)->toDateString(),
            'completed_at' => null,
            'reminder_sent_at' => null,
        ]);
    }
}

