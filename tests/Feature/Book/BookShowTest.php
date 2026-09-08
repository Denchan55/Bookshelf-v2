<?php

namespace Tests\Feature\Book;

use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
class BookShowTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_book_detail_displays_all_information()
{
    // ユーザー（レビュー投稿者）
    $user = User::factory()->create([
        'name' => 'レビュアー太郎',
    ]);

    // ジャンルを複数作成
    $genres = collect([
    Genre::factory()->create(['name' => 'ビジネス']),
    Genre::factory()->create(['name' => '自己啓発']),
]);


    // 書籍を作成
    $book = Book::factory()->create([
        'title' => '人を動かす',
        'author' => 'D・カーネギー',
        'isbn' => '9784422100524',
        'published_date' => '1936-01-01',
        'description' => '人間関係を円滑にする名著。',
    ]);

    // 書籍にジャンルを紐付け
    $book->genres()->sync($genres->pluck('id')->toArray());

    // レビューを作成
    $review = Review::factory()->create([
        'book_id' => $book->id,
        'user_id' => $user->id,
        'rating' => 5,
        'comment' => 'とても参考になりました。',
    ]);

    // 詳細ページへアクセス
    $response = $this->get("/books/{$book->id}");

    // ステータス確認
    $response->assertStatus(200);

    // 書籍の基本情報
    $response->assertSee('人を動かす');
    $response->assertSee('D・カーネギー');
    $response->assertSee('9784422100524');
    $response->assertSee('1936-01-01');
    $response->assertSee('人間関係を円滑にする名著。');

    // ジャンル表示
    foreach ($genres as $genre) {
        $response->assertSee($genre->name);
    }

    // レビュー表示
    $response->assertSee('とても参考になりました。');
    $response->assertSee('レビュアー太郎');
    $response->assertSee('5'); // ★ rating の表示（★5などの表記なら変更）
}
}
