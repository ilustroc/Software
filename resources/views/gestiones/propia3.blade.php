@extends('layouts.app')

@section('title', 'Gestiones Cartera Propia 3')

@section('content')
<h3>Gestiones - Cartera Propia 3 (Zigor)</h3>

@if(session('msg'))
    <div class="alert alert-success mt-3">{{ session('msg') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger mt-3">{{ session('error') }}</div>
@endif

{{-- CARGA DESDE CRM (SP) --}}
<div class="card border-0 shadow-sm mt-3">
    <div class="card-header">
        <strong>Cargar gestiones desde CRM (spGestionZigor)</strong>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('gestiones.propia3.cargar') }}" class="row g-3">
            @csrf

            <div class="col-md-3">
                <label class="form-label">Desde</label>
                <input type="date" name="desde" value="{{ old('desde', date('Y-m-d')) }}" class="form-control" required>
            </div>

            <div class="col-md-3">
                <label class="form-label">Hasta</label>
                <input type="date" name="hasta" value="{{ old('hasta', date('Y-m-d')) }}" class="form-control" required>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button class="btn btn-primary w-100">
                    Cargar gestiones Propia 3
                </button>
            </div>
        </form>
    </div>
</div>

{{-- CARGA DE GESTIONES SMS DESDE XLSX --}}
<div class="card border-0 shadow-sm mt-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Cargar gestiones SMS (archivo XLSX)</strong>
        <a href="{{ route('gestiones.propia3.plantillaSms') }}" class="btn btn-sm btn-outline-success">
            Descargar plantilla XLSX
        </a>
    </div>
    <div class="card-body">
        <p class="mb-2">
            Usa la plantilla y llena las columnas exactamente con estos nombres:
        </p>
        <ul>
            <li><code>documento</code></li>
            <li><code>nombre</code></li>
            <li><code>value2</code></li>
            <li><code>value1</code></li>
            <li><code>fullname</code></li>
            <li><code>operacion</code></li>
            <li><code>ctl</code></li>
            <li><code>dateprocessed</code></li>
            <li><code>fechaAgenda</code></li>
            <li><code>callerid</code></li>
            <li><code>comment</code></li>
            <li><code>pagar_por_cuota</code></li>
            <li><code>nroCuotas</code></li>
            <li><code>fecha_promesa</code></li>
            <li><code>campaign</code></li>
        </ul>

        <form method="POST"
              action="{{ route('gestiones.propia3.cargarSms') }}"
              enctype="multipart/form-data"
              class="row g-3">
            @csrf

            <div class="col-md-6">
                <label class="form-label">Archivo XLSX</label>
                <input type="file"
                       name="archivo"
                       accept=".xlsx"
                       class="form-control @error('archivo') is-invalid @enderror"
                       required>
                @error('archivo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">
                    Se insertan directamente en <code>Gestiones_Propia3</code> respetando todos esos campos.
                </small>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-success">
                    Cargar gestiones SMS
                </button>
            </div>
        </form>
    </div>
</div>
@endsection