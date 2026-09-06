<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Book;
use App\Models\Genre;


class BookStoreTest extends TestCase
{
    use RefreshDatabase;
protected function setUp(): void
{
    parent::setUp();
    $this->actingAs(\App\Models\User::factory()->create());
}

    /** @test */
public function can_create_book()
{
    $user = \App\Models\User::factory()->create();
    $genre = Genre::factory()->create();

    $payload = [
        'title' => 'Laravel入門',
        'author' => '山田太郎',
        'isbn' => '9781234567890',
        'published_at' => '2024-01-01',
        'description' => 'テスト用の説明文です。',
        'genres' => [$genre->id],   // ⭐ これが必須
    ];

    $response = $this->postJson('/api/v1/books', $payload);

    $response->assertStatus(201)
             ->assertJson([
                 'data' => [
                     'title' => 'Laravel入門',
                     'author' => '山田太郎',
                     'isbn' => '9781234567890',
                     'published_at' => '2024-01-01T00:00:00.000000Z',
                     'description' => 'テスト用の説明文です。',
                 ]
             ]);

    $this->assertDatabaseHas('books', [
        'title' => 'Laravel入門',
        'author' => '山田太郎',
        'isbn' => '9781234567890',
    ]);

    // ⭐ genres が紐付いていることも確認できる
    $this->assertDatabaseHas('book_genre', [
        'book_id' => Book::first()->id,
        'genre_id' => $genre->id,
    ]);
}
/** @test */
/** @test */
public function title_is_required()
{
    $payload = [
        'title' => '',
        'author' => '山田太郎',
        'isbn' => '9781234567890',
        'published_at' => '2024-01-01',
        'description' => '説明',
        'genres' => [\App\Models\Genre::factory()->create()->id],
    ];

    $this->postJson('/api/v1/books', $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['title']);
}
/** @test */
public function author_is_required()
{
    $payload = [
        'title' => 'Laravel入門',
        'author' => '',
        'isbn' => '9781234567890',
        'published_at' => '2024-01-01',
        'description' => '説明',
        'genres' => [\App\Models\Genre::factory()->create()->id],
    ];

    $this->postJson('/api/v1/books', $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['author']);
}
/** @test */
public function description_must_be_string()
{
    $payload = [
        'title' => 'Laravel入門',
        'author' => '山田太郎',
        'isbn' => '9781234567890',
        'published_at' => '2024-01-01',
        'description' => 123, // 数値はNG
        'genres' => [\App\Models\Genre::factory()->create()->id],
    ];

    $this->postJson('/api/v1/books', $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['description']);
}
/** @test */
public function image_url_must_be_valid_url()
{
    $payload = [
        'title' => 'Laravel入門',
        'author' => '山田太郎',
        'isbn' => '9781234567890',
        'published_at' => '2024-01-01',
        'description' => '説明',
        'image_url' => 'invalid-url',
        'genres' => [\App\Models\Genre::factory()->create()->id],
    ];

    $this->postJson('/api/v1/books', $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['image_url']);
}
/** @test */
public function genres_is_required()
{
    $payload = [
        'title' => 'Laravel入門',
        'author' => '山田太郎',
        'isbn' => '9781234567890',
        'published_at' => '2024-01-01',
        'description' => '説明',
        'genres' => null,
    ];

    $this->postJson('/api/v1/books', $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['genres']);
}
/** @test */
public function genres_must_be_array()
{
    $payload = [
        'title' => 'Laravel入門',
        'author' => '山田太郎',
        'isbn' => '9781234567890',
        'published_at' => '2024-01-01',
        'description' => '説明',
        'genres' => 'not-array',
    ];

    $this->postJson('/api/v1/books', $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['genres']);
}
/** @test */
public function genres_must_exist()
{
    $payload = [
        'title' => 'Laravel入門',
        'author' => '山田太郎',
        'isbn' => '9781234567890',
        'published_at' => '2024-01-01',
        'description' => '説明',
        'genres' => [99999], // 存在しない
    ];

    $this->postJson('/api/v1/books', $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['genres.0']);
}
/** @test */
public function isbn_is_required()
{
    $payload = [
        'title' => 'Laravel入門',
        'author' => '山田太郎',
        'isbn' => '',
        'published_at' => '2024-01-01',
        'description' => '説明',
        'genres' => [\App\Models\Genre::factory()->create()->id],
    ];

    $this->postJson('/api/v1/books', $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['isbn']);
}
/** @test */
public function isbn_must_be_13_characters()
{
    $payload = [
        'title' => 'Laravel入門',
        'author' => '山田太郎',
        'isbn' => '123', // 13桁じゃない
        'published_at' => '2024-01-01',
        'description' => '説明',
        'genres' => [\App\Models\Genre::factory()->create()->id],
    ];

    $this->postJson('/api/v1/books', $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['isbn']);
}
/** @test */
public function isbn_must_be_unique()
{
    $user = auth()->user();
    $genre = \App\Models\Genre::factory()->create();

    \App\Models\Book::factory()->create([
        'isbn' => '9781234567890',
        'user_id' => $user->id,
    ]);

    $payload = [
        'title' => 'Laravel入門',
        'author' => '山田太郎',
        'isbn' => '9781234567890', // 重複
        'published_at' => '2024-01-01',
        'description' => '説明',
        'genres' => [$genre->id],
    ];

    $this->postJson('/api/v1/books', $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['isbn']);
}
/** @test */
public function published_at_is_required()
{
    $payload = [
        'title' => 'Laravel入門',
        'author' => '山田太郎',
        'isbn' => '9781234567890',
        'published_at' => '',
        'description' => '説明',
        'genres' => [\App\Models\Genre::factory()->create()->id],
    ];

    $this->postJson('/api/v1/books', $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['published_at']);
}
/** @test */
public function published_at_must_be_valid_date()
{
    $payload = [
        'title' => 'Laravel入門',
        'author' => '山田太郎',
        'isbn' => '9781234567890',
        'published_at' => 'invalid-date',
        'description' => '説明',
        'genres' => [\App\Models\Genre::factory()->create()->id],
    ];

    $this->postJson('/api/v1/books', $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['published_at']);
}


}
