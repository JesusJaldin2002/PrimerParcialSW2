@extends('layouts.app')

@section('styles')
    <style>
        .card {
            border: none;
            animation: fadeIn 0.5s ease-in-out;
        }

        .card-header {
            background-color: #212529;
            border-bottom: 2px solid #0275d8;
        }

        .btn-primary {
            background-color: #0275d8;
            border: none;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #025aa5;
            transform: scale(1.05);
        }

        .form-control {
            border-radius: 5px;
            border: 1px solid #ced4da;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus {
            border-color: #0275d8;
            box-shadow: 0 0 5px rgba(2, 117, 216, 0.5);
        }

        .text-primary {
            color: #0275d8 !important;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection

@section('content')
    <div class="container d-flex justify-content-center" style="height: 100%; padding-top: 50px;">
        <div class="card shadow-lg animate__animated animate__fadeIn" style="width: 400px; border-radius: 10px;">
            <div class="card-header bg-dark text-white text-center"
                style="border-top-left-radius: 10px; border-top-right-radius: 10px;">
                <h4 class="mb-0">Iniciar Sesión</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group mb-4">
                        <label for="email">Correo Electrónico</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="password">Contraseña</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                            name="password" required>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember"
                            {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            Recuérdame
                        </label>
                    </div>

                    <div class="d-grid">
                        <button type="submit"
                            class="btn btn-primary btn-block animate__animated animate__pulse animate__infinite"
                            style="background-color: #0275d8; border-color: #0275d8;">
                            Iniciar Sesión
                        </button>
                    </div>

                    <div class="text-center mt-3">
                        @if (Route::has('password.request'))
                            <a class="text-primary" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
                        @endif
                    </div>

                    <div class="text-center mt-2">
                        <a href="{{ route('register') }}" class="text-dark">¿No tienes cuenta? Regístrate</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
