<?php

namespace Tests\Feature\Genre;

use Tests\TestCase;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;


class GenreIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */


    public function genre_index_displays_all_genres()
    {
$user = User::factory()->create();
    $this->actingAs($user);

        // ジャンルを複数作成
        $genres = Genre::factory()->count(3)->create();

        // 一覧ページへアクセス
        $response = $this->get('/genres');

        // ステータス確認
        $response->assertStatus(200);

        // 各ジャンル名が表示されているか
        foreach ($genres as $genre) {
            $response->assertSee($genre->name);
        }
    }

    /** @test */
    public function genre_index_displays_empty_message_when_no_genres_exist()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/genres');

    $response->assertStatus(200);
    $response->assertSee('ジャンルが登録されていません。');
}
    public function genre_index_redirects_when_not_logged_in()
{
    $response = $this->get('/genres');

    $response->assertRedirect('/login');
}
}
