<?php

namespace Tests\Feature;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GenreStoreTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_genre_store_creates_new_genre()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/genres', [
            'name' => '小説',
        ]);

        $response->assertRedirect('/genres');

        $this->assertDatabaseHas('genres', [
            'name' => '小説',
        ]);
    }

    #[Test]
    public function test_genre_store_fails_when_name_is_empty()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/genres', [
            'name' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function test_genre_store_fails_when_name_is_duplicate()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Genre::factory()->create(['name' => '小説']);

        $response = $this->from('/genres/create')->post('/genres', [
            'name' => '小説',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function test_genre_store_fails_when_name_is_too_long()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/genres', [
            'name' => str_repeat('あ', 256),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function test_genre_store_fails_when_name_is_not_string()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/genres', [
            'name' => 12345,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function test_genre_store_redirects_when_not_logged_in()
    {
        $response = $this->post('/genres', [
            'name' => '小説',
        ]);

        $response->assertRedirect('/login');
    }
}
