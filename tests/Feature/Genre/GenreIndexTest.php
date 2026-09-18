<?php

namespace Tests\Feature\Genre;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GenreIndexTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function genre_index_displays_all_genres()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $genres = Genre::factory()->count(3)->create();

        $response = $this->get('/genres');

        $response->assertStatus(200);

        foreach ($genres as $genre) {
            $response->assertSee($genre->name);
        }
    }

    #[Test]
    public function genre_index_displays_empty_message_when_no_genres_exist()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/genres');

        $response->assertStatus(200);
        $response->assertSee('ジャンルが登録されていません。');
    }

    #[Test]
    public function genre_index_redirects_when_not_logged_in()
    {
        $response = $this->get('/genres');

        $response->assertRedirect('/login');
    }
}
