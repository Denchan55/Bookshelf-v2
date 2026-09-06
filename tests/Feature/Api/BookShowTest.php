<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;

class BookShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_book_detail()
    {
        $book = Book::factory()->create();

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
                 ->assertJson([
                    'data' => [
                     'id' => $book->id,
                     'title' => $book->title,
                     'author' => $book->author,
                     'isbn' => $book->isbn,
                    ]
                 ])
                ->assertJsonStructure([
    'data' => [
        'id',
        'title',
        'author',
        'isbn',
        'published_at',
        'image_url',
        'genres',
        'average_rating',
        'review_count',
    ]
]);

    }

    /** @test */
    public function book_detail_includes_genres()
    {
        $genre = Genre::factory()->create(['name' => '技術書']);
        $book = Book::factory()->create();
        $book->genres()->attach($genre->id);

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
                ->assertJsonPath('data.genres.0.name', '技術書');
    }

    /** @test */
    public function book_detail_includes_average_rating_and_review_count()
    {
        $book = Book::factory()->create();

        Review::factory()->create(['book_id' => $book->id, 'rating' => 5]);
        Review::factory()->create(['book_id' => $book->id, 'rating' => 3]);

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200)
                ->assertJsonPath('data.average_rating', "4.0")
                ->assertJsonPath('data.review_count', 2);
    }

    /** @test */
    public function returns_404_if_book_not_found()
    {
        $response = $this->getJson('/api/v1/books/999999');

        $response->assertStatus(404);
    }
}
