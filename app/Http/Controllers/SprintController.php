<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class SprintController extends Controller
{
    public function getSprints($projectId)
    {
        // Busca el proyecto con sus sprints
        $project = Project::with('sprints')->findOrFail($projectId);
        
        // Retorna los sprints como respuesta JSON
        return response()->json($project->sprints);
    }
}