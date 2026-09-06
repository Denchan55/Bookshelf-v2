<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_success()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/books');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_fails_with_nonexistent_email()
    {
        $response = $this->post('/login', [
            'email' => 'notfound@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_logged_in_user_cannot_access_login_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/login');

        $response->assertRedirect('/books');
    }
}

