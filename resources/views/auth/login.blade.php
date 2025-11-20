@extends('layouts.auth')

@section('title', 'Login - ImpulseGo')

@section('content')
<div class="card shadow-sm">
    <div class="card-header text-center">
        <strong>Ingreso a Reportes</strong>
    </div>
    <div class="card-body">
        @if($errors->has('login'))
            <div class="alert alert-danger">
                {{ $errors->first('login') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Usuario</label>
                <input type="text"
                       name="usuario"
                       value="{{ old('usuario') }}"
                       class="form-control @error('usuario') is-invalid @enderror"
                       autofocus>
                @error('usuario')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Contraseña</label>
                <input type="password"
                       name="password"
                       class="form-control @error('password') is-invalid @enderror">
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button class="btn btn-primary w-100" type="submit">
                Ingresar
            </button>
        </form>
    </div>
</div>
@endsection
