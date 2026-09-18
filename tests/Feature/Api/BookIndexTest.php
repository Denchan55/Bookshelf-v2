<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookIndexTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_get_books_list()
    {
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'author',
                        'isbn',
                        'published_date',
                        'image_url',
                        'genres' => [
                            '*' => [
                                'id',
                                'name',
                            ],
                        ],
                        'average_rating',
                        'review_count',
                    ],
                ],
                'meta',
                'links',
            ]);
    }

    #[Test]
    public function can_filter_books_by_keyword()
    {
        Book::factory()->create(['title' => 'Laravel入門']);
        Book::factory()->create(['title' => 'PHPの基礎']);

        $response = $this->getJson('/api/v1/books?keyword=Laravel');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Laravel入門');
    }

    #[Test]
    public function can_filter_books_by_genre()
    {
        $genre = Genre::factory()->create();

        $book1 = Book::factory()->create();
        $book1->genres()->attach($genre->id);

        $book2 = Book::factory()->create();

        $response = $this->getJson("/api/v1/books?genre_id={$genre->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $book1->id);
    }

    #[Test]
    public function can_paginate_books()
    {
        Book::factory()->count(20)->create();

        $response = $this->getJson('/api/v1/books?per_page=5');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.per_page', 5);
    }

    #[Test]
    public function books_include_average_rating_and_review_count()
    {
        $book = Book::factory()->create();

        Review::factory()->create(['book_id' => $book->id, 'rating' => 5]);
        Review::factory()->create(['book_id' => $book->id, 'rating' => 3]);

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'average_rating' => 4,
                'review_count' => 2,
            ]);
    }

    #[Test]
    public function books_include_genres()
    {
        $genre = Genre::factory()->create(['name' => '技術書']);
        $book = Book::factory()->create();
        $book->genres()->attach($genre->id);

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => '技術書',
            ]);
    }

    #[Test]
    public function default_per_page_is_20()
    {
        Book::factory()->count(50)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200)
            ->assertJsonCount(20, 'data');
    }

    #[Test]
    public function per_page_50()
    {
        Book::factory()->count(100)->create();

        $response = $this->getJson('/api/v1/books?per_page=50');

        $response->assertStatus(200)
            ->assertJsonCount(50, 'data');
    }

    #[Test]
    public function test_books_index_per_page_max_is_100()
    {
        Book::factory()->count(200)->create();

        $response = $this->getJson('/api/v1/books?per_page=200');

        $response->assertStatus(422);
    }
}
