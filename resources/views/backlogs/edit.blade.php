@extends('layouts.app')

@section('navbar-links')
    <li class="nav-item">
        <a class="nav-link" href="">Tableros</a>
    </li>
@endsection

@section('styles')
    <style>
        .custom-save-button {
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
        }

        .custom-save-button:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .custom-save-button:active {
            background-color: #004085;
            transform: translateY(0);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .custom-container {
            max-width: 90%;
        }

        .table-responsive {
            overflow-x: auto;
        }

        @media (max-width: 767.98px) {
            .custom-container {
                max-width: 100%;
                padding: 0 10px;
            }

            table th,
            table td {
                font-size: 12px;
                white-space: nowrap;
            }

            input.form-control,
            select.form-control {
                width: 100%;
            }

            .table td {
                padding: 0.3rem;
            }

            .btn {
                font-size: 12px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container custom-container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><b>Editar Product Backlog</b></h4>
                    </div>

                    <div class="card-body py-4">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form id="backlogForm" action="{{ route('backlogs.update', request()->route('id')) }}"
                            method="POST">
                            @csrf
                            @method('PUT')

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="backlogTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th style="width: 40%">Descripción</th>
                                            <th>Estado</th>
                                            <th>Prioridad</th>
                                            <th>Sprint</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="backlogBody">
                                        <!-- Tareas anteriores -->
                                        @foreach ($sprints as $sprint)
                                            @foreach ($sprint->tasks as $task)
                                                <tr>
                                                    <td><input type="text" name="task_names[]" class="form-control"
                                                            value="{{ $task->name }}"></td>
                                                    <td><input type="text" name="descriptions[]" class="form-control"
                                                            value="{{ $task->description }}"></td>
                                                    <td>
                                                        <select name="statuses[]" class="form-control">
                                                            <option value="to do"
                                                                {{ $task->status == 'to do' ? 'selected' : '' }}>Por hacer
                                                            </option>
                                                            <option value="in progress"
                                                                {{ $task->status == 'in progress' ? 'selected' : '' }}>En
                                                                proceso</option>
                                                            <option value="done"
                                                                {{ $task->status == 'done' ? 'selected' : '' }}>Completada
                                                            </option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="priorities[]" class="form-control">
                                                            <option value="high"
                                                                {{ $task->priority == 'high' ? 'selected' : '' }}>Alta
                                                            </option>
                                                            <option value="medium"
                                                                {{ $task->priority == 'medium' ? 'selected' : '' }}>Media
                                                            </option>
                                                            <option value="low"
                                                                {{ $task->priority == 'low' ? 'selected' : '' }}>Baja
                                                            </option>
                                                        </select>
                                                    </td>
                                                    <td><input type="number" name="sprints[]" class="form-control"
                                                            value="{{ $sprint->name }}"></td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger"
                                                            onclick="deleteRow(this)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-center">
                                <button type="button" class="btn btn-success" onclick="addRow()">
                                    <i class="fas fa-plus"></i> Añadir Fila
                                </button>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                <button type="submit" class="btn btn-primary custom-save-button">Guardar Product
                                    Backlog</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function addRow() {
            const tableBody = document.getElementById('backlogBody');
            const newRow = tableBody.insertRow();

            // Name input (for task number like HU1)
            const nameCell = newRow.insertCell(0);
            const nameInput = document.createElement('input');
            nameInput.type = 'text';
            nameInput.name = 'task_names[]';
            nameInput.className = 'form-control';
            nameInput.placeholder = 'HU...';
            nameCell.appendChild(nameInput);

            // Description input
            const descriptionCell = newRow.insertCell(1);
            const descriptionInput = document.createElement('input');
            descriptionInput.type = 'text';
            descriptionInput.name = 'descriptions[]';
            descriptionInput.className = 'form-control';
            descriptionInput.placeholder = 'Descripción de la tarea';
            descriptionCell.appendChild(descriptionInput);

            // Status select
            const statusCell = newRow.insertCell(2);
            const statusSelect = document.createElement('select');
            statusSelect.name = 'statuses[]';
            statusSelect.className = 'form-control';
            const statuses = [{
                    value: 'to do',
                    label: 'Por hacer'
                },
                {
                    value: 'in progress',
                    label: 'En proceso'
                },
                {
                    value: 'done',
                    label: 'Completada'
                }
            ];
            statuses.forEach(status => {
                const option = document.createElement('option');
                option.value = status.value;
                option.textContent = status.label;
                statusSelect.appendChild(option);
            });
            statusCell.appendChild(statusSelect);

            // Priority select
            const priorityCell = newRow.insertCell(3);
            const prioritySelect = document.createElement('select');
            prioritySelect.name = 'priorities[]';
            prioritySelect.className = 'form-control';
            const priorities = [{
                    value: 'high',
                    label: 'Alta'
                },
                {
                    value: 'medium',
                    label: 'Media'
                },
                {
                    value: 'low',
                    label: 'Baja'
                }
            ];
            priorities.forEach(priority => {
                const option = document.createElement('option');
                option.value = priority.value;
                option.textContent = priority.label;
                prioritySelect.appendChild(option);
            });
            priorityCell.appendChild(prioritySelect);

            // Sprint input
            const sprintCell = newRow.insertCell(4);
            const sprintInput = document.createElement('input');
            sprintInput.type = 'number';
            sprintInput.name = 'sprints[]';
            sprintInput.className = 'form-control';
            sprintInput.min = '1';
            sprintCell.appendChild(sprintInput);

            // Action buttons (like delete row)
            const actionCell = newRow.insertCell(5);
            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'btn btn-danger';
            deleteButton.innerHTML = '<i class="fas fa-trash"></i>';
            deleteButton.onclick = function() {
                deleteRow(deleteButton);
            };
            actionCell.appendChild(deleteButton);

            // Scroll automático al añadir una fila
            newRow.scrollIntoView({
                behavior: 'smooth'
            });
        }

        function deleteRow(button) {
            const row = button.closest('tr');
            row.remove();
        }

        // Validación antes de enviar el formulario
        document.getElementById('backlogForm').addEventListener('submit', function(event) {
            let rows = document.querySelectorAll('#backlogBody tr');
            let incompleteRows = [];

            // Verificar si hay al menos una fila
            if (rows.length === 0) {
                event.preventDefault(); // Prevenir el envío del formulario
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Debe haber al menos una tarea en el Product Backlog.',
                    confirmButtonText: 'OK'
                });
                return; // Salir del bloque si no hay filas
            }

            // Validar que las filas no estén incompletas
            rows.forEach((row, index) => {
                let inputs = row.querySelectorAll('input, select');
                let allFilled = true;

                inputs.forEach(input => {
                    if (input.value.trim() === '') {
                        allFilled = false;
                    }
                });

                // Si una fila está incompleta, agregamos el índice (1-based)
                if (!allFilled) {
                    incompleteRows.push(index + 1);
                }
            });

            if (incompleteRows.length > 0) {
                event.preventDefault(); // Prevenir el envío del formulario
                let message = incompleteRows.length > 1 ?
                    `Las filas ${incompleteRows.join(', ')} están incompletas.` :
                    `La fila ${incompleteRows[0]} está incompleta.`;

                // Mostrar alerta de SweetAlert con las filas incompletas
                Swal.fire({
                    icon: 'error',
                    title: 'Campos incompletos',
                    text: message,
                    confirmButtonText: 'OK'
                });
            }
        });
    </script>
@endsection
