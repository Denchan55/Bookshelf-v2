<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\readingPlan;
use App\Models\review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MyReadingReportTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_displays_my_reading_report_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/reports');

        $response->assertStatus(200);
        $response->assertSee('マイ読書レポート');
        $response->assertSee('基本統計');
        $response->assertSee('評価分布');
        $response->assertSee('高評価書籍 TOP5');
        $response->assertSee('ジャンル別評価傾向 TOP5');
    }

    #[Test]
    public function it_displays_correct_stats_based_on_user_reviews()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genre = Genre::factory()->create(['name' => '技術書']);

        $book1 = Book::factory()->create();
        $book1->genres()->sync([$genre->id]);

        $book2 = Book::factory()->create();
        $book2->genres()->sync([$genre->id]);

        readingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'status' => 'completed',
        ]);

        readingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'status' => 'completed',
        ]);

        review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 5,
        ]);

        review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 3,
        ]);

        $response = $this->get('/reports');

        $response->assertSee('2');
        $response->assertSee('2');
        $response->assertSee('4.0');

        $response->assertSee('★★★★★');
        $response->assertSee('★★★');

        $response->assertSee($book1->title);

        $response->assertSee('技術書');
        $response->assertSee('4.0');
    }
}
