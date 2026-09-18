<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookStoreTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_cannot_create_book()
    {
        $genre = Genre::factory()->create();

        $payload = [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2024-01-01',
            'description' => 'テスト用の説明文です。',
            'genres' => [$genre->id],
        ];

        $this->postJson('/api/v1/books', $payload)
            ->assertStatus(401);
    }

    #[Test]
    public function authenticated_user_can_create_book()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $genre = Genre::factory()->create();

        $payload = [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2024-01-01',
            'description' => 'テスト用の説明文です。',
            'genres' => [$genre->id],
        ];

        $response = $this->postJson('/api/v1/books', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'title' => 'Laravel入門',
                    'author' => '山田太郎',
                    'isbn' => '9781234567890',
                    'published_date' => '2024-01-01',
                    'description' => 'テスト用の説明文です。',
                ],
            ]);

        $bookId = $response->json('data.id');

        $this->assertDatabaseHas('books', [
            'id' => $bookId,
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
        ]);

        $this->assertDatabaseHas('book_genres', [
            'book_id' => $bookId,
            'genre_id' => $genre->id,
        ]);
    }

    #[Test]
    public function title_is_required()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'title' => '',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2024-01-01',
            'description' => '説明',
            'genres' => [Genre::factory()->create()->id],
        ];

        $this->postJson('/api/v1/books', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    #[Test]
    public function author_is_required()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'title' => 'Laravel入門',
            'author' => '',
            'isbn' => '9781234567890',
            'published_date' => '2024-01-01',
            'description' => '説明',
            'genres' => [Genre::factory()->create()->id],
        ];

        $this->postJson('/api/v1/books', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['author']);
    }

    #[Test]
    public function description_must_be_string()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2024-01-01',
            'description' => 123,
            'genres' => [Genre::factory()->create()->id],
        ];

        $this->postJson('/api/v1/books', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['description']);
    }

    #[Test]
    public function image_url_must_be_valid_url()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2024-01-01',
            'description' => '説明',
            'image_url' => 'invalid-url',
            'genres' => [Genre::factory()->create()->id],
        ];

        $this->postJson('/api/v1/books', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['image_url']);
    }

    #[Test]
    public function genres_is_required()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2024-01-01',
            'description' => '説明',
            'genres' => null,
        ];

        $this->postJson('/api/v1/books', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['genres']);
    }

    #[Test]
    public function genres_must_be_array()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2024-01-01',
            'description' => '説明',
            'genres' => 'not-array',
        ];

        $this->postJson('/api/v1/books', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['genres']);
    }

    #[Test]
    public function genres_must_exist()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2024-01-01',
            'description' => '説明',
            'genres' => [99999],
        ];

        $this->postJson('/api/v1/books', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['genres.0']);
    }

    #[Test]
    public function isbn_is_required()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '',
            'published_date' => '2024-01-01',
            'description' => '説明',
            'genres' => [Genre::factory()->create()->id],
        ];

        $this->postJson('/api/v1/books', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['isbn']);
    }

    #[Test]
    public function isbn_must_be_13_characters()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '123',
            'published_date' => '2024-01-01',
            'description' => '説明',
            'genres' => [Genre::factory()->create()->id],
        ];

        $this->postJson('/api/v1/books', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['isbn']);
    }

    #[Test]
    public function isbn_must_be_unique()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $user = auth()->user();
        $genre = Genre::factory()->create();

        Book::factory()->create([
            'isbn' => '9781234567890',
            'user_id' => $user->id,
        ]);

        $payload = [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '2024-01-01',
            'description' => '説明',
            'genres' => [$genre->id],
        ];

        $this->postJson('/api/v1/books', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['isbn']);
    }

    #[Test]
    public function published_date_is_required()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $payload = [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => '',
            'description' => '説明',
            'genres' => [Genre::factory()->create()->id],
        ];

        $this->postJson('/api/v1/books', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['published_date']);
    }

    #[Test]
    public function published_date_must_be_valid_date()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $payload = [
            'title' => 'Laravel入門',
            'author' => '山田太郎',
            'isbn' => '9781234567890',
            'published_date' => 'invalid-date',
            'description' => '説明',
            'genres' => [Genre::factory()->create()->id],
        ];

        $this->postJson('/api/v1/books', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['published_date']);
    }
}
