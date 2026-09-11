<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DeadlineTodayNotification extends Notification
{
    use Queueable;

    protected $plan;

    public function __construct($plan)
    {
        $this->plan = $plan;
    }

    public function via($notifiable)
    {
        return ['database']; // DB通知を使う
    }

    public function toDatabase($notifiable)
{
    return[
        'title' => '期限が近づいています',
        'body' => '読書計画の期限が今日です。',
        'timing' =>'on_due_date',
        'plan_id' => $this->plan->id,
        'target_date' => $this->plan->target_date,
    ];
}

}