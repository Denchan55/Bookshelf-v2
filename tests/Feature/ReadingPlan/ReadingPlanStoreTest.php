<?php

namespace Tests\Feature\ReadingPlan;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReadingPlanStoreTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_creates_a_reading_plan()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'book_id' => Book::factory()->create(['title' => 'Test Book'])->id,
            'target_date' => now()->addDays(5)->toDateString(),
        ];

        $response = $this->post('/reading-plans', $data);

        $response->assertStatus(302);
        $response->assertRedirect('/reading-plans');

        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $data['book_id'],
            'target_date' => $data['target_date'],
            'status' => 'not_started',
        ]);

    }

    #[Test]
    public function it_fails_validation_when_required_fields_are_missing()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/reading-plans', [
            'book_id' => '',
            'target_date' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['book_id', 'target_date']);
    }
}
