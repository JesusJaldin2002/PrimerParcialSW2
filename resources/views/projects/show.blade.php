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
                        <!-- Botón de exportar más alineado -->
                        <a href="{{ url('export-tasks') }}" class="btn btn-success d-flex align-items-center">
                            <i class="fas fa-file-excel me-2"></i> Exportar Backlog a Excel
                        </a>
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
                            <a href="" class="btn btn-primary">
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
    <script src="{{ asset('js/datatable.js') }}"></script>
@endsection
