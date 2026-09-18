<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FavoriteToggleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_favorite_toggle_adds_favorite()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book = Book::factory()->create();

        $response = $this->post("/favorites/{$book->id}/toggle");

        $response->assertRedirect();

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    #[Test]
    public function test_favorite_toggle_removes_favorite()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book = Book::factory()->create();

        $user->favoriteBooks()->attach($book->id);

        $response = $this->post("/favorites/{$book->id}/toggle");

        $response->assertRedirect();

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    #[Test]
    public function test_favorite_toggle_redirects_when_not_logged_in()
    {
        $book = Book::factory()->create();

        $response = $this->post("/favorites/{$book->id}/toggle");

        $response->assertRedirect('/login');
    }

    #[Test]
    public function test_favorite_toggle_returns_404_for_nonexistent_book()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/favorites/999999/toggle');

        $response->assertStatus(404);
    }
}
