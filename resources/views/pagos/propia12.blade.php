@extends('layouts.app')

@section('title', 'Pagos Propia 1 y 2')
@section('page_title', 'Pagos - Cartera Propia 1 y 2')
@section('page_subtitle', 'Carga masiva, registro manual y edición rápida de pagos.')

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


{{-- =====================================
     FILA SUPERIOR: IZQUIERDA / DERECHA
===================================== --}}
<div class="row g-4 mb-4 align-items-stretch">

    {{-- COLUMNA IZQUIERDA: CARGA MASIVA + BUSCAR --}}
    <div class="col-lg-5 d-flex flex-column gap-3">

        {{-- CARGA MASIVA --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <strong>Carga masiva de pagos</strong>
                    <div class="small text-muted">
                        Importa un archivo CSV con pagos de Cartera Propia 1 y 2.
                    </div>
                </div>
                <span class="badge text-bg-light border">
                    CSV / delimitado por comas
                </span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('pagos.propia12.upload') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3 align-items-end">

                        <div class="col-12">
                            <label class="form-label fw-semibold">Archivo CSV</label>
                            <input type="file"
                                   name="archivo"
                                   class="form-control form-control-sm"
                                   required>
                            <div class="form-text">
                                Asegúrate de que el CSV tenga las columnas esperadas
                                (DNI, Operación, Fecha, Monto, etc.).
                            </div>
                        </div>

                        <div class="col-12 d-grid">
                            <button class="btn btn-primary btn-sm">
                                <i class="bi bi-upload me-1"></i> Subir archivo
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- BUSCAR PAGOS / CLIENTE (SOLO DNI) --}}
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <strong>Buscar pagos / cliente</strong>
                    <div class="small text-muted">
                        Filtra por DNI del cliente.
                    </div>
                </div>
                @if($dni)
                    <span class="badge text-bg-secondary">
                        Filtro activo
                    </span>
                @endif
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('pagos.propia12.index') }}">
                    <div class="row g-3 align-items-end">

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">DNI</label>
                            <input type="text"
                                   name="dni"
                                   value="{{ $dni }}"
                                   class="form-control form-control-sm">
                        </div>

                        <div class="col-md-4 d-grid">
                            <button class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-search me-1"></i> Buscar
                            </button>
                        </div>

                        @if($dni)
                            <div class="col-12 d-grid">
                                <a href="{{ route('pagos.propia12.index') }}"
                                   class="btn btn-link btn-sm text-decoration-none">
                                    Limpiar filtro
                                </a>
                            </div>
                        @endif

                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- COLUMNA DERECHA: REGISTRAR PAGO MANUAL --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header">
                <strong>Registrar pago manual</strong>
                <div class="small text-muted">
                    Usa este formulario para corregir o registrar un pago puntual.
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('pagos.propia12.store') }}">
                    @csrf

                    <div class="row g-3">
                        {{-- Fila 1 --}}
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">DNI</label>
                            <input type="text"
                                   name="dni"
                                   class="form-control form-control-sm"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Operación</label>
                            <input type="text"
                                   name="operacion"
                                   class="form-control form-control-sm"
                                   required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Moneda</label>
                            <input type="text"
                                   name="moneda"
                                   class="form-control form-control-sm"
                                   placeholder="S/ o US$">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Fecha</label>
                            <input type="date"
                                   name="fecha"
                                   class="form-control form-control-sm"
                                   required>
                        </div>

                        {{-- Fila 2 --}}
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Monto</label>
                            <input type="number"
                                   step="0.01"
                                   name="monto"
                                   class="form-control form-control-sm"
                                   required>
                        </div>

                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Gestor</label>
                            <input type="text"
                                   name="gestor"
                                   class="form-control form-control-sm"
                                   placeholder="Opcional">
                        </div>

                        <div class="col-md-4 d-grid d-md-flex align-items-end justify-content-md-end mt-1">
                            <button class="btn btn-success btn-sm px-4 w-100 w-md-auto">
                                <i class="bi bi-plus-circle me-1"></i> Registrar pago
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


