<?php

namespace Tests\Feature\ReadingPlan;

use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReadingPlanIndexTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_displays_reading_plan_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        ReadingPlan::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->get('/reading-plans');

        $response->assertStatus(200);
        $response->assertViewIs('reading-plans.index');
        $response->assertSee('読書計画');
    }

    #[Test]
    public function it_does_not_display_other_users_reading_plans()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user);

        $otherPlan = ReadingPlan::factory()->create([
            'user_id' => $other->id,
        ]);

        $response = $this->get('/reading-plans');

        $response->assertStatus(200);
        $response->assertDontSee($otherPlan->target_date);

    }
}
