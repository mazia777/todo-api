<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_returns_token(): void
    {
        $payload = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response
            ->assertStatus(201)
            ->assertJsonStructure([
                'data' => ['token', 'token_type', 'user'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
        ]);
    }

    public function test_login_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['token', 'token_type', 'user'],
            ]);
    }

    public function test_logout_requires_auth_and_revokes_token(): void
    {
        $this->postJson('/api/auth/logout')->assertUnauthorized();

        $user = User::factory()->create();
        $token = $user->createToken('auth_token');

        $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->postJson('/api/auth/logout')
            ->assertNoContent();

        $this->assertSame(0, $user->tokens()->count());
    }
}
