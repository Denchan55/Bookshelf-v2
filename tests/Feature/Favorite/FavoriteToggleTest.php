<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;

class FavoriteToggleTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_favorite_toggle_removes_favorite()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book = Book::factory()->create();

        // 事前にお気に入り登録
        $user->favoriteBooks()->attach($book->id);

        $response = $this->post("/favorites/{$book->id}/toggle");

        $response->assertRedirect();

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    public function test_favorite_toggle_redirects_when_not_logged_in()
    {
        $book = Book::factory()->create();

        $response = $this->post("/favorites/{$book->id}/toggle");

        $response->assertRedirect('/login');
    }

    public function test_favorite_toggle_returns_404_for_nonexistent_book()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post("/favorites/999999/toggle");

        $response->assertStatus(404);
    }
}
