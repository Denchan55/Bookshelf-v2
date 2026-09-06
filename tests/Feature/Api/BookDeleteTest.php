<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;

class BookDeleteTest extends TestCase
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
            'title' => '削除対象',
            'author' => '著者',
            'isbn' => '9781111111111',
            'published_at' => '2020-01-01',
            'description' => '説明',
            'image_url' => 'https://example.com/img.jpg',
            'user_id' => auth()->id(),
        ]);

        $genre = Genre::factory()->create();
        $book->genres()->sync([$genre->id]);

        return $book;
    }

    /** @test */
    public function can_delete_book()
    {
        $book = $this->createBook();

        $response = $this->deleteJson("/api/v1/books/{$book->id}");

        // ステータスコード（あなたの実装に合わせて 200 or 204）
        $response->assertStatus(204);

        // DBから削除されていること
        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);

        // pivotも削除されていること
        $this->assertDatabaseMissing('book_genre', [
            'book_id' => $book->id,
        ]);
    }
/** @test */
public function cannot_delete_nonexistent_book()
{
    // 存在しないIDを削除
    $response = $this->deleteJson('/api/v1/books/99999');

    $response->assertStatus(404);
}
/** @test */
public function cannot_delete_book_owned_by_another_user()
{
    // 他人のユーザー
    $otherUser = User::factory()->create();

    // 他人の本
    $book = Book::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    // 自分は actingAs でログイン済み
    $response = $this->deleteJson("/api/v1/books/{$book->id}");

    $response->assertStatus(403);
}
/** @test */
public function cannot_delete_book_twice()
{
    $book = Book::factory()->create([
        'user_id' => auth()->id(),
    ]);

    // 1回目（成功）
    $this->deleteJson("/api/v1/books/{$book->id}")
         ->assertStatus(204);

    // 2回目（存在しないので404）
    $this->deleteJson("/api/v1/books/{$book->id}")
         ->assertStatus(404);
}


}
