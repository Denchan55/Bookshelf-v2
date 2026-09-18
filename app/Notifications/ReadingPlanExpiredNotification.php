<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanExpiredNotification extends Notification
{
    use Queueable;

    protected $plan;

    public function __construct($plan)
    {
        $this->plan = $plan;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => '読書計画が失効しました',
            'body' => '期限から7日が経過したため、読書計画は失効扱いになりました。',
            'timing' => 'expired',
            'plan_id' => $this->plan->id,
            'target_date' => $this->plan->target_date,
        ];
    }
}
