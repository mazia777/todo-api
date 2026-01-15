<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Todo\StoreTodoRequest;
use App\Http\Requests\Todo\UpdateTodoRequest;
use App\Models\Todo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $todos = Todo::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('updated_at')
            ->paginate(15);

        return response()->json([
            'data' => $todos,
        ]);
    }

    public function store(StoreTodoRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $payload['user_id'] = $request->user()->id;

        $todo = Todo::create($payload);

        return response()->json([
            'data' => $todo,
        ], 201);
    }

    public function show(Todo $todo): JsonResponse
    {
        $this->authorize('view', $todo);

        return response()->json([
            'data' => $todo,
        ]);
    }

    public function update(UpdateTodoRequest $request, Todo $todo): JsonResponse
    {
        $this->authorize('update', $todo);

        $todo->fill($request->validated());
        $todo->save();

        return response()->json([
            'data' => $todo,
        ]);
    }

    public function destroy(Todo $todo): Response
    {
        $this->authorize('delete', $todo);

        $todo->delete();

        return response()->noContent();
    }
}
