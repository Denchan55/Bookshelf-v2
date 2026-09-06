<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

public function test_logout_success()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post('/logout');

    // Fortify はログアウト後は / に戻る
    $response->assertRedirect('/');

    $this->assertGuest();
}

public function test_logout_when_not_logged_in()
{
    $response = $this->post('/logout');

    // Fortify は未ログインで logout を叩くと /login に飛ばす
    $response->assertRedirect('/login');

    $this->assertGuest();
}

}
