<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Task;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->isAdmin()
            ? Task::query()->with('user:id,name,email')
            : $request->user()->tasks();

        $query->orderBy('due_date');

        if ($request->user()->isAdmin() && $request->filled('assigned_to')) {
            $query->where('user_id', $request->query('assigned_to'));
        }

        match ($request->query('status')) {
            'pending' => $query->pending(),
            'completed' => $query->completed(),
            'overdue' => $query->overdue(),
            default => null,
        };

        return response()->json($query->get());
    }

    public function store(StoreTaskRequest $request)
    {
        $task = Task::create($request->validated())->fresh();

        return response()->json($task, 201);
    }

    public function show(Request $request, Task $task)
    {
        $this->authorizeOwner($request, $task);

        return response()->json($task);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorizeOwner($request, $task);

        $data = $request->validated();

        if (($data['status'] ?? null) === 'completed' && $task->status !== 'completed') {
            $data['completed_at'] = now();
        } elseif (($data['status'] ?? null) === 'pending') {
            $data['completed_at'] = null;
        }

        $task->update($data);

        return response()->json($task);
    }

    public function toggle(Request $request, Task $task)
    {
        $this->authorizeOwner($request, $task);

        if ($task->status === 'completed') {
            $task->update(['status' => 'pending', 'completed_at' => null]);
        } else {
            $task->update(['status' => 'completed', 'completed_at' => now()]);
        }

        return response()->json($task);
    }

    public function destroy(Request $request, Task $task)
    {
        $this->authorizeOwner($request, $task);

        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }

    private function authorizeOwner(Request $request, Task $task): void
    {
        if (! $request->user()->isAdmin() && $task->user_id !== $request->user()->id) {
            abort(Response::HTTP_FORBIDDEN, 'You do not own this task.');
        }
    }
}
