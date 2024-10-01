@extends('layouts.app')

@section('content')
    <div class="container">
        <div id="kanban">
            <kanban-board :project-id="{{ $project->id }}" :sprint-id="{{ $sprint->id }}">
            </kanban-board>
        </div>
    </div>
@endsection
