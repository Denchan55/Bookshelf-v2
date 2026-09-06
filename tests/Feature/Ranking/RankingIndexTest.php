<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Review;

class RankingIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_ranking_displays_books_in_correct_order_by_average_rating()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 書籍を3冊作成
        $bookA = Book::factory()->create(['title' => '本A']);
        $bookB = Book::factory()->create(['title' => '本B']);
        $bookC = Book::factory()->create(['title' => '本C']);

        // 平均評価を操作
        Review::factory()->create(['book_id' => $bookA->id, 'rating' => 5]);
        Review::factory()->create(['book_id' => $bookB->id, 'rating' => 3]);
        Review::factory()->create(['book_id' => $bookC->id, 'rating' => 1]);

        $response = $this->get('/ranking');

        $response->assertStatus(200);

        // 平均評価が高い順に表示されること
        $response->assertSeeInOrder(['本A', '本B', '本C']);
    }

    public function test_ranking_does_not_display_books_without_reviews()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book = Book::factory()->create(['title' => 'レビューなし本']);

        $response = $this->get('/ranking');

        $response->assertStatus(200);

        // レビューがない書籍は表示されない
        $response->assertDontSee('レビューなし本');
    }

    public function test_ranking_displays_only_top_10_books()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 11冊作成して全部にレビューを付ける
        $books = Book::factory()->count(11)->create();

        foreach ($books as $index => $book) {
    Review::factory()->create([
        'book_id' => $book->id,
        'rating' => 10 - $index, // 10,9,8,7,6,5,4,3,2,1,0
    ]);
}


        $response = $this->get('/ranking');

        $response->assertStatus(200);

        // 11冊目は表示されない（Top10のみ）
        $response->assertDontSee($books[10]->title);
    }

}
