<?php

namespace Tests\Unit;

use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\DeadlinePassedNotification;
use App\Notifications\DeadlineSoonNotification;
use App\Notifications\DeadlineTodayNotification;
use App\Notifications\ReadingPlanExpiredNotification;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    #[Test]
    public function deadline_soon_notification_has_expected_fields()
    {
        $plan = ReadingPlan::factory()->make([
            'id' => 1,
            'target_date' => Carbon::now()->addDays(3),
        ]);

        $notification = new DeadlineSoonNotification($plan);
        $data = $notification->toDatabase(new User);

        $this->assertEquals('three_days_before', $data['timing']);
        $this->assertEquals(1, $data['plan_id']);
        $this->assertEquals($plan->target_date, $data['target_date']);
    }

    #[Test]
    public function deadline_today_notification_has_expected_fields()
    {
        $plan = ReadingPlan::factory()->make([
            'id' => 2,
            'target_date' => Carbon::now(),
        ]);

        $notification = new DeadlineTodayNotification($plan);
        $data = $notification->toDatabase(new User);

        $this->assertEquals('on_due_date', $data['timing']);
        $this->assertEquals(2, $data['plan_id']);
        $this->assertEquals($plan->target_date, $data['target_date']);
    }

    #[Test]
    public function deadline_passed_notification_has_expected_fields()
    {
        $plan = ReadingPlan::factory()->make([
            'id' => 3,
            'target_date' => Carbon::now()->subDays(3),
        ]);

        $notification = new DeadlinePassedNotification($plan);
        $data = $notification->toDatabase(new User);

        $this->assertEquals('three_days_after', $data['timing']);
        $this->assertEquals(3, $data['plan_id']);
        $this->assertEquals($plan->target_date, $data['target_date']);
    }

    #[Test]
    public function expired_notification_has_expected_fields()
    {
        $plan = ReadingPlan::factory()->make([
            'id' => 4,
            'target_date' => Carbon::now()->subDays(7),
        ]);

        $notification = new ReadingPlanExpiredNotification($plan);
        $data = $notification->toDatabase(new User);

        $this->assertEquals('expired', $data['timing']);
        $this->assertEquals(4, $data['plan_id']);
        $this->assertEquals($plan->target_date, $data['target_date']);
    }
}
