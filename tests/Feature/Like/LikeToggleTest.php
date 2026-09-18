<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LikeToggleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_like_toggle_adds_like()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $response = $this->post("/reviews/{$review->id}/like");

        $response->assertRedirect();

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    #[Test]
    public function test_like_toggle_removes_like()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $user->likedReviews()->attach($review->id);

        $response = $this->post("/reviews/{$review->id}/like");

        $response->assertRedirect();

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    #[Test]
    public function test_like_toggle_redirects_when_not_logged_in()
    {
        $review = Review::factory()->create();

        $response = $this->post("/reviews/{$review->id}/like");

        $response->assertRedirect('/login');
    }

    #[Test]
    public function test_like_toggle_returns_404_for_nonexistent_review()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/reviews/999999/like');

        $response->assertStatus(404);
    }
}
