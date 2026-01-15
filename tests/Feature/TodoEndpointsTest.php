<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_todo_with_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->postJson('/api/todos', [
                'title' => 'Write docs',
                'description' => 'Finish API docs',
                'completed' => false,
            ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'Write docs');

        $this->assertDatabaseHas('todos', [
            'user_id' => $user->id,
            'title' => 'Write docs',
        ]);
    }

    public function test_user_cannot_view_other_users_todo(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $otherUser->id]);
        $token = $user->createToken('auth_token');

        $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->getJson('/api/todos/'.$todo->id)
            ->assertForbidden();
    }

    public function test_index_returns_only_own_todos(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Todo::factory()->count(2)->create(['user_id' => $user->id]);
        $otherTodo = Todo::factory()->create(['user_id' => $otherUser->id]);
        $token = $user->createToken('auth_token');

        $response = $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->getJson('/api/todos');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data.data')
            ->assertJsonMissing(['id' => $otherTodo->id]);
    }
}
