<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;

class BookUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }
    private function createBook()
    {
        $book = Book::factory()->create([
            'title' => '旧タイトル',
            'author' => '旧著者',
            'isbn' => '9781111111111',
            'published_at' => '2020-01-01',
            'description' => '旧説明',
            'image_url' => 'https://example.com/old.jpg',
            'user_id' => auth()->id(),
        ]);

        $genre = Genre::factory()->create();
        $book->genres()->sync([$genre->id]);

        return $book;
    }
    /** @test */
    public function can_update_book()
    {
        // 既存の本を作成
        $book = Book::factory()->create([
            'title' => '旧タイトル',
            'author' => '旧著者',
            'isbn' => '9781111111111',
            'published_at' => '2020-01-01',
            'description' => '旧説明',
            'image_url' => 'https://example.com/old.jpg',
            'user_id' => auth()->id(),
        ]);

        // 既存ジャンル
        $genre1 = Genre::factory()->create();
        $genre2 = Genre::factory()->create();

        $book->genres()->sync([$genre1->id]);

        // 更新データ
        $payload = [
            'title' => '新タイトル',
            'author' => '新著者',
            'isbn' => '9782222222222',
            'published_at' => '2024-01-01',
            'description' => '新しい説明文です。',
            'image_url' => 'https://example.com/new.jpg',
            'genres' => [$genre2->id], // ⭐ sync が正しく動くか確認
        ];

        $response = $this->putJson("/api/v1/books/{$book->id}", $payload);

        // ステータスコード
        $response->assertStatus(200);

        // JSONレスポンス
        $response->assertJson([
            'data' => [
                'title' => '新タイトル',
                'author' => '新著者',
                'isbn' => '9782222222222',
                'published_at' => '2024-01-01',
                'description' => '新しい説明文です。',
                'image_url' => 'https://example.com/new.jpg',
            ]
        ]);

        // DBが更新されていること
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '新タイトル',
            'author' => '新著者',
            'isbn' => '9782222222222',
        ]);

        // ジャンルが更新されていること
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre2->id,
        ]);

        // 古いジャンルが外れていること
        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre1->id,
        ]);
    }

    /** @test */
public function title_is_required()
{
    $book = $this->createBook();

    $payload = [
        'title' => '',
        'author' => '新著者',
        'isbn' => '9782222222222',
        'published_at' => '2024-01-01',
        'description' => '説明',
        'genres' => [Genre::factory()->create()->id],
    ];

    $this->putJson("/api/v1/books/{$book->id}", $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['title']);
}
/** @test */
public function author_is_required()
{
    $book = $this->createBook();

    $payload = [
        'title' => '新タイトル',
        'author' => '',
        'isbn' => '9782222222222',
        'published_at' => '2024-01-01',
        'description' => '説明',
        'genres' => [Genre::factory()->create()->id],
    ];

    $this->putJson("/api/v1/books/{$book->id}", $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['author']);
}
/** @test */
public function description_must_be_string()
{
    $book = $this->createBook();

    $payload = [
        'title' => '新タイトル',
        'author' => '新著者',
        'isbn' => '9782222222222',
        'published_at' => '2024-01-01',
        'description' => 123,
        'genres' => [Genre::factory()->create()->id],
    ];

    $this->putJson("/api/v1/books/{$book->id}", $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['description']);
}
/** @test */
public function image_url_must_be_valid_url()
{
    $book = $this->createBook();

    $payload = [
        'title' => '新タイトル',
        'author' => '新著者',
        'isbn' => '9782222222222',
        'published_at' => '2024-01-01',
        'description' => '説明',
        'image_url' => 'invalid-url',
        'genres' => [Genre::factory()->create()->id],
    ];

    $this->putJson("/api/v1/books/{$book->id}", $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['image_url']);
}
/** @test */
public function genres_is_required()
{
    $book = $this->createBook();

    $payload = [
        'title' => '新タイトル',
        'author' => '新著者',
        'isbn' => '9782222222222',
        'published_at' => '2024-01-01',
        'description' => '説明',
        'genres' => null,
    ];

    $this->putJson("/api/v1/books/{$book->id}", $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['genres']);
}
/** @test */
public function genres_must_be_array()
{
    $book = $this->createBook();

    $payload = [
        'title' => '新タイトル',
        'author' => '新著者',
        'isbn' => '9782222222222',
        'published_at' => '2024-01-01',
        'description' => '説明',
        'genres' => 'not-array',
    ];

    $this->putJson("/api/v1/books/{$book->id}", $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['genres']);
}
/** @test */
public function genres_must_exist()
{
    $book = $this->createBook();

    $payload = [
        'title' => '新タイトル',
        'author' => '新著者',
        'isbn' => '9782222222222',
        'published_at' => '2024-01-01',
        'description' => '説明',
        'genres' => [99999],
    ];

    $this->putJson("/api/v1/books/{$book->id}", $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['genres.0']);
}
/** @test */
public function isbn_is_required()
{
    $book = $this->createBook();

    $payload = [
        'title' => '新タイトル',
        'author' => '新著者',
        'isbn' => '',
        'published_at' => '2024-01-01',
        'description' => '説明',
        'genres' => [Genre::factory()->create()->id],
    ];

    $this->putJson("/api/v1/books/{$book->id}", $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['isbn']);
}
/** @test */
public function isbn_must_be_13_characters()
{
    $book = $this->createBook();

    $payload = [
        'title' => '新タイトル',
        'author' => '新著者',
        'isbn' => '123',
        'published_at' => '2024-01-01',
        'description' => '説明',
        'genres' => [Genre::factory()->create()->id],
    ];

    $this->putJson("/api/v1/books/{$book->id}", $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['isbn']);
}
/** @test */
public function isbn_must_be_unique_except_self()
{
    $book = $this->createBook();

    // 別の本を作成（重複チェック用）
    $other = Book::factory()->create([
        'isbn' => '9789999999999',
        'user_id' => auth()->id(),
    ]);

    $payload = [
        'title' => '新タイトル',
        'author' => '新著者',
        'isbn' => '9789999999999', // 他の本と重複
        'published_at' => '2024-01-01',
        'description' => '説明',
        'genres' => [Genre::factory()->create()->id],
    ];

    $this->putJson("/api/v1/books/{$book->id}", $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['isbn']);
}
/** @test */
public function published_at_is_required()
{
    $book = $this->createBook();

    $payload = [
        'title' => '新タイトル',
        'author' => '新著者',
        'isbn' => '9782222222222',
        'published_at' => '',
        'description' => '説明',
        'genres' => [Genre::factory()->create()->id],
    ];

    $this->putJson("/api/v1/books/{$book->id}", $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['published_at']);
}
/** @test */
public function published_at_must_be_valid_date()
{
    $book = $this->createBook();

    $payload = [
        'title' => '新タイトル',
        'author' => '新著者',
        'isbn' => '9782222222222',
        'published_at' => 'invalid-date',
        'description' => '説明',
        'genres' => [Genre::factory()->create()->id],
    ];

    $this->putJson("/api/v1/books/{$book->id}", $payload)
         ->assertStatus(422)
         ->assertJsonValidationErrors(['published_at']);
}

}
