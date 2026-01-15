<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TodoEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_requires_auth(): void
    {
        $this->getJson('/api/todos')->assertUnauthorized();
    }

    public function test_index_returns_only_own_todos(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        Todo::factory()->count(2)->create(['user_id' => $user->id]);
        $otherTodo = Todo::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/todos');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data.data')
            ->assertJsonMissing(['id' => $otherTodo->id]);
    }

    public function test_store_requires_auth(): void
    {
        $this->postJson('/api/todos', [
            'title' => 'Write docs',
        ])->assertUnauthorized();
    }

    public function test_store_success_ignores_user_id_from_payload(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/todos', [
            'title' => 'Write docs',
            'description' => 'Finish API docs',
            'completed' => false,
            'user_id' => $otherUser->id,
        ]);

        $response
            ->assertStatus(201)
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.title', 'Write docs');

        $this->assertDatabaseHas('todos', [
            'user_id' => $user->id,
            'title' => 'Write docs',
        ]);
    }

    public function test_store_validation_fails(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/todos', [
            'completed' => 'not-a-bool',
            'due_at' => 'not-a-date',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'completed', 'due_at']);

        $this->postJson('/api/todos', [
            'title' => str_repeat('a', 121),
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_show_success_for_owner(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $this->getJson('/api/todos/'.$todo->id)
            ->assertOk()
            ->assertJsonPath('data.id', $todo->id);
    }

    public function test_show_forbidden_for_other_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($user);

        $this->getJson('/api/todos/'.$todo->id)->assertForbidden();
    }

    public function test_update_success_for_owner(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/todos/'.$todo->id, [
            'title' => 'Updated title',
            'description' => 'Updated description',
            'completed' => true,
            'due_at' => '2030-01-01 10:00:00',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated title')
            ->assertJsonPath('data.completed', true);

        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'title' => 'Updated title',
            'completed' => true,
        ]);
    }

    public function test_update_forbidden_for_other_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($user);

        $this->putJson('/api/todos/'.$todo->id, [
            'title' => 'Updated title',
        ])->assertForbidden();
    }

    public function test_delete_success_for_owner(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $this->deleteJson('/api/todos/'.$todo->id)->assertNoContent();

        $this->assertDatabaseMissing('todos', [
            'id' => $todo->id,
        ]);
    }

    public function test_delete_forbidden_for_other_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $otherUser->id]);

        Sanctum::actingAs($user);

        $this->deleteJson('/api/todos/'.$todo->id)->assertForbidden();
    }

    public function test_not_found_returns_404_for_show_update_delete(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/todos/999999')->assertNotFound();
        $this->putJson('/api/todos/999999', ['title' => 'Nope'])->assertNotFound();
        $this->deleteJson('/api/todos/999999')->assertNotFound();
    }
}
