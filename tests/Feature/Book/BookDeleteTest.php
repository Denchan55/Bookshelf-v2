<?php

namespace Tests\Feature\Book;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookDeleteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_book_delete_success()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $genres = Genre::factory()->count(2)->create();
        $book->genres()->sync($genres->pluck('id'));

        Review::factory()->count(2)->create([
            'book_id' => $book->id,
            'user_id' => $user->id,
        ]);

        $response = $this->delete("/books/{$book->id}");

        $response->assertRedirect(route('books.index'));

        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
    }

    #[Test]
    public function test_book_delete_fails_when_not_logged_in()
    {
        $book = Book::factory()->create();

        $response = $this->delete("/books/{$book->id}");

        $response->assertRedirect('/login');
    }

    #[Test]
    public function test_book_delete_fails_when_user_has_no_permission()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $book = Book::factory()->create([
            'user_id' => $owner->id,
        ]);

        $this->actingAs($otherUser);

        $response = $this->delete("/books/{$book->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function test_book_delete_fails_when_book_not_found()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->delete('/books/999999');

        $response->assertStatus(404);
    }

    #[Test]
    public function test_book_delete_fails_when_already_deleted()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book = Book::factory()->create([
            'user_id' => $user->id,
        ]);

        $book->delete();

        $response = $this->delete("/books/{$book->id}");

        $response->assertStatus(404);
    }
}
