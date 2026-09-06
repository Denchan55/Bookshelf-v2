<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_success()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Fortify の登録後は / にリダイレクト
        $response->assertRedirect('/books');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_register_fails_with_duplicate_email()
    {
        User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->post('/register', [
            'name' => 'Another User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_register_fails_with_invalid_password()
    {
        $response = $this->post('/register', [
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    public function test_logged_in_user_cannot_access_register_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/register');

        // Fortify はログイン済みで register にアクセスすると / に飛ばす
        $response->assertRedirect('/books');
    }
}
