<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GenreUpdateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_genre_update_success()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genre = Genre::factory()->create(['name' => '小説']);

        $response = $this->put("/genres/{$genre->id}", [
            'name' => '文学',
        ]);

        $response->assertRedirect("/genres/{$genre->id}");

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '文学',
        ]);
    }

    #[Test]
    public function test_genre_update_fails_when_name_is_empty()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genre = Genre::factory()->create(['name' => '小説']);

        $response = $this->from("/genres/{$genre->id}/edit")
            ->put("/genres/{$genre->id}", [
                'name' => '',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function test_genre_update_fails_when_name_is_duplicate()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genreA = Genre::factory()->create(['name' => '小説']);
        $genreB = Genre::factory()->create(['name' => '漫画']);

        $response = $this->from("/genres/{$genreB->id}/edit")
            ->put("/genres/{$genreB->id}", [
                'name' => '小説',
            ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function test_genre_update_fails_when_name_is_too_long()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genre = Genre::factory()->create(['name' => '小説']);

        $response = $this->put("/genres/{$genre->id}", [
            'name' => str_repeat('あ', 256),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function test_genre_update_fails_when_name_is_not_string()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genre = Genre::factory()->create(['name' => '小説']);

        $response = $this->put("/genres/{$genre->id}", [
            'name' => 12345,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function test_genre_update_redirects_when_not_logged_in()
    {
        $genre = Genre::factory()->create(['name' => '小説']);

        $response = $this->put("/genres/{$genre->id}", [
            'name' => '文学',
        ]);

        $response->assertRedirect('/login');
    }
}
