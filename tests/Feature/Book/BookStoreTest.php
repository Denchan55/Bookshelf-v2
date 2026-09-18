<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookStoreTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_book_store_creates_new_book()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genres = Genre::factory()->count(2)->create();

        $postData = [
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => 'テスト用の説明文です。',
            'image_url' => null,
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->post('/books', $postData);

        $book = Book::latest()->first();

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'user_id' => $user->id,
            'image_url' => null,
        ]);

        foreach ($genres as $genre) {
            $this->assertDatabaseHas('book_genres', [
                'book_id' => $book->id,
                'genre_id' => $genre->id,
            ]);
        }
    }

    #[Test]
    public function test_book_store_fails_when_title_is_empty()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genre = Genre::factory()->create();

        $postData = [
            'title' => '',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '説明文',
            'image_url' => null,
            'genres' => [$genre->id],
        ];

        $response = $this->post('/books', $postData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['title']);
    }

    #[Test]
    public function test_book_store_fails_when_isbn_is_duplicate()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genres = Genre::factory()->count(2)->create();

        $book = Book::factory()->create([
            'isbn' => '9784101010014',
        ]);

        $book->genres()->sync($genres->pluck('id')->toArray());

        $postData = [
            'title' => '新しい本',
            'author' => '誰か',
            'isbn' => '9784101010014',
            'published_date' => '2020-01-01',
            'description' => '説明文',
            'image_url' => null,
            'genres' => $genres->pluck('id')->toArray(),
        ];

        $response = $this->post('/books', $postData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['isbn']);
    }

    #[Test]
    public function test_book_store_fails_when_published_date_is_invalid()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genre = Genre::factory()->create();

        $postData = [
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => 'invalid-date',
            'description' => '説明文',
            'image_url' => null,
            'genres' => [$genre->id],
        ];

        $response = $this->post('/books', $postData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['published_date']);
    }

    #[Test]
    public function test_book_store_fails_when_genres_is_empty()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $postData = [
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '説明文',
            'image_url' => null,
            'genres' => [],
        ];

        $response = $this->post('/books', $postData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['genres']);
    }

    #[Test]
    public function test_book_store_fails_when_genres_contains_invalid_id()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $postData = [
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '説明文',
            'image_url' => null,
            'genres' => [99999],
        ];

        $response = $this->post('/books', $postData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['genres.0']);
    }

    #[Test]
    public function book_create_page_shows_isbn_field()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/books/create');

        $response->assertStatus(200)
            ->assertSee('ISBN');
    }

    #[Test]
    public function can_register_book_with_valid_isbn()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $book = Book::latest()->first();
        $genre = Genre::factory()->create();

        $postData = [
            'title' => 'テスト本',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2024-01-01',
            'description' => '説明文',
            'genres' => [$genre->id],
        ];

        $response = $this->post('/books', $postData);

        $response->assertStatus(302);
        $book = Book::orderBy('id', 'desc')->first();
        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('books', [
            'title' => 'テスト本',
            'isbn' => '9781234567890',
        ]);
    }

    #[Test]
    public function isbn_is_required()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genre = Genre::factory()->create();

        $postData = [
            'title' => 'テスト本',
            'author' => 'テスト著者',
            'isbn' => '',
            'published_date' => '2024-01-01',
            'description' => '説明文',
            'genres' => [$genre->id],
        ];

        $response = $this->post('/books', $postData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['isbn']);
    }

    #[Test]
    public function isbn_must_be_13_digits()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genre = Genre::factory()->create();

        $postData = [
            'title' => 'テスト本',
            'author' => 'テスト著者',
            'isbn' => '12345',
            'published_date' => '2024-01-01',
            'description' => '説明文',
            'genres' => [$genre->id],
        ];

        $response = $this->post('/books', $postData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['isbn']);
    }

    #[Test]
    public function isbn_must_be_numeric()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genre = Genre::factory()->create();

        $postData = [
            'title' => 'テスト本',
            'author' => 'テスト著者',
            'isbn' => 'abc1234567890',
            'published_date' => '2024-01-01',
            'description' => '説明文',
            'genres' => [$genre->id],
        ];

        $response = $this->post('/books', $postData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['isbn']);
    }

    #[Test]
    public function isbn_must_be_unique()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genre = Genre::factory()->create();

        Book::factory()->create([
            'isbn' => '9781234567890',
        ]);

        $postData = [
            'title' => '重複テスト本',
            'author' => 'テスト著者',
            'isbn' => '9781234567890',
            'published_date' => '2024-01-01',
            'description' => '説明文',
            'genres' => [$genre->id],
        ];

        $response = $this->post('/books', $postData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['isbn']);
    }

    #[Test]
    public function book_store_fails_when_genres_contains_invalid_id()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $postData = [
            'title' => '吾輩は猫である',
            'author' => '夏目漱石',
            'isbn' => '9784101010014',
            'published_date' => '1905-01-01',
            'description' => '説明文',
            'genres' => [99999],
        ];

        $response = $this->post('/books', $postData);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['genres.0']);
    }
}
