<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReviewDeleteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_review_delete_success()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->delete("/reviews/{$review->id}");

        $response->assertRedirect("/books/{$book->id}");
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    #[Test]
    public function test_review_delete_redirects_when_not_logged_in()
    {
        $review = Review::factory()->create();

        $response = $this->delete("/reviews/{$review->id}");

        $response->assertRedirect('/login');
    }

    #[Test]
    public function test_review_delete_fails_when_user_is_not_owner()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($user);

        $book = Book::factory()->create();

        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
        ]);

        $response = $this->delete("/reviews/{$review->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function test_review_delete_returns_404_for_nonexistent_review()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->delete('/reviews/999999');

        $response->assertStatus(404);
    }
}
