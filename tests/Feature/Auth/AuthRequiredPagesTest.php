<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthRequiredPagesTest extends TestCase
{
    use RefreshDatabase;

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

    #[Test]
    public function test_all_protected_pages_redirect_to_login_when_not_authenticated()
    {
        $user = User::factory()->create();
        foreach ($this->protectedPages($user) as $page) {
            $response = $this->get($page);
            $response->assertRedirect('/login');
        }
    }

    #[Test]
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
