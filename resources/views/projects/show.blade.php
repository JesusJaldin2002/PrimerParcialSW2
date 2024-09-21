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
                                                <tr>
                                                    <td>{{ $task->name }}</td>
                                                    <td>{{ $task->description }}</td>
                                                    <td>
                                                        @if ($task->status == 'to do')
                                                            <span class="badge bg-warning text-dark">Por Hacer</span>
                                                        @elseif($task->status == 'in progress')
                                                            <span class="badge bg-primary">En Proceso</span>
                                                        @else
                                                            <span class="badge bg-success">Completada</span>
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
