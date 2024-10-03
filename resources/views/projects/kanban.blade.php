@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Estás en el Sprint: {{ $sprint->name }}</h2>
            <a href="{{ route('projects.show', $project->id) }}" class="btn btn-secondary">Volver atrás</a>
        </div>

        <div id="kanban">
            <kanban-board :project-id="{{ $project->id }}" :sprint-id="{{ $sprint->id }}">
            </kanban-board>
        </div>
    </div>
@endsection
