<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Review;

class ReviewDeleteTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
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
public function test_review_delete_redirects_when_not_logged_in()
{
    $review = Review::factory()->create();

    $response = $this->delete("/reviews/{$review->id}");

    $response->assertRedirect('/login');
}
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
public function test_review_delete_returns_404_for_nonexistent_review()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->delete("/reviews/999999");

    $response->assertStatus(404);
}

}
