<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;


class ReviewStoreTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_review_store_success()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $book = Book::factory()->create();

    $response = $this->post("/books/{$book->id}/reviews", [
        'rating' => 5,
        'comment' => 'とても良かったです。',
    ]);

    $response->assertRedirect("/books/{$book->id}");
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('reviews', [
        'book_id' => $book->id,
        'user_id' => $user->id,
        'rating' => 5,
        'comment' => 'とても良かったです。',
    ]);
}
public function test_review_store_fails_when_rating_is_empty()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $book = Book::factory()->create();

    $response = $this->from("/books/{$book->id}")
                     ->post("/books/{$book->id}/reviews", [
                         'rating' => '',
                         'comment' => 'コメントだけ',
                     ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('rating');

    $this->assertDatabaseMissing('reviews', [
        'comment' => 'コメントだけ',
    ]);
}
public function test_review_store_fails_when_rating_is_out_of_range()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $book = Book::factory()->create();

    $response = $this->post("/books/{$book->id}/reviews", [
        'rating' => 6,
        'comment' => '範囲外',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('rating');
}
public function test_review_store_fails_when_comment_is_too_long()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $book = Book::factory()->create();

    $response = $this->post("/books/{$book->id}/reviews", [
        'rating' => 4,
        'comment' => str_repeat('あ', 1001),
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('comment');
}
public function test_review_store_redirects_when_not_logged_in()
{
    $book = Book::factory()->create();

    $response = $this->post("/books/{$book->id}/reviews", [
        'rating' => 5,
        'comment' => 'ログインしていない',
    ]);

    $response->assertRedirect('/login');
}
public function test_review_store_returns_404_for_nonexistent_book()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/books/999999/reviews', [
        'rating' => 5,
        'comment' => '存在しない書籍',
    ]);

    $response->assertStatus(404);
}

}
