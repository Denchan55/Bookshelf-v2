<?php

namespace Tests\Feature\Book;

use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookStoreTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_book_store_creates_new_book()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $genres = Genre::factory()->count(2)->create();

    $postData = [
        'title' => '吾輩は猫である',
        'author' => '夏目漱石',
        'isbn' => '9784101010014',
        'published_at' => '1905-01-01',
        'description' => 'テスト用の説明文です。',
        'image_url' => null, // ★ nullable|url を満たすため必須
        'genres' => $genres->pluck('id')->toArray(), // ★複数ジャンル
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
        $this->assertDatabaseHas('book_genre', [
            'book_id' => $book->id,
            'genre_id' => $genre->id,
        ]);
    }
}

public function test_book_store_fails_when_title_is_empty()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $genre = Genre::factory()->create();

    $postData = [
        'title' => '',
        'author' => '夏目漱石',
        'isbn' => '9784101010014',
        'published_at' => '1905-01-01',
        'description' => '説明文',
        'image_url' => null, 
        'genres' => [$genre->id],
    ];

    $response = $this->post('/books', $postData);

    // 異常系：バリデーションエラー → 422
    $response->assertStatus(302); // Laravelは422ではなくリダイレクト（302）になる
    $response->assertSessionHasErrors(['title']);
}
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
        'isbn' => '9784101010014', // ★重複
        'published_at' => '2020-01-01',
        'description' => '説明文',
        'image_url' => null,
        'genres' => $genres->pluck('id')->toArray(),
    ];

    $response = $this->post('/books', $postData);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['isbn']);
}

public function test_book_store_fails_when_published_at_is_invalid()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $genre = Genre::factory()->create();

    $postData = [
        'title' => '吾輩は猫である',
        'author' => '夏目漱石',
        'isbn' => '9784101010014',
        'published_at' => 'invalid-date',
        'description' => '説明文',
        'image_url' => null, 
        'genres' => [$genre->id],
    ];

    $response = $this->post('/books', $postData);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['published_at']);
}
public function test_book_store_fails_when_genres_is_empty()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $postData = [
        'title' => '吾輩は猫である',
        'author' => '夏目漱石',
        'isbn' => '9784101010014',
        'published_at' => '1905-01-01',
        'description' => '説明文',
        'image_url' => null, 
        'genres' => [], // ★空
    ];

    $response = $this->post('/books', $postData);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['genres']);
}
public function test_book_store_fails_when_genres_contains_invalid_id()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $postData = [
        'title' => '吾輩は猫である',
        'author' => '夏目漱石',
        'isbn' => '9784101010014',
        'published_at' => '1905-01-01',
        'description' => '説明文',
        'image_url' => null, 
        'genres' => [99999], // ★存在しないID
    ];

    $response = $this->post('/books', $postData);

    $response->assertStatus(302);
    $response->assertSessionHasErrors(['genres.0']);
}


}
