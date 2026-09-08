<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Genre;

class BookAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_access_edit_page()
    {
        $owner = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($owner);

        $response = $this->get("/books/{$book->id}/edit");
        $response->assertStatus(200);
    }

    public function test_non_owner_cannot_access_edit_page()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other);

        $response = $this->get("/books/{$book->id}/edit");
        $response->assertStatus(403);
    }

    public function test_owner_can_update_book()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genres = Genre::factory()->count(2)->create();

        $book = Book::factory()->create(['user_id' => $user->id]);
        $book->genres()->attach($genres->pluck('id')->toArray());

        $response = $this->put("/books/{$book->id}", [
            'title' => '新しいタイトル',
            'author' => '新しい著者名',
            'genres' => $genres->pluck('id')->toArray(),
            'isbn' => '1234567890123',
            'published_date' => '2024-01-01',
            'image_url' => null,
        ]);

        $response->assertRedirect("/books/{$book->id}");
    }

    public function test_non_owner_cannot_update_book()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $genres = Genre::factory()->count(2)->create();

        $book = Book::factory()->create(['user_id' => $owner->id]);
        $book->genres()->attach($genres->pluck('id')->toArray());

        $this->actingAs($other);

        $response = $this->put("/books/{$book->id}", [
            'title' => 'Updated Title',
            'author' => 'Updated Author',
            'genres' => $genres->pluck('id')->toArray(),
            'isbn' => '1234567890123',
            'published_date' => '2024-01-01',
        ]);

        $response->assertStatus(403);
    }

    public function test_owner_can_delete_book()
    {
        $owner = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($owner);

        $response = $this->delete("/books/{$book->id}");
        $response->assertRedirect('/books');
    }

    public function test_non_owner_cannot_delete_book()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other);

        $response = $this->delete("/books/{$book->id}");
        $response->assertStatus(403);
    }
}
