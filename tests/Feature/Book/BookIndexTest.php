<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookIndexTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_book_index_displays_books_when_not_logged_in()
    {

        $books = Book::factory()->count(3)->create();

        $response = $this->get('/books');

        $response->assertStatus(200);

        foreach ($books as $book) {
            $response->assertSee($book->title);
        }

        $response->assertSee('ログイン');
        $response->assertSee('新規登録');
    }

    #[Test]
    public function test_book_index_displays_books_when_logged_in()
    {

        $user = User::factory()->create();
        $this->actingAs($user);

        $books = Book::factory()->count(3)->create();

        $response = $this->get('/books');

        $response->assertStatus(200);

        foreach ($books as $book) {
            $response->assertSee($book->title);
        }

        $response->assertSee($user->name);
    }

    #[Test]
    public function web_book_index_can_search_by_keyword()
    {
        $book1 = Book::factory()->create(['title' => 'Laravel入門']);
        $book2 = Book::factory()->create(['title' => 'JavaScript基礎']);

        $response = $this->get('/books?keyword=Laravel');

        $response->assertStatus(200)
            ->assertSee('Laravel入門')
            ->assertDontSeeText('JavaScript基礎');
    }

    #[Test]
    public function web_book_index_can_filter_by_genre()
    {
        $genre = Genre::factory()->create();

        $book1 = Book::factory()->create(['title' => 'Laravel実践']);
        $book1->genres()->attach($genre->id);

        $book2 = Book::factory()->create(['title' => 'JavaScript基礎']);

        $response = $this->get("/books?genre={$genre->id}");

        $response->assertStatus(200)
            ->assertSee('Laravel実践')
            ->assertDontSeeText('JavaScript基礎');
    }

    #[Test]
    public function web_book_index_can_search_by_keyword_and_genre()
    {
        $genre = Genre::factory()->create();

        $book1 = Book::factory()->create(['title' => 'Laravel実践']);
        $book1->genres()->attach($genre->id);

        $book2 = Book::factory()->create(['title' => 'Laravel入門']);

        $response = $this->get("/books?keyword=Laravel&genre={$genre->id}");

        $response->assertStatus(200)
            ->assertSee('Laravel実践')
            ->assertDontSeeText('Laravel入門');
    }

    #[Test]
    public function web_book_index_keyword_no_match_shows_empty_message()
    {
        Book::factory()->create(['title' => 'Laravel入門']);

        $response = $this->get('/books?keyword=Python');

        $response->assertStatus(200)
            ->assertSee('書籍が見つかりませんでした。');
    }

    #[Test]
    public function web_book_index_can_sort_by_title()
    {
        $book1 = Book::factory()->create(['title' => '7つの習慣']);
        $book2 = Book::factory()->create(['title' => 'Clean Code']);
        $book3 = Book::factory()->create(['title' => 'コンテナ技術入門']);
        $book4 = Book::factory()->create(['title' => '坊っちゃん']);
        $book5 = Book::factory()->create(['title' => '嫌われる勇気']);

        $response = $this->get('/books?sort=title');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            '7つの習慣',
            'Clean Code',
            'コンテナ技術入門',
            '坊っちゃん',
            '嫌われる勇気',
        ]);
    }

    #[Test]
    public function web_book_index_can_sort_by_rating()
    {
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        $book3 = Book::factory()->create();

        Review::factory()->count(5)->create(['book_id' => $book1->id, 'rating' => 5]);
        Review::factory()->count(3)->create(['book_id' => $book2->id, 'rating' => 3]);
        Review::factory()->count(1)->create(['book_id' => $book3->id, 'rating' => 1]);

        $response = $this->get('/books?sort=rating');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $book1->title,
            $book2->title,
            $book3->title,
        ]);
    }

    #[Test]
    public function web_book_index_invalid_sort_value_falls_back_to_default()
    {
        $book1 = Book::factory()->create(['published_date' => '2024-01-01']);
        $book2 = Book::factory()->create(['published_date' => '2024-02-01']);

        $response = $this->get('/books?sort=hoge');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $book2->title,
            $book1->title,
        ]);
    }

    #[Test]
    public function web_book_index_empty_sort_value_falls_back_to_default()
    {
        $book1 = Book::factory()->create(['published_date' => '2024-01-01']);
        $book2 = Book::factory()->create(['published_date' => '2024-02-01']);

        $response = $this->get('/books?sort=');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $book2->title,
            $book1->title,
        ]);
    }

    #[Test]
    public function web_book_index_without_sort_param_uses_default()
    {
        $book1 = Book::factory()->create(['published_date' => '2024-01-01']);
        $book2 = Book::factory()->create(['published_date' => '2024-02-01']);

        $response = $this->get('/books');

        $response->assertStatus(200);

        $response->assertSeeInOrder([
            $book2->title,
            $book1->title,
        ]);
    }
}
