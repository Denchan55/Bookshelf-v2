<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendReadingPlanNotifications extends Command
{
    protected $signature = 'reading-plan:notify';
    protected $description = 'Send reading plan notifications';

    public function handle()
    {
        $today = now()->startOfDay();

        // ① 期限3日前通知
        $this->sendDeadlineSoonNotifications($today);

        // ② 期限当日通知
        $this->sendDeadlineTodayNotifications($today);

        // ③ 期限超過通知
        $this->sendDeadlinePassedNotifications($today);

        // ④ 放置通知
        $this->sendNoProgressNotifications($today);

        $this->info('All reading plan notifications sent.');
    }

    private function sendDeadlineSoonNotifications($today)
    {
        $plans = \App\Models\ReadingPlan::whereDate('target_date', $today->copy()->addDays(3))
            ->whereNull('reminder_sent_at')
            ->get();

        foreach ($plans as $plan) {
            $plan->user->notify(new \App\Notifications\DeadlineSoonNotification($plan));
            $plan->update(['reminder_sent_at' => now()]);
        }
    }

    private function sendDeadlineTodayNotifications($today)
    {
        $plans = \App\Models\ReadingPlan::whereDate('target_date', $today)
            ->whereNull('deadline_today_sent_at')
            ->get();

        foreach ($plans as $plan) {
            $plan->user->notify(new \App\Notifications\DeadlineTodayNotification($plan));
            $plan->update(['deadline_today_sent_at' => now()]);
        }
    }

    private function sendDeadlinePassedNotifications($today)
    {
        $plans = \App\Models\ReadingPlan::whereDate('target_date', '<', $today)
            ->whereNull('deadline_passed_sent_at')
            ->get();

        foreach ($plans as $plan) {
            $plan->user->notify(new \App\Notifications\DeadlinePassedNotification($plan));
            $plan->update(['deadline_passed_sent_at' => now()]);
        }
    }

    private function sendNoProgressNotifications($today)
    {
        $plans = \App\Models\ReadingPlan::where('status', 'unread')
            ->whereDate('created_at', '<=', $today->copy()->subDays(3))
            ->whereNull('no_progress_sent_at')
            ->get();

        foreach ($plans as $plan) {
            $plan->user->notify(new \App\Notifications\NoProgressNotification($plan));
            $plan->update(['no_progress_sent_at' => now()]);
        }
    }
}