{{-- ===========================
     TABLA DE PAGOS
=========================== --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>Listado de pagos</strong>
        <span class="small text-muted">
            Mostrando {{ $pagos->count() }} de {{ $pagos->total() }} registros
        </span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr class="text-nowrap">
                        {{-- SIN ID --}}
                        <th>DNI</th>
                        <th>Operación</th>
                        <th>Fecha</th>
                        <th>Moneda</th>
                        <th class="text-end">Monto</th>
                        <th>Gestor</th>
                        <th style="width: 360px;">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($pagos as $p)
                        <tr>
                            <td>{{ $p->DNI }}</td>
                            <td>{{ $p->OPERACION }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->FECHA)->format('d/m/Y') }}</td>
                            <td>{{ $p->MONEDA }}</td>
                            <td class="text-end">
                                {{ number_format($p->MONTO, 2) }}
                            </td>
                            <td>{{ $p->GESTOR }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    {{-- BOTÓN EDITAR (ABRE MODAL) --}}
                                    <button type="button"
                                            class="btn btn-outline-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalPago{{ $p->id }}">
                                        <i class="bi bi-pencil-square me-1"></i> Editar
                                    </button>

                                    {{-- FORM BORRAR --}}
                                    <form method="POST"
                                        action="{{ route('pagos.propia12.destroy', $p->id) }}"
                                        onsubmit="return confirm('¿Eliminar este pago?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-trash me-1"></i> Borrar
                                        </button>
                                    </form>
                                </div>

                                {{-- MODAL EDITAR PAGO --}}
                                <div class="modal fade" id="modalPago{{ $p->id }}" tabindex="-1"
                                    aria-labelledby="modalPagoLabel{{ $p->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">

                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalPagoLabel{{ $p->id }}">
                                                    Editar pago
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                            </div>

                                            <form method="POST"
                                                action="{{ route('pagos.propia12.update', $p->id) }}">
                                                @csrf

                                                <div class="modal-body">
                                                    <div class="mb-2">
                                                        <label class="form-label mb-0">DNI</label>
                                                        <input type="text"
                                                            class="form-control form-control-sm"
                                                            value="{{ $p->DNI }}"
                                                            disabled>
                                                    </div>

                                                    <div class="mb-2">
                                                        <label class="form-label mb-0">Operación</label>
                                                        <input type="text"
                                                            class="form-control form-control-sm"
                                                            value="{{ $p->OPERACION }}"
                                                            disabled>
                                                    </div>

                                                    <div class="mb-2">
                                                        <label class="form-label mb-0">Fecha</label>
                                                        <input type="date"
                                                            name="fecha"
                                                            value="{{ $p->FECHA }}"
                                                            class="form-control form-control-sm"
                                                            required>
                                                    </div>

                                                    <div class="mb-2">
                                                        <label class="form-label mb-0">Moneda</label>
                                                        <input type="text"
                                                            name="moneda"
                                                            value="{{ $p->MONEDA }}"
                                                            class="form-control form-control-sm">
                                                    </div>

                                                    <div class="mb-2">
                                                        <label class="form-label mb-0">Monto</label>
                                                        <input type="number"
                                                            step="0.01"
                                                            name="monto"
                                                            value="{{ $p->MONTO }}"
                                                            class="form-control form-control-sm text-end"
                                                            required>
                                                    </div>

                                                    <div class="mb-0">
                                                        <label class="form-label mb-0">Gestor</label>
                                                        <input type="text"
                                                            name="gestor"
                                                            value="{{ $p->GESTOR }}"
                                                            class="form-control form-control-sm">
                                                    </div>
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button"
                                                            class="btn btn-secondary"
                                                            data-bs-dismiss="modal">
                                                        Cancelar
                                                    </button>
                                                    <button type="submit"
                                                            class="btn btn-primary">
                                                        Guardar cambios
                                                    </button>
                                                </div>
                                            </form>

                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                No se encontraron pagos para los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <div class="p-3 border-top">
            {{ $pagos->links() }}
        </div>
    </div>
</div>

@endsection
