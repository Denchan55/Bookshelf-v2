<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NoProgressNotification extends Notification
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
        return [
        'title' => '進行がないようです',
        'body' => '読書計画に進行がないようです。',
        'timing' => 'no_progress',
        'plan_id' => $this->plan->id,
        'target_date' => $this->plan->target_date,
        ];
    }
}
