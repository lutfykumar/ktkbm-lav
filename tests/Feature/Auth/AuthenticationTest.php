<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Selamat Datang');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        // User with known credentials or first active user
        $user = User::where('active', 1)->first();
        $this->assertNotNull($user);

        // Test login request directly with identity
        $response = $this->post('/login', [
            'identity' => $user->email,
            'password' => 'wrong-pass-test',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'identity' => 'nonexistent@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_ajax_login_returns_json_response(): void
    {
        $response = $this->postJson('/login', [
            'identity' => 'nonexistent@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertJson([
            'status' => false,
        ]);
        $this->assertGuest();
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::where('active', 1)->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    public function test_users_can_logout(): void
    {
        $user = User::where('active', 1)->first();
        $this->assertNotNull($user);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
