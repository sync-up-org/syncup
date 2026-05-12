<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Tasks;
use App\Http\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => 'nullable|string|in:pending,completed,incomplete',
            'search' => 'nullable|string|max:255',
        ]);

        $query = $request->user()->tasks();

        if ($request->filled('status')) {
            $query->where('status', $validated['status']);
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\%', '\_'], $validated['search']);
            $query->whereRaw('title LIKE ? ESCAPE ?', ['%' . $search . '%', '\\']);
        }

        $tasks = $query->latest()->paginate(15);

        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request, TaskService $taskService)
    {
        $task = $taskService->createTask($request->user(), $request->validated());
        return new TaskResource($task);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tasks $task)
    {
        if ((int) $task->user_id !== (int) Auth::id()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Forbidden: You do not own this task.'
            ], 403);
        }

        $validated = $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string|max:10000',
            'status'      => 'sometimes|required|string|in:pending,completed,incomplete',
        ]);

        try {
            $task->update($validated);

            return response()->json([
                'status'  => 'success',
                'message' => 'Task updated successfully',
                'data'    => new TaskResource($task)
            ], 200);
        } catch (Throwable $e) {
            Log::error('Task update failed', [
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'Update failed',
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tasks $task)
    {
        if ((int) $task->user_id !== (int) Auth::id()) {
            return response()->json([
                'message' => 'Forbidden',
            ], 403);
        }

        $task->delete();
        return response()->json([
            'message' => 'Successfully deleted task',
        ]);
    }
}

