<?php

namespace Tests\Feature\Book;

use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookIndexTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_book_index_displays_books_when_not_logged_in()
{

    $books = Book::factory()->count(3)->create();

    $response = $this->get('/books');

    $response->assertStatus(200);

    foreach ($books as $book) {
        $response->assertSee($book->title);
    }

    // 未ログイン時はログインボタンと新規登録ボタンが表示される
    $response->assertSee('ログイン');
    $response->assertSee('新規登録');
}

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

    // ログイン時はユーザー名が表示される
    $response->assertSee($user->name);
}
}
