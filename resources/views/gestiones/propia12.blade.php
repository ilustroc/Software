@extends('layouts.app')

@section('title', 'Cargas Gestiones - Propia 1 y 2')

@section('content')
    <h4>Carga de Gestiones - Cartera Propia 1 y 2</h4>

    @if(session('msg'))
        <div class="alert alert-success mt-3">
            {{ session('msg') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger mt-3">
            {{ session('error') }}
        </div>
    @endif

    <div class="card mt-3">
        <div class="card-body">
            <p class="mb-3">
                Este proceso ejecuta el <strong>spGestionPropia(fecha_inicio, fecha_fin)</strong> en el CRM y
                copia las gestiones a la tabla <strong>Gestiones_1y2</strong> de esta base de datos.
            </p>

            <form method="POST" action="{{ route('gestiones.propia12.cargar') }}" class="row g-3">
                @csrf

                <div class="col-md-4">
                    <label class="form-label">Fecha inicio</label>
                    <input type="date"
                           name="desde"
                           value="{{ old('desde', now()->startOfMonth()->toDateString()) }}"
                           class="form-control @error('desde') is-invalid @enderror">
                    @error('desde')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label">Fecha fin</label>
                    <input type="date"
                           name="hasta"
                           value="{{ old('hasta', now()->toDateString()) }}"
                           class="form-control @error('hasta') is-invalid @enderror">
                    @error('hasta')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        Ejecutar carga de gestiones
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Subir gestiones desde Excel</h5>
            <p class="mb-2">
                Usa la plantilla <a href="{{ asset('plantillas/plantilla_gestiones_propia12.xlsx') }}" download>
                plantilla_gestiones_propia12.xlsx</a> para registrar las gestiones.
            </p>

            <form method="POST"
                action="{{ route('gestiones.propia12.importExcel') }}"
                enctype="multipart/form-data"
                class="row g-3">
                @csrf
                <div class="col-md-6">
                    <label class="form-label">Archivo Excel (.xlsx)</label>
                    <input type="file"
                        name="archivo"
                        accept=".xlsx"
                        class="form-control @error('archivo') is-invalid @enderror">
                    @error('archivo')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12">
                    <button class="btn btn-success" type="submit">
                        Importar gestiones desde Excel
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
