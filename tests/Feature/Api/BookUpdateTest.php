<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function createBookFor(User $user)
    {
        $book = Book::factory()->create([
            'title' => '旧タイトル',
            'author' => '旧著者',
            'isbn' => '9781111111111',
            'published_date' => '2020-01-01',
            'description' => '旧説明',
            'image_url' => 'https://example.com/old.jpg',
            'user_id' => $user->id,
        ]);

        $genre = Genre::factory()->create();
        $book->genres()->sync([$genre->id]);

        return [$book, $genre];
    }

    #[Test]
    public function owner_can_update_book()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$book, $oldGenre] = $this->createBookFor($user);

        $newGenre = Genre::factory()->create();

        $payload = [
            'title' => '新タイトル',
            'author' => '新著者',
            'isbn' => '9782222222222',
            'published_date' => '2024-01-01',
            'description' => '新しい説明文です。',
            'image_url' => 'https://example.com/new.jpg',
            'genres' => [$newGenre->id],
        ];

        $response = $this->putJson("/api/v1/books/{$book->id}", $payload);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'title' => '新タイトル',
                    'author' => '新著者',
                    'isbn' => '9782222222222',
                    'published_date' => '2024-01-01',
                    'description' => '新しい説明文です。',
                    'image_url' => 'https://example.com/new.jpg',
                ],
            ]);

        $this->assertDatabaseHas('book_genres', [
            'book_id' => $book->id,
            'genre_id' => $newGenre->id,
        ]);

        $this->assertDatabaseMissing('book_genres', [
            'book_id' => $book->id,
            'genre_id' => $oldGenre->id,
        ]);
    }

    #[Test]
    public function non_owner_cannot_update_book()
    {

        $owner = User::factory()->create();
        [$book] = $this->createBookFor($owner);

        $other = User::factory()->create();
        Sanctum::actingAs($other);

        $payload = [
            'title' => '新タイトル',
            'author' => '新著者',
            'isbn' => '9782222222222',
            'published_date' => '2024-01-01',
            'description' => '説明',
            'image_url' => 'https://example.com/new.jpg',
            'genres' => [Genre::factory()->create()->id],
        ];

        $response = $this->putJson("/api/v1/books/{$book->id}", $payload);

        $response->assertStatus(403);
    }

    #[Test]
    public function guest_cannot_update_book()
    {
        $owner = User::factory()->create();
        [$book] = $this->createBookFor($owner);

        $response = $this->putJson("/api/v1/books/{$book->id}", [
            'title' => '新タイトル',
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function title_is_required()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$book] = $this->createBookFor($user);

        $payload = [
            'title' => '',
            'author' => '新著者',
            'isbn' => '9782222222222',
            'published_date' => '2024-01-01',
            'description' => '説明',
            'genres' => [Genre::factory()->create()->id],
        ];

        $this->putJson("/api/v1/books/{$book->id}", $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    #[Test]
    public function author_is_required()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$book, $oldGenre] = $this->createBookFor($user);

        $payload = [
            'title' => '新タイトル',
            'author' => '',
            'isbn' => '9782222222222',
            'published_date' => '2024-01-01',
            'description' => '説明',
            'genres' => [Genre::factory()->create()->id],
        ];

        $this->putJson("/api/v1/books/{$book->id}", $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['author']);
    }

    #[Test]
    public function description_must_be_string()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$book, $oldGenre] = $this->createBookFor($user);

        $payload = [
            'title' => '新タイトル',
            'author' => '新著者',
            'isbn' => '9782222222222',
            'published_date' => '2024-01-01',
            'description' => 123,
            'genres' => [Genre::factory()->create()->id],
        ];

        $this->putJson("/api/v1/books/{$book->id}", $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }

    #[Test]
    public function image_url_must_be_valid_url()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$book, $oldGenre] = $this->createBookFor($user);

        $payload = [
            'title' => '新タイトル',
            'author' => '新著者',
            'isbn' => '9782222222222',
            'published_date' => '2024-01-01',
            'description' => '説明',
            'image_url' => 'invalid-url',
            'genres' => [Genre::factory()->create()->id],
        ];

        $this->putJson("/api/v1/books/{$book->id}", $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['image_url']);
    }

    #[Test]
    public function genres_is_required()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$book, $oldGenre] = $this->createBookFor($user);

        $payload = [
            'title' => '新タイトル',
            'author' => '新著者',
            'isbn' => '9782222222222',
            'published_date' => '2024-01-01',
            'description' => '説明',
            'genres' => null,
        ];

        $this->putJson("/api/v1/books/{$book->id}", $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['genres']);
    }

    #[Test]
    public function genres_must_be_array()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$book, $oldGenre] = $this->createBookFor($user);

        $payload = [
            'title' => '新タイトル',
            'author' => '新著者',
            'isbn' => '9782222222222',
            'published_date' => '2024-01-01',
            'description' => '説明',
            'genres' => 'not-array',
        ];

        $this->putJson("/api/v1/books/{$book->id}", $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['genres']);
    }

    #[Test]
    public function genres_must_exist()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$book, $oldGenre] = $this->createBookFor($user);

        $payload = [
            'title' => '新タイトル',
            'author' => '新著者',
            'isbn' => '9782222222222',
            'published_date' => '2024-01-01',
            'description' => '説明',
            'genres' => [99999],
        ];

        $this->putJson("/api/v1/books/{$book->id}", $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['genres.0']);
    }

    #[Test]
    public function isbn_is_required()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$book, $oldGenre] = $this->createBookFor($user);

        $payload = [
            'title' => '新タイトル',
            'author' => '新著者',
            'isbn' => '',
            'published_date' => '2024-01-01',
            'description' => '説明',
            'genres' => [Genre::factory()->create()->id],
        ];

        $this->putJson("/api/v1/books/{$book->id}", $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['isbn']);
    }

    #[Test]
    public function isbn_must_be_13_characters()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$book, $oldGenre] = $this->createBookFor($user);

        $payload = [
            'title' => '新タイトル',
            'author' => '新著者',
            'isbn' => '123',
            'published_date' => '2024-01-01',
            'description' => '説明',
            'genres' => [Genre::factory()->create()->id],
        ];

        $this->putJson("/api/v1/books/{$book->id}", $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['isbn']);
    }

    #[Test]
    public function isbn_must_be_unique_except_self()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$book, $oldGenre] = $this->createBookFor($user);

        $other = Book::factory()->create([
            'isbn' => '9789999999999',
            'user_id' => auth()->id(),
        ]);

        $payload = [
            'title' => '新タイトル',
            'author' => '新著者',
            'isbn' => '9789999999999',
            'published_date' => '2024-01-01',
            'description' => '説明',
            'genres' => [Genre::factory()->create()->id],
        ];

        $this->putJson("/api/v1/books/{$book->id}", $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['isbn']);
    }

    #[Test]
    public function published_date_is_required()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$book, $oldGenre] = $this->createBookFor($user);

        $payload = [
            'title' => '新タイトル',
            'author' => '新著者',
            'isbn' => '9782222222222',
            'published_date' => '',
            'description' => '説明',
            'genres' => [Genre::factory()->create()->id],
        ];

        $this->putJson("/api/v1/books/{$book->id}", $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['published_date']);
    }

    #[Test]
    public function published_date_must_be_valid_date()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        [$book, $oldGenre] = $this->createBookFor($user);

        $payload = [
            'title' => '新タイトル',
            'author' => '新著者',
            'isbn' => '9782222222222',
            'published_date' => 'invalid-date',
            'description' => '説明',
            'genres' => [Genre::factory()->create()->id],
        ];

        $this->putJson("/api/v1/books/{$book->id}", $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['published_date']);
    }
}
