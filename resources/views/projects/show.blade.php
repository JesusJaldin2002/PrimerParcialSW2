@extends('layouts.app')

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
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><b>Proyecto: </b> {{ $project->name }}</h4>
                        <div class="d-flex">
                            @if ($hasBacklog)
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle me-2" type="button"
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
                                <!-- Botón de Editar Backlog -->
                                <a href="{{ route('backlogs.edit', $project->id) }}"
                                    class="btn btn-warning d-flex align-items-center me-2">
                                    <i class="fas fa-edit me-2"></i> Editar Backlog
                                </a>

                                @if (auth()->user()->id == $project->owner_id)
                                    <!-- Botón de Eliminar Backlog -->
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
                            <!-- Mostrar Product Backlog -->
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
                                                <tr data-task-id="{{ $task->id }}">
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
                            <!-- Mostrar botón de crear Product Backlog -->
                            <div class="alert alert-warning">
                                <strong>No hay Product Backlog.</strong> Aún no se han definido sprints ni tareas.
                            </div>
                            <a href="{{ route('backlogs.create', $project->id) }}" class="btn btn-primary">
                                Crear Product Backlog
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="notification" class="notification"></div>
@endsection

@section('scripts')
    <!-- DataTables -->
    <script src="{{ asset('js/datatable_backlog.js') }}"></script>

    {{-- Sockets --}}
    <script>
        // Conectar al servidor de Socket.io
        const socket = io("http://localhost:4444"); // Cambia esta IP por la correcta si es necesario

        // Emitir un evento cuando el usuario esté viendo el proyecto
        socket.emit('user-connected', {
            projectId: "{{ $project->id }}",
            sprintId: null // No necesitamos el sprint en esta vista, pero puedes incluirlo si es necesario
        });

        // Escuchar el evento de actualización de tareas
        socket.on('task-updated', function(data) {
            const taskId = data.taskId;
            const newStatus = data.newStatus;

            // Hacer una petición AJAX para obtener los detalles de la tarea actualizada
            axios.get(`/projects/tasks/${taskId}`)
                .then(response => {
                    const task = response.data;

                    // Buscar la fila de la tabla donde está la tarea
                    const row = document.querySelector(`tr[data-task-id="${taskId}"]`);

                    if (row) {
                        // Actualizar el estado de la tarea en la tabla
                        const statusCell = row.querySelector('.task-status');
                        statusCell.innerHTML = getStatusBadge(task.status);

                        // Aquí también puedes actualizar otras columnas si es necesario
                    }
                })
                .catch(error => {
                    console.error("Error al obtener los detalles de la tarea actualizada:", error);
                });
        });

        // Escuchar el evento de eliminación de tareas
        socket.on('task-deleted', function(taskId) {
            // Buscar la fila de la tarea eliminada en la tabla
            const row = document.querySelector(`tr[data-task-id="${taskId}"]`);
            if (row) {
                // Eliminar la fila de la tabla
                row.remove();
            }
        });

        // Función auxiliar para obtener el badge de estado
        function getStatusBadge(status) {
            if (status === 'to do') {
                return '<span class="badge bg-warning text-dark">Por Hacer</span>';
            } else if (status === 'in progress') {
                return '<span class="badge bg-primary">En Proceso</span>';
            } else if (status === 'done') {
                return '<span class="badge bg-success">Completado</span>';
            }
        }
    </script>

    <script>
        // Función para confirmar eliminación del backlog con SweetAlert
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
