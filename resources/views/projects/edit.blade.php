@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <b>Editar Proyecto:</b> {{ $project->name }}
                        </div>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver atrás
                        </a>
                    </div>

                    <div class="card-body py-4">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        <form method="POST" action="{{ route('projects.update', $project->id) }}" class="mt-3">
                            @csrf
                            @method('PUT')

                            <div class="form-group mb-4">
                                <label for="name"><b>Nombre del Proyecto</b></label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ $project->name }}" required>
                            </div>

                            <div class="form-group mb-4">
                                <label for="share_code"><b>Código para Compartir</b></label>
                                <input type="text" class="form-control" id="share_code" name="share_code"
                                    value="{{ $project->share_code }}" required>
                            </div>

                            <div class="form-group mb-4">
                                <label><b>Modificar Colaboradores:</b></label>
                                <p class="text-muted">Si desmarcas el checkbox, desvincularás al colaborador</p>
                                @foreach ($collaborators as $collaborator)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" value="{{ $collaborator->id }}"
                                            id="collaborator-{{ $collaborator->id }}" name="collaborators[]"
                                            {{ $project->users->contains($collaborator->id) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="collaborator-{{ $collaborator->id }}">
                                            {{ $collaborator->name }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-save"></i> Guardar cambios</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="notification" class="notification"></div>
@endsection
