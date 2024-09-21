<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use Illuminate\Http\Request;

class BacklogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backlogs.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $id)
    {
        // Validación de los datos entrantes
        $request->validate([
            'task_names.*' => 'required',
            'descriptions.*' => 'required',
            'statuses.*' => 'required|in:to do,in progress,done',
            'priorities.*' => 'required|in:high,medium,low',
            'sprints.*' => 'required|integer|min:1',
        ], [
            'task_names.*.required' => 'El nombre de la tarea es obligatorio.',
            'descriptions.*.required' => 'La descripción de la tarea es obligatoria.',
            'statuses.*.required' => 'El estado de la tarea es obligatorio.',
            'statuses.*.in' => 'El estado de la tarea es inválido.',
            'priorities.*.required' => 'La prioridad de la tarea es obligatoria.',
            'priorities.*.in' => 'La prioridad de la tarea es inválida.',
            'sprints.*.required' => 'El número de sprint es obligatorio.',
            'sprints.*.integer' => 'El número de sprint debe ser un número entero.',
            'sprints.*.min' => 'El número de sprint debe ser al menos 1.',
        ]);

        // Obtener el proyecto actual
        $project = Project::findOrFail($id);

        // Crear los sprints y las tareas
        $sprintsData = collect($request->input('sprints'))->unique(); // Obtener números de sprints únicos

        foreach ($sprintsData as $sprintNumber) {
            // Crear el sprint si no existe
            $sprint = Sprint::firstOrCreate([
                'name' => $sprintNumber,
                'project_id' => $project->id, // Asignar el project_id correctamente
                'start_date' => now(), // Ajustar si es necesario
                'end_date' => now()->addWeeks(2) // Ajustar la fecha según lo necesites
            ]);

            // Crear las tareas relacionadas con este sprint
            $tasksForSprint = $request->input('task_names');
            foreach ($tasksForSprint as $index => $taskName) {
                if ($request->input('sprints')[$index] == $sprintNumber) {
                    $task = Task::create([
                        'name' => $taskName,
                        'description' => $request->input('descriptions')[$index],
                        'status' => $request->input('statuses')[$index],
                        'priority' => $request->input('priorities')[$index],
                    ]);

                    // Asignar la tarea al sprint
                    $sprint->tasks()->attach($task->id);
                }
            }
        }

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Product Backlog creado con éxito.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
