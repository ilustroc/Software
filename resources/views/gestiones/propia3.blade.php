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
@endsection
