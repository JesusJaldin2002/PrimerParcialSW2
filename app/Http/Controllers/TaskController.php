<?php

namespace App\Http\Controllers;

use App\Models\Sprint;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function store(Request $request, $projectId, $sprintId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:high,medium,low',
            'status' => 'required|in:to do,in progress,done',
        ]);

        $task = Task::create($request->only(['name', 'description', 'priority', 'status']));

        // Asociar la tarea con el sprint actual
        $sprint = Sprint::findOrFail($sprintId);
        $sprint->tasks()->attach($task->id);

        return response()->json($task, 201);
    }
}
