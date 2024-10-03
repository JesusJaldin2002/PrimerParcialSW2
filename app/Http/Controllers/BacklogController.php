<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        ]);

        // Obtener el proyecto actual
        $project = Project::findOrFail($id);

        // Convertir los IDs de las tareas existentes en enteros y eliminar duplicados
        $existingTasksIds = array_map('intval', $request->input('existing_task_ids', []));
        $existingTasksIds = array_unique($existingTasksIds);

        // Obtener todas las tareas existentes del proyecto
        $existingTasks = Task::whereHas('sprints', function ($query) use ($project) {
            $query->where('project_id', $project->id);
        })->get()->keyBy('id');  // Usamos `keyBy` para indexar las tareas por su ID

        // Almacenar las nuevas tareas
        $newTasks = [];
        // Almacenar las tareas actualizadas y eliminadas para emitir eventos
        $updatedTasks = [];
        $removedTasks = [];

        // Eliminar tareas que no están presentes en la solicitud
        $tasksToDelete = array_diff($existingTasks->keys()->toArray(), $existingTasksIds);

        foreach ($tasksToDelete as $taskId) {
            $task = Task::findOrFail($taskId);
            $removedTasks[] = $task; // Guardar la tarea eliminada
            $task->delete(); // Eliminar la tarea

            // Emitir el evento 'task-backlog-deleted' para cada tarea eliminada
            $this->notifySocketServer($task, 'task-backlog-deleted');
        }

        // Actualizar o crear tareas nuevas
        foreach ($request->input('task_names') as $index => $taskName) {
            $taskId = $existingTasksIds[$index] ?? null;

            if ($taskId && $existingTasks->has($taskId)) {
                // Obtener la tarea existente
                $task = $existingTasks->get($taskId);

                // Verificar si la tarea fue modificada o movida de sprint
                $taskModified = false;
                $sprintChanged = false;

                $sprintNumber = $request->input('sprints')[$index];
                $currentSprints = $task->sprints; // Obtener los sprints actuales de la tarea

                // Emitir primero el evento de actualización (task-backlog-updated)
                if (
                    $task->name !== $taskName ||
                    $task->description !== $request->input('descriptions')[$index] ||
                    $task->status !== $request->input('statuses')[$index] ||
                    $task->priority !== $request->input('priorities')[$index]
                ) {
                    $task->update([
                        'name' => $taskName,
                        'description' => $request->input('descriptions')[$index],
                        'status' => $request->input('statuses')[$index],
                        'priority' => $request->input('priorities')[$index],
                    ]);
                    $taskModified = true;
                    $updatedTasks[] = $task;
                }

                // Luego manejar los cambios de sprint
                foreach ($currentSprints as $currentSprint) {
                    if ($currentSprint->name != $sprintNumber) {
                        $currentSprint->tasks()->detach($task->id);
                        $sprintChanged = true;
                    }
                }

                if ($sprintChanged) {
                    $newSprint = Sprint::firstOrCreate([
                        'name' => $sprintNumber,
                        'project_id' => $project->id,
                    ], [
                        'start_date' => now(),
                        'end_date' => now()->addWeeks(2),
                    ]);

                    $newSprint->tasks()->syncWithoutDetaching($task->id);

                    // Emitir el evento de cambio de sprint (task-sprint-changed)
                    if (isset($currentSprint->id) && isset($newSprint->id)) {
                        $this->notifySocketServer($task, 'task-sprint-changed', [
                            'oldSprintId' => $currentSprint->id,
                            'newSprintId' => $newSprint->id,
                        ]);
                    } else {
                        Log::error('Error: Faltan datos para el cambio de sprint.');
                    }
                }
            } else {
                // Crear nueva tarea si el ID no existe
                $task = Task::create([
                    'name' => $taskName,
                    'description' => $request->input('descriptions')[$index],
                    'status' => $request->input('statuses')[$index],
                    'priority' => $request->input('priorities')[$index],
                ]);
                $newTasks[] = $task;

                // Asociar la tarea al sprint correcto
                $sprintNumber = $request->input('sprints')[$index];
                $sprint = Sprint::firstOrCreate([
                    'name' => $sprintNumber,
                    'project_id' => $project->id,
                ], [
                    'start_date' => now(),
                    'end_date' => now()->addWeeks(2),
                ]);

                $sprint->tasks()->attach($task->id);
            }
        }

        // Emitir eventos de Socket.io para las nuevas tareas
        foreach ($newTasks as $newTask) {
            $this->notifySocketServer($newTask, 'task-added');
        }

        // Emitir eventos de Socket.io para las tareas actualizadas
        foreach ($updatedTasks as $updatedTask) {
            $this->notifySocketServer($updatedTask, 'task-backlog-updated');
        }

        // Verificar si algún sprint se quedó sin tareas y eliminarlo
        $sprints = Sprint::where('project_id', $project->id)->get();

        foreach ($sprints as $sprint) {
            if ($sprint->tasks()->count() === 0) {
                $this->notifySocketServer($sprint, 'sprint-deleted');
                $sprint->delete();
            }
        }

        return redirect()->route('projects.show', $project->id)
            ->with('success', 'Product Backlog actualizado con éxito.');
    }


    private function notifySocketServer($entity, $eventType, $extraData = [])
    {
        $client = new \GuzzleHttp\Client();

        try {
            // Verificar si estamos notificando sobre una tarea o un sprint
            $isTask = $entity instanceof Task;
            $isSprint = $entity instanceof Sprint;

            // Log para verificar si se está llamando a la función correctamente
            Log::info("Notificando al servidor de sockets: Evento {$eventType}, ID {$entity->id}");

            // Datos básicos para la notificación
            $data = [
                'id' => $entity->id,  // Para sprint o tarea
            ];

            if ($isTask) {
                // Agregar datos específicos de la tarea
                $data['taskId'] = $entity->id;
                $data['name'] = $entity->name;
                $data['status'] = $entity->status;
                $data['description'] = $entity->description;

                // Verificar si hay sprints asociados
                $sprints = $entity->sprints ?? collect();
                if ($sprints->isNotEmpty()) {
                    $firstSprint = $sprints->first();
                    $data['projectId'] = $firstSprint->project_id;
                    $data['sprintId'] = $firstSprint->id;
                }
            } elseif ($isSprint) {
                // Agregar datos específicos del sprint
                $data['sprintId'] = $entity->id;
                $data['projectId'] = $entity->project_id;
            }

            // Si el evento es un cambio de sprint para una tarea
            if ($eventType === 'task-sprint-changed' && isset($extraData['oldSprintId'], $extraData['newSprintId'])) {
                $data['oldSprintId'] = $extraData['oldSprintId'];
                $data['newSprintId'] = $extraData['newSprintId']; // Usar newSprintId en lugar de newSprintNumber
            }

            // Enviar la solicitud al servidor de sockets
            $response = $client->post('http://localhost:4444/' . $eventType, [
                'json' => $data
            ]);

            // Log para verificar la respuesta del servidor de sockets
            Log::info("Respuesta del servidor de sockets: " . $response->getStatusCode());
        } catch (\Exception $e) {
            // Log para capturar cualquier error en el proceso
            Log::error('Error al enviar el evento al servidor de WebSockets: ' . $e->getMessage());
        }
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
