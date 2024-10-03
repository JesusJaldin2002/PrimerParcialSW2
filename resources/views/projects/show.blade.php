@extends('layouts.app')

@section('styles')
    <style>
        @media (max-width: 768px) {

            .d-flex.flex-wrap>.dropdown,
            .d-flex.flex-wrap>a,
            .d-flex.flex-wrap>form {
                width: 100%;
                margin-bottom: 10px;
            }

            .dropdown-toggle {
                width: 100%;
            }
        }
    </style>
@endsection

@section('navbar-links')
    <li class="nav-item">
        <a class="nav-link" href="">Tableros</a>
    </li>
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                        <h4 class="mb-0"><b>Proyecto: </b> {{ $project->name }}</h4>
                        <div class="d-flex flex-wrap mt-2 mt-md-0">
                            @if ($hasBacklog)
                                <div class="dropdown me-2">
                                    <button class="btn btn-secondary dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        Seleccionar Sprint
                                    </button>
                                    <ul class="dropdown-menu">
                                        @foreach ($project->sprints as $sprint)
                                            <li><a class="dropdown-item"
                                                    href="{{ route('projects.kanban', ['projectId' => $project->id, 'sprintId' => $sprint->id]) }}">{{ $sprint->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <a href="{{ url('export-tasks') }}" class="btn btn-success d-flex align-items-center me-2">
                                    <i class="fas fa-file-excel me-2"></i> Exportar Backlog a Excel
                                </a>

                                <a href="{{ route('backlogs.edit', $project->id) }}"
                                    class="btn btn-warning d-flex align-items-center me-2">
                                    <i class="fas fa-edit me-2"></i> Editar Backlog
                                </a>

                                @if (auth()->user()->id == $project->owner_id)
                                    <form action="{{ route('backlogs.delete', $project->id) }}" method="POST"
                                        id="deleteBacklogForm">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-danger d-flex align-items-center"
                                            onclick="confirmDelete()">
                                            <i class="fas fa-trash-alt me-2"></i> Eliminar Backlog
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="card-body py-4">
                        <p><b>Fecha de Creación:</b> {{ $project->created_at->format('d/m/Y') }}</p>

                        @if ($hasBacklog)
                            <h5>Product Backlog</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="datatable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Descripción</th>
                                            <th>Estado</th>
                                            <th>Prioridad</th>
                                            <th>Sprint</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($project->sprints as $sprint)
                                            @foreach ($sprint->tasks as $task)
                                                <tr data-sprint="{{ $sprint->name }}" data-task="{{ $task->name }}">
                                                    <td>{{ $task->name }}</td>
                                                    <td>{{ $task->description }}</td>
                                                    <td class="task-status">
                                                        @if ($task->status == 'to do')
                                                            <span class="badge bg-warning text-dark">Por Hacer</span>
                                                        @elseif($task->status == 'in progress')
                                                            <span class="badge bg-primary">En Proceso</span>
                                                        @else
                                                            <span class="badge bg-success">Completado</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($task->priority == 'high')
                                                            <span class="badge bg-danger">Alta</span>
                                                        @elseif($task->priority == 'medium')
                                                            <span class="badge bg-warning">Media</span>
                                                        @else
                                                            <span class="badge bg-secondary">Baja</span>
                                                        @endif
                                                    </td>
                                                    <td>Sprint {{ $sprint->name }}</td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <strong>No hay Product Backlog.</strong> Aún no se han definido sprints ni tareas.
                            </div>
                            <a href="{{ route('backlogs.create', $project->id) }}" class="btn btn-primary">Crear Product
                                Backlog</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="notification" class="notification"></div>
@endsection

@section('scripts')
    <script src="{{ asset('js/datatable_backlog.js') }}"></script>

    <script>
        const socket = io("http://localhost:4444");

        socket.emit('user-connected', {
            projectId: "{{ $project->id }}",
            sprintId: null
        });

        socket.on('task-updated', function(data) {
            const taskId = data.taskId;
            axios.get(`/projects/tasks/${taskId}`).then(response => {
                const task = response.data;
                const row = document.querySelector(`tr[data-task-id="${taskId}"]`);
                if (row) {
                    const statusCell = row.querySelector('.task-status');
                    statusCell.innerHTML = getStatusBadge(task.status);
                }
            }).catch(error => {
                console.error("Error al obtener los detalles de la tarea actualizada:", error);
            });
        });

        socket.on('task-modal-created', function(data) {
            const taskId = data.taskId;
            const sprintName = data.sprintName; // Usamos el nombre del sprint
            const statusBadge = getStatusBadge(data.status);
            const priorityBadge = getPriorityBadge(data.priority);

            // Crear la nueva fila de la tarea
            const newTaskRow = `
            <tr data-task-id="${taskId}">
            <td>${data.name}</td>
            <td>${data.description}</td>
            <td>${statusBadge}</td>
            <td>${priorityBadge}</td>
            <td>Sprint ${sprintName}</td>
            </tr>
            `;

            // Buscar filas que tengan la celda con el nombre del sprint
            const rows = document.querySelectorAll('tbody tr');
            let lastSprintRow = null;

            rows.forEach(row => {
                const sprintCell = row.cells[4]; // La celda del sprint está en la columna 5 (index 4)
                if (sprintCell && sprintCell.textContent.trim() === `Sprint ${sprintName}`) {
                    lastSprintRow = row; // Guardar la última fila que pertenece a este sprint
                }
            });

            if (lastSprintRow) {
                // Insertar la nueva tarea después de la última tarea del sprint correspondiente
                lastSprintRow.insertAdjacentHTML('afterend', newTaskRow);
            } else {
                // Si no hay filas del sprint, agregarla al final
                document.querySelector('tbody').insertAdjacentHTML('beforeend', newTaskRow);
            }
        });

        socket.on('task-deleted', function(taskId) {
            const row = document.querySelector(`tr[data-task-id="${taskId}"]`);
            if (row) {
                row.remove();
            }
        });

        function getStatusBadge(status) {
            if (status === 'to do') {
                return '<span class="badge bg-warning text-dark">Por Hacer</span>';
            } else if (status === 'in progress') {
                return '<span class="badge bg-primary">En Proceso</span>';
            } else if (status === 'done') {
                return '<span class="badge bg-success">Completado</span>';
            }
        }

        function getPriorityBadge(priority) {
            if (priority === 'high') {
                return '<span class="badge bg-danger">Alta</span>';
            } else if (priority === 'medium') {
                return '<span class="badge bg-warning">Media</span>';
            } else {
                return '<span class="badge bg-secondary">Baja</span>';
            }
        }

        function confirmDelete() {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteBacklogForm').submit();
                }
            });
        }
    </script>
@endsection
