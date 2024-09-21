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
    public function edit($id)
    {
        $project = Project::with(['sprints.tasks'])->find($id);

        if (!$project) {
            return redirect()->route('home')->with('error', 'Este proyecto ha sido eliminado.');
        }

        if (!$project->users->contains(auth()->user()->id)) {
            return redirect()->route('home')->with('error', 'No tienes permiso para editar este proyecto.');
        }

        $sprints = $project->sprints->sortBy('name'); // Ordenar por nombre de sprint

        return view('backlogs.edit', compact('project', 'sprints'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
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

        // Obtener los IDs de las tareas existentes
        $existingTasksIds = $request->input('existing_task_ids', []);

        // Obtener todas las tareas existentes del proyecto
        $existingTasks = Task::whereHas('sprints', function ($query) use ($project) {
            $query->where('project_id', $project->id);
        })->get();

        // Eliminar tareas que no están presentes en la solicitud
        $tasksToDelete = $existingTasks->filter(function ($task) use ($existingTasksIds) {
            return !in_array($task->id, $existingTasksIds);
        });
        foreach ($tasksToDelete as $task) {
            $task->delete(); // Eliminar las tareas faltantes
        }

        // Actualizar o crear tareas nuevas
        foreach ($request->input('task_names') as $index => $taskName) {
            $taskId = $existingTasksIds[$index] ?? null;

            if ($taskId) {
                // Actualizar la tarea existente
                $task = Task::findOrFail($taskId);
                $task->update([
                    'name' => $taskName,
                    'description' => $request->input('descriptions')[$index],
                    'status' => $request->input('statuses')[$index],
                    'priority' => $request->input('priorities')[$index],
                ]);
            } else {
                // Crear nueva tarea
                $task = Task::create([
                    'name' => $taskName,
                    'description' => $request->input('descriptions')[$index],
                    'status' => $request->input('statuses')[$index],
                    'priority' => $request->input('priorities')[$index],
                ]);
            }

            // Actualizar relación Sprint-Task
            $sprintNumber = $request->input('sprints')[$index];
            $sprint = Sprint::firstOrCreate([
                'name' => $sprintNumber,
                'project_id' => $project->id,
            ], [
                'start_date' => now(),
                'end_date' => now()->addWeeks(2),
            ]);

            // Asegurar que la tarea esté asociada al sprint correcto a través de la tabla pivote
            if (!$sprint->tasks->contains($task->id)) {
                $sprint->tasks()->attach($task->id);
            }
        }

        // Eliminar sprints que ya no tengan tareas asociadas
        foreach ($project->sprints as $sprint) {
            if ($sprint->tasks->isEmpty()) {
                $sprint->tasks()->detach(); // Limpiar las tareas asociadas
                $sprint->delete(); // Luego eliminar el sprint
            }
        }

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Product Backlog actualizado con éxito.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Encontrar el proyecto
        $project = Project::findOrFail($id);
        // Almacenar los IDs de las tareas que están asociadas al proyecto
        $taskIds = [];
        // Eliminar todos los sprints y tareas asociadas al proyecto
        foreach ($project->sprints as $sprint) {
            // Guardar los IDs de las tareas antes de detach
            $taskIds = array_merge($taskIds, $sprint->tasks->pluck('id')->toArray());
            // Desvincular las tareas del sprint
            $sprint->tasks()->detach();
            // Eliminar el sprint
            $sprint->delete();
        }

        // Eliminar las tareas que ya no están asociadas a ningún sprint
        Task::whereIn('id', $taskIds)
            ->doesntHave('sprints') // Verifica que no estén asociadas a ningún sprint
            ->delete();

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Product Backlog eliminado correctamente.');
    }
}
