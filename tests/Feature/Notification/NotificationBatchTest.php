<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationBatchTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_pre_deadline_notification()
    {
        Carbon::setTestNow('2026-09-10');

        $user = User::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => Carbon::now()->addDays(3),
            'status' => ReadingPlanStatus::NOT_STARTED,
        ]);

        $this->artisan('reading-plan:notify');

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
        ]);

        $notification = DB::table('notifications')->first();
        $data = json_decode($notification->data, true);

        $this->assertEquals('three_days_before', $data['timing']);
        $this->assertEquals($plan->id, $data['plan_id']);
    }

    #[Test]
    public function it_creates_deadline_today_notification()
    {
        Carbon::setTestNow('2026-09-10');

        $user = User::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => Carbon::now(),
            'status' => ReadingPlanStatus::NOT_STARTED,
        ]);

        $this->artisan('reading-plan:notify');

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
        ]);

        $notification = DB::table('notifications')->first();
        $data = json_decode($notification->data, true);

        $this->assertEquals('on_due_date', $data['timing']);

        $this->assertEquals($plan->id, $data['plan_id']);
    }

    #[Test]
    public function it_creates_deadline_passed_notification()
    {
        Carbon::setTestNow('2026-09-10');

        $user = User::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => Carbon::now()->subDays(3),
            'status' => ReadingPlanStatus::NOT_STARTED,
        ]);

        $this->artisan('reading-plan:notify');

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
        ]);

        $notification = DB::table('notifications')->first();
        $data = json_decode($notification->data, true);

        $this->assertEquals('three_days_after', $data['timing']);

        $this->assertEquals($plan->id, $data['plan_id']);
    }

    #[Test]
    public function it_creates_expired_notification()
    {
        Carbon::setTestNow('2026-09-10');

        $user = User::factory()->create();

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => Carbon::now()->subDays(7),
            'status' => ReadingPlanStatus::NOT_STARTED,
            'expired_sent_at' => null,
        ]);

        $this->artisan('reading-plan:notify');

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
        ]);

        $notification = DB::table('notifications')->first();
        $data = json_decode($notification->data, true);

        $this->assertEquals('expired', $data['timing']);
        $this->assertEquals($plan->id, $data['plan_id']);

        $plan->refresh();
        $this->assertEquals('expired', $plan->status->value);

        $this->assertNotNull($plan->expired_sent_at);
    }
}
