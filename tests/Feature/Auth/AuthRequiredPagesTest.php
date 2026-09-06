<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;

class AuthRequiredPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 認証必須ページ一覧（仕様書準拠）
     */
private function protectedPages(User $user)
{
    $book = Book::factory()->create(['user_id' => $user->id]);
    $review = Review::factory()->create([
        'book_id' => $book->id,
        'user_id' => $user->id,
    ]);

    return [
        '/books/create',
        "/books/{$book->id}/edit",
        "/reviews/{$review->id}/edit",
        '/favorites',
    ];
}

    public function test_all_protected_pages_redirect_to_login_when_not_authenticated()
    {
        $user = User::factory()->create();
        foreach ($this->protectedPages($user) as $page) {
            $response = $this->get($page);
            $response->assertRedirect('/login');
        }
    }

    public function test_all_protected_pages_are_accessible_when_authenticated()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        foreach ($this->protectedPages($user) as $page) {
            $response = $this->get($page);
            $response->assertStatus(200);
        }
    }
}
