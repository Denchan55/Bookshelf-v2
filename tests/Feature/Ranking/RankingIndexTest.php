<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RankingIndexTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_ranking_displays_books_in_correct_order_by_average_rating()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $bookA = Book::factory()->create(['title' => '本A']);
        $bookB = Book::factory()->create(['title' => '本B']);
        $bookC = Book::factory()->create(['title' => '本C']);

        Review::factory()->create(['book_id' => $bookA->id, 'rating' => 5]);
        Review::factory()->create(['book_id' => $bookB->id, 'rating' => 3]);
        Review::factory()->create(['book_id' => $bookC->id, 'rating' => 1]);

        $response = $this->get('/ranking');

        $response->assertStatus(200);

        $response->assertSeeInOrder(['本A', '本B', '本C']);
    }

    #[Test]
    public function test_ranking_does_not_display_books_without_reviews()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book = Book::factory()->create(['title' => 'レビューなし本']);

        $response = $this->get('/ranking');

        $response->assertStatus(200);

        $response->assertDontSee('レビューなし本');
    }

    #[Test]
    public function test_ranking_displays_only_top_10_books()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $books = Book::factory()->count(11)->create();

        foreach ($books as $index => $book) {
            Review::factory()->create([
                'book_id' => $book->id,
                'rating' => 10 - $index,
            ]);
        }

        $response = $this->get('/ranking');

        $response->assertStatus(200);

        $response->assertDontSee($books[10]->title);
    }
}
