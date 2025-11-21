@extends('layouts.app')

@section('title', 'Llamadas Abandonadas')
@section('page_title', 'Llamadas Abandonadas')
@section('page_subtitle', 'Consulta y exportación de llamadas abandonadas desde el CRM.')

@section('content')

{{-- ALERTAS --}}
@if(session('msg'))
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="bi bi-check-circle me-1"></i> {{ session('msg') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif


{{-- FORM RANGO DE FECHAS + CARGA --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <strong>Cargar desde CRM</strong>
            <div class="small text-muted">
                Selecciona el rango de fechas para traer y exportar llamadas abandonadas.
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('gestiones.abandonados.cargar') }}" class="row g-3 align-items-end">
            @csrf

            <div class="col-md-3">
                <label class="form-label fw-semibold">Desde</label>
                <input type="date"
                       name="desde"
                       value="{{ $desde }}"
                       class="form-control form-control-sm"
                       required>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Hasta</label>
                <input type="date"
                       name="hasta"
                       value="{{ $hasta }}"
                       class="form-control form-control-sm"
                       required>
            </div>

            <div class="col-md-3 d-grid">
                <button class="btn btn-primary btn-sm">
                    <i class="bi bi-cloud-download me-1"></i> Cargar abandonados
                </button>
            </div>

            <div class="col-md-3 d-grid">
                <a href="{{ route('gestiones.abandonados.descargar', ['desde' => $desde, 'hasta' => $hasta]) }}"
                   class="btn btn-outline-success btn-sm">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Descargar CSV
                </a>
            </div>
        </form>
    </div>
</div>


{{-- TABLA --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header">
        <strong>Listado de llamadas abandonadas</strong>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover table-striped mb-0 align-middle">
                <thead class="table-light">
                    <tr class="text-nowrap">
                        <th>Fecha evento</th>
                        <th>Evento</th>
                        <th>CallID</th>
                        <th>Queue</th>
                        <th>CallerID</th>
                        <th>TimeWait (s)</th>
                        <th>Documento</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $r)
                        <tr>
                            <td>{{ $r->fecha_evento }}</td>
                            <td>{{ $r->event }}</td>
                            <td>{{ $r->callidnum }}</td>
                            <td>{{ $r->queue }}</td>
                            <td>{{ $r->callerid }}</td>
                            <td>{{ $r->timewait }}</td>
                            <td>{{ $r->documento }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No se encontraron llamadas abandonadas en el rango seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-muted mb-0">
                @if($registros->total() > 0)
                    Mostrando {{ $registros->firstItem() }} al {{ $registros->lastItem() }}
                    de {{ $registros->total() }} registros
                @else
                    Sin registros para el rango seleccionado
                @endif
            </small>

            {{-- paginación pequeña (usa el mismo componente que en pagos) --}}
            {{ $registros->onEachSide(1)->links('components.pagination-sm') }}
        </div>
    </div>
</div>

@endsection
