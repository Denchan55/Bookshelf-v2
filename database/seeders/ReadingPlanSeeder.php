<?php

namespace Database\Seeders;

use App\Models\ReadingPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ReadingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 1,
            'status' => 'in_progress',
            'target_date' => $today->copy()->addDays(3)->toDateString(),
            'completed_at' => null,
            'reminder_sent_at' => null,
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 2,
            'status' => 'in_progress',
            'target_date' => $today->copy()->toDateString(),
            'completed_at' => null,
            'reminder_sent_at' => null,
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 3,
            'status' => 'in_progress',
            'target_date' => $today->copy()->subDays(3)->toDateString(),
            'completed_at' => null,
            'reminder_sent_at' => null,
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 4,
            'status' => 'in_progress',
            'target_date' => $today->copy()->addDays(7)->toDateString(),
            'completed_at' => null,
            'reminder_sent_at' => null,
        ]);

        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 5,
            'status' => 'completed',
            'target_date' => $today->copy()->subDays(10)->toDateString(),
            'completed_at' => $today->copy()->subDays(5),
            'reminder_sent_at' => null,
        ]);

        ReadingPlan::create([
            'user_id' => 2,
            'book_id' => 6,
            'status' => 'in_progress',
            'target_date' => $today->copy()->addDays(5)->toDateString(),
            'completed_at' => null,
            'reminder_sent_at' => null,
        ]);
    }
}
