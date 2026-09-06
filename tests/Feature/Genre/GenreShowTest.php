<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Genre;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GenreShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_genre_show_displays_genre_details()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genre = Genre::factory()->create(['name' => '小説']);

        $response = $this->get("/genres/{$genre->id}");

        $response->assertStatus(200);
        $response->assertSee('小説');
    }

    public function test_genre_show_displays_related_books()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genre = Genre::factory()->create(['name' => '小説']);

        // 書籍を複数紐づける
        $books = Book::factory()->count(3)->create();
        $genre->books()->attach($books->pluck('id'));

        $response = $this->get("/genres/{$genre->id}");

        $response->assertStatus(200);

        foreach ($books as $book) {
            $response->assertSee($book->title);
        }
    }

    public function test_genre_show_returns_404_for_nonexistent_genre()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/genres/999999');

        $response->assertStatus(404);
    }

    public function test_genre_show_redirects_when_not_logged_in()
    {
        $genre = Genre::factory()->create(['name' => '小説']);

        $response = $this->get("/genres/{$genre->id}");

        $response->assertRedirect('/login');
    }
}

