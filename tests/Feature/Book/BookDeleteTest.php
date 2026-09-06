<?php

namespace Tests\Feature\Book;

use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookDeleteTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_book_delete_success()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    // 書籍作成（作成者をログインユーザーにする）
    $book = Book::factory()->create([
        'user_id' => $user->id,
    ]);

    // ジャンル作成 & 紐付け
    $genres = Genre::factory()->count(2)->create();
    $book->genres()->sync($genres->pluck('id'));

    // レビュー作成
    Review::factory()->count(2)->create([
        'book_id' => $book->id,
        'user_id' => $user->id,
    ]);

    // 削除実行
    $response = $this->delete("/books/{$book->id}");

    // 一覧へリダイレクト
    $response->assertRedirect(route('books.index'));

    // 書籍が削除されている
    $this->assertDatabaseMissing('books', [
        'id' => $book->id,
    ]);
}
public function test_book_delete_fails_when_not_logged_in()
{
    $book = Book::factory()->create();

    $response = $this->delete("/books/{$book->id}");

    $response->assertRedirect('/login');
}
public function test_book_delete_fails_when_user_has_no_permission()
{
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $book = Book::factory()->create([
        'user_id' => $owner->id,
    ]);

    $this->actingAs($otherUser);

    $response = $this->delete("/books/{$book->id}");

    $response->assertStatus(403);
}
public function test_book_delete_fails_when_book_not_found()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->delete("/books/999999");

    $response->assertStatus(404);
}
public function test_book_delete_fails_when_already_deleted()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $book = Book::factory()->create([
        'user_id' => $user->id,
    ]);

    $book->delete();

    $response = $this->delete("/books/{$book->id}");

    $response->assertStatus(404);
}


}
