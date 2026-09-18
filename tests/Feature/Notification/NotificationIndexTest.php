<?php

namespace Tests\Feature;

use App\Models\readingPlan;
use App\Models\User;
use App\Notifications\DeadlineSoonNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationIndexTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_view_only_their_notifications()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $user->notify(new DeadlineSoonNotification(
            readingPlan::factory()->create(['user_id' => $user->id])
        ));

        $otherUser->notify(new DeadlineSoonNotification(
            readingPlan::factory()->create(['user_id' => $otherUser->id])
        ));

        $this->actingAs($user);

        $response = $this->get('/notifications');

        $myNotification = DB::table('notifications')
            ->where('notifiable_id', $user->id)
            ->first();

        $response->assertSee($myNotification->id);

        $otherNotification = DB::table('notifications')
            ->where('notifiable_id', $otherUser->id)
            ->first();

        $response->assertDontSee($otherNotification->id);
    }
}
