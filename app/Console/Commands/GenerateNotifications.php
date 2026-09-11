<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReadingPlan;
use App\Models\Notification;
use App\Enums\ReadingPlanStatus;

class GenerateNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
{
    $today = now()->startOfDay();

    $plans = ReadingPlan::where('status', ReadingPlanStatus::IN_PROGRESS)->get();

    foreach ($plans as $plan) {
        $target = $plan->target_date->startOfDay();

        // 期限3日前
        if ($today->equalTo($target->copy()->subDays(3))) {
            $this->createNotification($plan, 'deadline_soon', '期限が近づいています！');
        }

        // 期限当日
        if ($today->equalTo($target)) {
            $this->createNotification($plan, 'deadline_today', '今日が期限です！');
        }

        // 期限超過
        if ($today->greaterThan($target)) {
            $this->createNotification($plan, 'overdue', '期限を過ぎています！');
        }
    }

    // 再エンゲージメント（7日間触っていない）
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
