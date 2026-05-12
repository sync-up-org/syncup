<?php

namespace App\Http\Services;

use App\Models\User;

class TaskService
{
    public function createTask(User $user, array $taskData)
    {
        return $user->tasks()->create($taskData);
    }
}
