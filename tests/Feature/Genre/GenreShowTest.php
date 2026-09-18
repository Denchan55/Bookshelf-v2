<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GenreShowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_genre_show_displays_genre_details()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genre = Genre::factory()->create(['name' => '小説']);

        $response = $this->get("/genres/{$genre->id}");

        $response->assertStatus(200);
        $response->assertSee('小説');
    }

    #[Test]
    public function test_genre_show_displays_related_books()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genre = Genre::factory()->create(['name' => '小説']);

        $books = Book::factory()->count(3)->create();
        $genre->books()->attach($books->pluck('id'));

        $response = $this->get("/genres/{$genre->id}");

        $response->assertStatus(200);

        foreach ($books as $book) {
            $response->assertSee($book->title);
        }
    }

    #[Test]
    public function test_genre_show_returns_404_for_nonexistent_genre()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/genres/999999');

        $response->assertStatus(404);
    }

    #[Test]
    public function test_genre_show_redirects_when_not_logged_in()
    {
        $genre = Genre::factory()->create(['name' => '小説']);

        $response = $this->get("/genres/{$genre->id}");

        $response->assertRedirect('/login');
    }
}
