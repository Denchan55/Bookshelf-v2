<?php

namespace Tests\Feature\ReadingPlan;

use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReadingPlanStatusTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_reading_plan_status_to_completed()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => 'in_progress',
            'completed_at' => null,
        ]);

        $data = [
            'status' => 'completed',
        ];

        $response = $this->patch("/reading-plans/{$plan->id}/status", $data);

        $response->assertStatus(302);
        $response->assertRedirect('/reading-plans');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => 'completed',
        ]);

        $this->assertNotNull(ReadingPlan::find($plan->id)->completed_at);
    }

    #[Test]
    public function it_cannot_update_status_of_other_users_reading_plan()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user);

        $otherPlan = ReadingPlan::factory()->create([
            'user_id' => $other->id,
            'status' => 'in_progress',
        ]);

        $response = $this->patch("/reading-plans/{$otherPlan->id}/status", [
            'status' => 'completed',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function it_fails_validation_when_status_is_missing()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->patch("/reading-plans/{$plan->id}/status", [
            'status' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['status']);
    }
}
