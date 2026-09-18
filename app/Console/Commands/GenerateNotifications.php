<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\Notification;
use App\Models\ReadingPlan;
use Illuminate\Console\Command;

class GenerateNotifications extends Command
{
    protected $signature = 'app:generate-notifications';

    protected $description = 'Command description';

    public function handle()
    {
        $today = now()->startOfDay();

        $plans = ReadingPlan::where('status', ReadingPlanStatus::IN_PROGRESS)->get();

        foreach ($plans as $plan) {
            $target = $plan->target_date->startOfDay();

            if ($today->equalTo($target->copy()->subDays(3))) {
                $this->createNotification($plan, 'deadline_soon', '期限が近づいています！');
            }

            if ($today->equalTo($target)) {
                $this->createNotification($plan, 'deadline_today', '今日が期限です！');
            }

            if ($today->greaterThan($target)) {
                $this->createNotification($plan, 'overdue', '期限を過ぎています！');
            }
        }

        $inactivePlans = ReadingPlan::where('updated_at', '<', now()->subDays(7))->get();

        foreach ($inactivePlans as $plan) {
            $this->createNotification($plan, 'reengagement', '最近読書計画を触っていません。');
        }
    }

    private function createNotification($plan, $type, $message)
    {
        Notification::create([
            'user_id' => $plan->user_id,
            'type' => $type,
            'message' => $message,
        ]);
    }
}
