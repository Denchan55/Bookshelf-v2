<?php

namespace Tests\Feature\ReadingPlan;

use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReadingPlanDeleteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_deletes_a_reading_plan()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->delete("/reading-plans/{$plan->id}");

        $response->assertStatus(302);
        $response->assertRedirect('/reading-plans');

        $this->assertDatabaseMissing('reading_plans', [
            'id' => $plan->id,
        ]);
    }

    #[Test]
    public function it_cannot_delete_other_users_reading_plan()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user);

        $otherPlan = ReadingPlan::factory()->create([
            'user_id' => $other->id,
        ]);

        $response = $this->delete("/reading-plans/{$otherPlan->id}");

        $response->assertStatus(403);
    }
}
