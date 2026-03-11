@extends('layouts.app')

@php($tipo = 'propia3')

@section('title', 'Reporte Gestiones - Propia 3')
@section('page_title', 'Reporte · Gestiones - Propia 3')
@section('page_subtitle', 'Filtra por fecha, documento y tipificación, y exporta a XLSX.')

@push('styles')
    @vite(['resources/css/reportes.css'])
@endpush

@section('content')
<div class="report-page">

    <section class="report-card">
        <div class="report-card-header">
            <h2 class="report-title">Filtros de búsqueda</h2>
            <p class="report-subtitle">Consulta registros y exporta el resultado en Excel.</p>
        </div>

        <form method="GET" action="{{ route('reportes.gestiones.index', $tipo) }}" class="report-form-grid">
            <div>
                <label class="form-label">Desde</label>
                <input
                    type="date"
                    name="desde"
                    value="{{ $desde }}"
                    class="form-input"
                >
            </div>

            <div>
                <label class="form-label">Hasta</label>
                <input
                    type="date"
                    name="hasta"
                    value="{{ $hasta }}"
                    class="form-input"
                >
            </div>

            <div>
                <label class="form-label">DNI</label>
                <input
                    type="text"
                    name="documento"
                    value="{{ $documento ?? '' }}"
                    placeholder="12345678"
                    class="form-input"
                >
            </div>

            <div>
                <label class="form-label">Tipificación</label>
                <input
                    type="text"
                    name="tipificacion"
                    value="{{ $tipificacion ?? '' }}"
                    placeholder="NO CONTESTA"
                    class="form-input"
                >
            </div>

            <div class="report-actions md:col-span-2 xl:col-span-5">
                <button type="submit" class="report-btn-primary">
                    Filtrar
                </button>

                <a href="{{ route('reportes.gestiones.index', $tipo) }}" class="report-btn-secondary">
                    Limpiar
                </a>

                <a
                    href="{{ route('reportes.gestiones.xlsx', array_merge(['tipo' => $tipo], request()->query())) }}"
                    class="report-btn-export"
                >
                    Descargar XLSX
                </a>
            </div>
        </form>
    </section>

    <section class="report-list-card">
        <div class="report-list-head">
            <div>
                <h2 class="report-title">Listado</h2>
                <p class="report-subtitle">
                    Mostrando {{ $registros->count() ? $registros->firstItem() : 0 }}
                    a {{ $registros->count() ? $registros->lastItem() : 0 }}
                    de {{ $registros->total() }} registros
                </p>
            </div>
        </div>

        <div class="report-table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Documento</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Tipificación</th>
                        <th>Resultado</th>
                        <th>Operación</th>
                        <th>Campaña</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($registros as $r)
                        <tr>
                            <td class="whitespace-nowrap text-slate-700">
                                {{ \Carbon\Carbon::parse($r->dateprocessed)->format('d/m/Y') }}
                            </td>
                            <td class="whitespace-nowrap">{{ $r->documento }}</td>
                            <td>{{ $r->nombre }}</td>
                            <td class="whitespace-nowrap">{{ $r->callerid }}</td>
                            <td>{{ $r->value2 }}</td>
                            <td>{{ $r->value1 }}</td>
                            <td class="whitespace-nowrap">{{ $r->operacion }}</td>
                            <td>{{ $r->campaign }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="report-table-empty">
                                No hay registros con los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="report-pagination">
            {{ $registros->onEachSide(1)->links() }}
        </div>
    </section>

</div>
@endsection