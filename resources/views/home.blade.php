@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><b>Proyectos</b></h4>

                        <div class="d-flex flex-column flex-sm-row">
                            <!-- Botón para crear nuevo proyecto -->
                            <button type="button" class="btn btn-primary me-sm-2 mb-2 mb-sm-0" data-bs-toggle="modal"
                                data-bs-target="#createProjectModal">
                                <i class="fas fa-plus"></i> Nuevo Proyecto
                            </button>

                            <!-- Botón para añadir proyecto por código -->
                            <button type="button" class="btn btn-secondary" data-bs-toggle="modal"
                                data-bs-target="#addProjectModal">
                                <i class="fas fa-key"></i> Ingresar Código
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <!-- Tabla de proyectos -->
                        <div class="table-responsive">
                            <table class="table" id="datatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Dueño</th>
                                        <th>Share Code</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($projects as $project)
                                        <tr>
                                            <td>{{ $project->id }}</td>
                                            <td>{{ $project->name }}</td>
                                            <td>{{ \App\Models\User::find($project->owner_id)->name }}</td>
                                            <td>
                                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                                                    <span class="share-code align-middle mb-2 mb-md-0">********</span>
                                                    <div class="d-flex justify-content-end align-items-center">
                                                        <i class="fas fa-eye cursor-pointer me-2"
                                                            onclick="toggleShareCode(this, '{{ $project->share_code }}')"></i>
                                                        <i class="fas fa-copy cursor-pointer"
                                                            onclick="copyToClipboard(this, '{{ $project->share_code }}')"></i>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex justify-content-end">
                                                    <a href="{{ route('projects.show', $project->id) }}"
                                                        class="btn btn-sm btn-primary me-1">
                                                        <i class="fas fa-eye"></i> Ver
                                                    </a>
                                                    @auth
                                                        @if (auth()->user()->id == $project->owner_id)
                                                            <a class="btn btn-sm btn-secondary me-1"
                                                                href="{{ route('projects.edit', $project->id) }}">
                                                                <i class="fas fa-edit"></i> Editar
                                                            </a>
                                                            <form id="delete-form-{{ $project->id }}"
                                                                action="{{ route('projects.delete', $project->id) }}"
                                                                method="POST" onsubmit="event.preventDefault();">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-danger"
                                                                    onclick="confirmDelete({{ $project->id }})">
                                                                    <i class="fas fa-trash"></i> Eliminar
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endauth
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear nuevo proyecto -->
    <div class="modal fade" id="createProjectModal" tabindex="-1" aria-labelledby="createProjectModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createProjectModalLabel">Nuevo Proyecto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('projects.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="name"><b>Nombre del Proyecto</b></label>
                            <input type="text" class="form-control" id="name" name="name"
                                placeholder="Ingrese un nombre para su Proyecto..." required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Crear Proyecto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para añadir código de proyecto -->
    <div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProjectModalLabel"><b>Añadir Proyecto</b></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('projects.add') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="codigo"><b>Código del Proyecto</b></label>
                            <input type="text" class="form-control" id="codigo" name="codigo"
                                placeholder="Ingrese el código del Proyecto" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Añadir Proyecto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="notification" class="notification"></div>
@endsection

@section('scripts')
    <script src="{{ asset('js/datatable.js') }}"></script>
    <script>
        function toggleShareCode(element, shareCode) {
            var shareCodeElement = element.parentElement.previousElementSibling;
            if (shareCodeElement.textContent === "********") {
                shareCodeElement.textContent = shareCode;
            } else {
                shareCodeElement.textContent = "********";
            }
        }

        function copyToClipboard(element, shareCode) {
            var text = shareCode;
            var textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();

            try {
                if (document.execCommand('copy')) {
                    var notification = document.getElementById('notification');
                    notification.textContent = 'Copiado al portapapeles';
                    notification.style.display = 'block';
                    setTimeout(function() {
                        notification.style.display = 'none';
                    }, 3000);
                } else {
                    throw new Error('Error al copiar al portapapeles');
                }
            } catch (err) {
                alert('Error al copiar al portapapeles');
            }

            document.body.removeChild(textArea);
        }

        function confirmDelete(projectId) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + projectId).submit();
                }
            })
        }
    </script>

    @if (session('add') == 'ok')
        <script>
            Swal.fire(
                'Añadido correctamente',
                'El Proyecto: {{ session('name') }} ha sido añadido correctamente.',
                'success'
            )
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "{{ session('error') }}",
            });
        </script>
    @endif
@endsection
