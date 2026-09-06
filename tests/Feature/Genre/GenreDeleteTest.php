<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Genre;
use App\Models\User;    
use App\Models\Book;

class GenreDeleteTest extends TestCase
{
        use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_genre_delete_success()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $genre = Genre::factory()->create();

    $response = $this->delete("/genres/{$genre->id}");

    $response->assertRedirect('/genres');

    $this->assertDatabaseMissing('genres', [
        'id' => $genre->id,
    ]);
}
public function test_genre_delete_fails_when_books_are_attached()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $genre = Genre::factory()->create();
    $book = Book::factory()->create();

    $genre->books()->attach($book->id);

    $response = $this->delete("/genres/{$genre->id}");

    $response->assertRedirect('/genres');
    $response->assertSessionHas('error');

    $this->assertDatabaseHas('genres', [
        'id' => $genre->id,
    ]);
}
public function test_genre_delete_redirects_when_not_logged_in()
{
    $genre = Genre::factory()->create();

    $response = $this->delete("/genres/{$genre->id}");

    $response->assertRedirect('/login');
}

}
