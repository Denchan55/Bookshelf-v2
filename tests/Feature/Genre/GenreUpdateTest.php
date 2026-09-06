<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GenreUpdateTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_genre_update_redirects_when_not_logged_in()
    {
        $genre = Genre::factory()->create(['name' => '小説']);

        $response = $this->put("/genres/{$genre->id}", [
            'name' => '文学',
        ]);

        $response->assertRedirect('/login');
    }
}
