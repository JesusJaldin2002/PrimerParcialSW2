@extends('layouts.app')

@section('content')
    <div class="container">
        <div id="kanban">
            <kanban-board :project-id="{{ $project->id }}" :sprint-id="{{ $sprint->id }}">
            </kanban-board>
        </div>
    </div>
@endsection

@section('scriptsTop')
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"
        integrity="sha384-2huaZvOR9iDzHqslqwpR87isEmrfxqyWOF7hr7BY6KG0+hVKLoEXMPUJw3ynWuhO" crossorigin="anonymous">
    </script>
@endsection
