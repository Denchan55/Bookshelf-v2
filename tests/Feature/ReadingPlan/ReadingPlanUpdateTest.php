<?php

namespace Tests\Feature\ReadingPlan;

use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReadingPlanUpdateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_updates_a_reading_plan()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'target_date' => now()->addDays(3)->toDateString(),
            'status' => 'not_started',
        ]);

        $data = [
            'book_id' => $plan->book_id,
            'target_date' => now()->addDays(10)->toDateString(),
            'status' => 'in_progress',
        ];

        $response = $this->put("/reading-plans/{$plan->id}", $data);

        $response->assertStatus(302);
        $response->assertRedirect('/reading-plans');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'target_date' => $data['target_date'],
            'status' => $data['status'],
        ]);
    }

    #[Test]
    public function it_cannot_update_other_users_reading_plan()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user);

        $otherPlan = ReadingPlan::factory()->create([
            'user_id' => $other->id,
        ]);

        $data = [
            'book_id' => $otherPlan->book_id,
            'target_date' => now()->addDays(10)->toDateString(),
            'status' => 'in_progress',
        ];

        $response = $this->put("/reading-plans/{$otherPlan->id}", $data);

        $response->assertStatus(403);
    }

    #[Test]
    public function it_fails_validation_when_required_fields_are_missing()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->put("/reading-plans/{$plan->id}", [
            'book_id' => '',
            'target_date' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['book_id', 'target_date']);
    }
}
