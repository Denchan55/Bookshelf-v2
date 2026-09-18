<?php

namespace App\Console\Commands;

use App\Models\ReadingPlan;
use App\Notifications\DeadlinePassedNotification;
use App\Notifications\DeadlineSoonNotification;
use App\Notifications\DeadlineTodayNotification;
use App\Notifications\ReadingPlanExpiredNotification;
use Illuminate\Console\Command;

class SendReadingPlanNotifications extends Command
{
    protected $signature = 'reading-plan:notify';

    protected $description = 'Send reading plan notifications';

    public function handle()
    {
        $today = now()->startOfDay();

        $this->sendDeadlineSoonNotifications($today);

        $this->sendDeadlineTodayNotifications($today);

        $this->sendDeadlinePassedNotifications($today);

        $this->sendExpiredNotifications($today);

        $this->info('All reading plan notifications sent.');
    }

    private function sendDeadlineSoonNotifications($today)
    {
        $plans = ReadingPlan::whereDate('target_date', $today->copy()->addDays(3))
            ->whereNull('reminder_sent_at')
            ->get();

        foreach ($plans as $plan) {
            $plan->user->notify(new DeadlineSoonNotification($plan));
            $plan->update(['reminder_sent_at' => now()]);
        }
    }

    private function sendDeadlineTodayNotifications($today)
    {
        $plans = ReadingPlan::whereDate('target_date', $today)
            ->whereNull('deadline_today_sent_at')
            ->get();

        foreach ($plans as $plan) {
            $plan->user->notify(new DeadlineTodayNotification($plan));
            $plan->update(['deadline_today_sent_at' => now()]);
        }
    }

    private function sendDeadlinePassedNotifications($today)
    {
        $plans = ReadingPlan::whereDate('target_date', $today->copy()->subDays(3))
            ->whereNull('deadline_passed_sent_at')
            ->get();

        foreach ($plans as $plan) {
            $plan->user->notify(new DeadlinePassedNotification($plan));
            $plan->update(['deadline_passed_sent_at' => now()]);
        }
    }

    private function sendExpiredNotifications($today)
    {
        $plans = ReadingPlan::whereDate('target_date', '<=', $today->copy()->subDays(7))
            ->where('status', '!=', 'completed')
            ->whereNull('expired_sent_at')

            ->get();

        foreach ($plans as $plan) {

            $plan->status = 'expired';
            $plan->save();

            $plan->user->notify(new ReadingPlanExpiredNotification($plan));

            $plan->update(['expired_sent_at' => now()]);
        }
    }
}
