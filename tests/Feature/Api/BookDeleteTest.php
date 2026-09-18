<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookDeleteTest extends TestCase
{
    use RefreshDatabase;

    private function createBookFor(User $user)
    {
        $book = Book::factory()->create([
            'title' => '削除対象',
            'author' => '著者',
            'isbn' => '9781111111111',
            'published_date' => '2020-01-01',
            'description' => '説明',
            'image_url' => 'https://example.com/img.jpg',
            'user_id' => $user->id,
        ]);

        $genre = Genre::factory()->create();
        $book->genres()->sync([$genre->id]);

        return $book;
    }

    #[Test]
    public function owner_can_delete_book()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $book = $this->createBookFor($user);

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);

        $this->assertDatabaseMissing('book_genres', [
            'book_id' => $book->id,
        ]);
    }

    #[Test]
    public function non_owner_cannot_delete_book()
    {
        $owner = User::factory()->create();
        $book = $this->createBookFor($owner);

        $other = User::factory()->create();
        Sanctum::actingAs($other);

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function guest_cannot_delete_book()
    {
        $owner = User::factory()->create();
        $book = $this->createBookFor($owner);

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(401);
    }

    #[Test]
    public function cannot_delete_nonexistent_book()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/v1/books/99999');

        $response->assertStatus(404);
    }

    #[Test]
    public function cannot_delete_book_twice()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $book = $this->createBookFor($user);

        $this->deleteJson("/api/v1/books/{$book->id}")
            ->assertStatus(204);

        $this->deleteJson("/api/v1/books/{$book->id}")
            ->assertStatus(404);
    }
}
