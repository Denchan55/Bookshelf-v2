<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Review;

class ReviewUpdateTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
public function test_review_update_success()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $book = Book::factory()->create();

    $review = Review::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
        'rating' => 3,
        'comment' => '前のコメント',
    ]);

    $response = $this->put("/reviews/{$review->id}", [
    'rating' => 5,
    'comment' => '編集後のコメント',
]);


    $response->assertRedirect("/books/{$book->id}");
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('reviews', [
        'id' => $review->id,
        'rating' => 5,
        'comment' => '編集後のコメント',
    ]);
}
public function test_review_update_fails_when_rating_is_empty()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $book = Book::factory()->create();

    $review = Review::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
    ]);

    $response = $this->put("/reviews/{$review->id}", [
        'rating' => '',
        'comment' => 'コメントだけ',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('rating');

    $this->assertDatabaseHas('reviews', [
        'id' => $review->id,
        'comment' => $review->comment, // 元のまま
    ]);
}
public function test_review_update_fails_when_rating_is_out_of_range()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $book = Book::factory()->create();

    $review = Review::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
    ]);

    $response = $this->put("/reviews/{$review->id}", [
        'rating' => 6,
        'comment' => '範囲外',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('rating');
}
public function test_review_update_fails_when_comment_is_too_long()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $book = Book::factory()->create();

    $review = Review::factory()->create([
        'user_id' => $user->id,
        'book_id' => $book->id,
    ]);

    $response = $this->put("/reviews/{$review->id}", [
        'rating' => 4,
        'comment' => str_repeat('あ', 1001),
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('comment');
}
public function test_review_update_redirects_when_not_logged_in()
{
    $book = Book::factory()->create();
    $review = Review::factory()->create();

    $response = $this->put("/reviews/{$review->id}", [
        'rating' => 5,
        'comment' => 'ログインしていない',
    ]);

    $response->assertRedirect('/login');
}
public function test_review_update_fails_when_user_is_not_owner()
{
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user);

    $book = Book::factory()->create();

    $review = Review::factory()->create([
        'user_id' => $otherUser->id,
        'book_id' => $book->id,
    ]);

    $response = $this->put("/reviews/{$review->id}", [
        'rating' => 5,
        'comment' => '勝手に編集',
    ]);

    $response->assertStatus(403);
}
public function test_review_update_returns_404_for_nonexistent_review()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $book = Book::factory()->create();

    $response = $this->put("/reviews/999999", [
        'rating' => 5,
        'comment' => '存在しないレビュー',
    ]);

    $response->assertStatus(404);
}

}
