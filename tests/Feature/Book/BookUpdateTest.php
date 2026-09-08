<?php

namespace Tests\Feature\Book;

use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
class BookUpdateTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_book_update_success()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    // ⭐ owner の book として作成する
    $book = Book::factory()->create(['user_id' => $user->id]);

    $genres = Genre::factory()->count(2)->create();

    $response = $this->put("/books/{$book->id}", [
        'title' => '新しいタイトル',
        'author' => '新しい著者',
        'isbn' => '9784101010014',
        'published_date' => '2020-01-01',
        'description' => '新しい説明文',
        'genres' => $genres->pluck('id')->toArray(),
        'image_url' => null,
    ]);

    $response->assertRedirect(route('books.show', $book));

    $this->assertDatabaseHas('books', [
        'id' => $book->id,
        'title' => '新しいタイトル',
    ]);
}


public function test_book_update_fails_when_title_is_empty()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $book = Book::factory()->create();
    $genres = Genre::factory()->count(2)->create();

    $response = $this->put("/books/{$book->id}", [
        'title' => '',
        'author' => '新しい著者',
        'isbn' => $book->isbn,
        'published_date' => '2020-01-01',
        'description' => '説明',
        'genres' => $genres->pluck('id')->toArray(),
        'image_url' => null,
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('title');
}
public function test_book_update_fails_when_isbn_is_duplicate()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $book = Book::factory()->create([
        'isbn' => '9784101010014',
    ]);

    $otherBook = Book::factory()->create([
        'isbn' => '9784101010015',
    ]);

    $genres = Genre::factory()->count(2)->create();

    $response = $this->put("/books/{$book->id}", [
        'title' => 'タイトル',
        'author' => '著者',
        'isbn' => $otherBook->isbn, // ← 他の本のISBNを使う
        'published_date' => '2020-01-01',
        'description' => '説明',
        'genres' => $genres->pluck('id')->toArray(),
        'image_url' => null,
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('isbn');
}
public function test_book_update_fails_when_published_date_is_invalid()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $book = Book::factory()->create();
    $genres = Genre::factory()->count(2)->create();

    $response = $this->put("/books/{$book->id}", [
        'title' => 'タイトル',
        'author' => '著者',
        'isbn' => $book->isbn,
        'published_date' => 'invalid-date',
        'description' => '説明',
        'genres' => $genres->pluck('id')->toArray(),
        'image_url' => null,
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('published_date');
}
public function test_book_update_fails_when_genres_is_empty()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $book = Book::factory()->create();

    $response = $this->put("/books/{$book->id}", [
        'title' => 'タイトル',
        'author' => '著者',
        'isbn' => $book->isbn,
        'published_date' => '2020-01-01',
        'description' => '説明',
        'genres' => [], // ← 空
        'image_url' => null,
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('genres');
}
public function test_book_update_fails_when_genres_contains_invalid_id()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $book = Book::factory()->create();
    $genres = Genre::factory()->count(2)->create();

    $response = $this->put("/books/{$book->id}", [
        'title' => 'タイトル',
        'author' => '著者',
        'isbn' => $book->isbn,
        'published_date' => '2020-01-01',
        'description' => '説明',
        'genres' => ['99999'], // ← 存在しないID
        'image_url' => null,
    ]);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('genres.*');
}
}
