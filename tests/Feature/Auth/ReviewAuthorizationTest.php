<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Review;

class ReviewAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_access_review_edit_page()
    {
        $owner = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $owner->id,
        ]);

        $this->actingAs($owner);

        $response = $this->get("/reviews/{$review->id}/edit");
        $response->assertStatus(200);
    }

    public function test_non_owner_cannot_access_review_edit_page()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $owner->id,
        ]);

        $this->actingAs($other);

        $response = $this->get("/reviews/{$review->id}/edit");
        $response->assertStatus(403);
    }

    public function test_owner_can_update_review()
    {
        $owner = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $owner->id,
        ]);

        $this->actingAs($owner);

        $response = $this->put("/reviews/{$review->id}", [
            'rating' => 4,
            'comment' => 'Updated comment',
        ]);

        $response->assertRedirect("/books/{$book->id}");
    }

    public function test_non_owner_cannot_update_review()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $owner->id,
        ]);

        $this->actingAs($other);

        $response = $this->put("/reviews/{$review->id}", [
            'rating' => 4,
            'comment' => 'Updated comment',
        ]);

        $response->assertStatus(403);
    }

    public function test_owner_can_delete_review()
    {
        $owner = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $owner->id,
        ]);

        $this->actingAs($owner);

        $response = $this->delete("/reviews/{$review->id}");
        $response->assertRedirect("/books/{$book->id}");
    }

    public function test_non_owner_cannot_delete_review()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);
        $review = Review::factory()->create([
            'book_id' => $book->id,
            'user_id' => $owner->id,
        ]);

        $this->actingAs($other);

        $response = $this->delete("/reviews/{$review->id}");
        $response->assertStatus(403);
    }
}
