<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Sprint;
use App\Models\Task;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(
            [
                'name' => 'required|string|max:255',
            ],
            [
                'name.required' => 'Por favor, ingrese un nombre',
            ]
        );

        $project = new Project();
        $project->name = $request->name;
        $project->owner_id = auth()->id();
        $project->share_code = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(8 / strlen($x)))), 1, 8);
        $project->save();


        $project->users()->attach(auth()->user());

        return redirect()->route('projects.show', $project->id)->with([
            'create' => 'ok',
            'name' => $project->name,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        $project = Project::with(['sprints.tasks'])->find($id);

        // Comprobar si el proyecto existe
        if (!$project) {
            return redirect()->route('home')->with('error', 'Este proyecto ha sido eliminado.');
        }

        // Comprobar si el usuario actual es un colaborador de el proyecto
        if (!$project->users->contains(auth()->user()->id)) {
            return redirect()->route('home')->with('error', 'No tienes permiso para ver este proyecto.');
        }

        // Verificar si el proyecto ya tiene un Product Backlog (sprints y tareas)
        $hasBacklog = $project->sprints->isNotEmpty() && $project->sprints->pluck('tasks')->flatten()->isNotEmpty();

        return view('projects.show', compact('project', 'hasBacklog'));
    }



    public function delete(string $id)
    {
        $project = Project::find($id);

        // Comprobar si el usuario actual es el propietario de el proyecto
        if ($project->owner_id != auth()->user()->id) {
            return redirect()->route('home')->with('error', 'No tienes permiso para eliminar este proyecto.');
        }

        $project->delete();

        return redirect()->route('home')->with([
            'delete' => 'ok',
            'name' => $project->name,
        ]);
    }


    public function add(Request $request)
    {
        $request->validate(
            [
                'codigo' => 'required|string',
            ],
            [
                'codigo.required' => 'Por favor, ingrese el codigo',
            ]
        );

        $project = Project::where('share_code', $request->codigo)->first();
        if ($project) {
            // Asociar el usuario autenticado a el proyecto
            $project->users()->attach(auth()->user());
            return redirect()->route('home')->with([
                'add' => 'ok',
                'name' => $project->name,
            ]);
        } else {
            // Sala no encontrada
            return redirect()->back()->with('error', 'Sala no encontrada, por favor ingrese otro código.');
        }
    }


    public function edit($id)
    {
        $project = Project::find($id);

        // Obtener todos los usuarios que no son el propietario de el proyecto
        $collaborators = User::where('id', '!=', $project->owner_id)->whereHas('projects', function ($query) use ($id) {
            $query->where('project_id', $id);
        })->get();

        return view('projects.edit', compact('project', 'collaborators'));
    }

    public function update(Request $request, $id)
    {
        $request->validate(
            [
                'name' => 'required|string|max:255',
            ],
            [
                'name.required' => 'Por favor, ingrese un nombre',
            ]
        );

        $project = Project::find($id);
        $project->name = $request->name;
        $project->share_code = $request->share_code;
        $project->save();

        // Asegurarse de que el propietario de el proyecto esté incluido en los colaboradores
        $collaborators = $request->collaborators ?? [];
        if (!in_array($project->owner_id, $collaborators)) {
            $collaborators[] = $project->owner_id;
        }

        // Actualizar los colaboradores de el proyecto
        $project->users()->sync($collaborators);

        return redirect()->route('home')->with([
            'update' => 'ok',
            'name' => $project->name,
        ]);
    }


    // Future implementation
    public function check($id)
    {
        $project = Project::find($id);

        $isCollaborator = $project && $project->users->contains(auth()->user()->id);

        return response()->json([
            'deleted' => $project ? false : true,
            'isCollaborator' => $isCollaborator,
        ]);
    }

    public function showKanban($projectId, $sprintId)
    {
        // Obtener el proyecto y verificar que el usuario es colaborador o propietario
        $project = Project::with('sprints.tasks')->findOrFail($projectId);

        // Comprobar si el usuario actual es un colaborador o propietario del proyecto
        if (!$project->users->contains(auth()->user()->id)) {
            return redirect()->route('home')->with('error', 'No tienes permiso para ver este tablero.');
        }

        // Obtener el sprint correspondiente
        $sprint = Sprint::with('tasks')->where('project_id', $projectId)->findOrFail($sprintId);

        return view('projects.kanban', compact('project', 'sprint'));
    }

    public function getSprintTasks($projectId, $sprintId)
    {
        // Obtener el proyecto y verificar que el usuario es colaborador o propietario
        $project = Project::findOrFail($projectId);

        // Comprobar si el usuario actual es un colaborador o propietario del proyecto
        if (!$project->users->contains(auth()->user()->id)) {
            return response()->json(['error' => 'No tienes permiso para acceder a estas tareas'], 403);
        }

        // Asegúrate de que el sprint pertenezca al proyecto correcto
        $sprint = Sprint::where('project_id', $projectId)
            ->where('id', $sprintId)
            ->with('tasks') // Cargar tareas relacionadas
            ->firstOrFail();

        // Retornar las tareas en formato JSON
        return response()->json($sprint->tasks);
    }

    public function updateStatus(Request $request, Task $task)
    {
        // Validar el nuevo estado
        $validated = $request->validate([
            'status' => 'required|in:to do,in progress,done',
        ]);

        // Actualizar el estado de la tarea
        $task->status = $validated['status'];
        $task->save();

        return response()->json(['message' => 'Estado de la tarea actualizado exitosamente'], 200);
    }

    // Tasks

    public function destroyTask(Task $task)
    {
        try {
            // Eliminar la tarea de la base de datos
            $task->delete();

            return response()->json(['message' => 'Tarea eliminada con éxito'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar la tarea'], 500);
        }
    }

    public function getTask($taskId)
    {
        // Buscar la tarea por su ID
        $task = Task::findOrFail($taskId);

        // Retornar la tarea como respuesta JSON
        return response()->json($task);
    }
}
