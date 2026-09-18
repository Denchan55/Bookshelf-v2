<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FavoriteIndexTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_favorite_index_displays_favorites()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $book1 = Book::factory()->create(['title' => '本A']);
        $book2 = Book::factory()->create(['title' => '本B']);

        $user->favoriteBooks()->attach($book1->id);
        $user->favoriteBooks()->attach($book2->id);

        $response = $this->get('/favorites');

        $response->assertStatus(200);

        $response->assertSee('本A');
        $response->assertSee('本B');
    }

    #[Test]
    public function test_favorite_index_displays_empty_message_when_no_favorites()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/favorites');

        $response->assertStatus(200);
        $response->assertSee('お気に入りに登録された書籍はありません。');
    }

    #[Test]
    public function test_favorite_index_redirects_when_not_logged_in()
    {
        $response = $this->get('/favorites');

        $response->assertRedirect('/login');
    }
}
