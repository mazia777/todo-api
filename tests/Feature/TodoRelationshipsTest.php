<?php

namespace Tests\Feature;

use App\Models\Todo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_many_todos_and_todo_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $todo = Todo::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->todos->contains($todo));
        $this->assertTrue($todo->user->is($user));
    }
}
