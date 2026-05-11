@extends('layouts.app')

@section('title', 'Reporte de gestiones')
@section('page_title', 'Reporte de gestiones')
@section('page_subtitle', 'Filtra por cartera, fechas, DNI y gestor.')

@push('styles')
    @vite(['resources/css/reportes.css'])
@endpush

@section('content')
<div class="report-page">
    <section class="report-card">
        <div class="report-card-header">
            <h2 class="report-title">Filtros</h2>
            <p class="report-subtitle">Reporte unico de gestiones para todas las carteras.</p>
        </div>

        <form method="GET" action="{{ route('reportes.gestiones.index') }}" class="report-form-grid">
            <div>
                <label class="form-label">Cartera</label>
                <select name="cartera_id" class="form-input">
                    <option value="">Todas</option>
                    @foreach($carteras as $item)
                        <option value="{{ $item->id }}" @selected((int) $carteraId === $item->id)>{{ $item->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label">Fecha inicio</label>
                <input type="date" name="fecha_inicio" value="{{ $desde }}" class="form-input">
            </div>

            <div>
                <label class="form-label">Fecha fin</label>
                <input type="date" name="fecha_fin" value="{{ $hasta }}" class="form-input">
            </div>

            <div>
                <label class="form-label">DNI</label>
                <input type="text" name="dni" value="{{ $dni }}" class="form-input">
            </div>

            <div>
                <label class="form-label">Gestor</label>
                <input type="text" name="gestor" value="{{ $gestor }}" class="form-input">
            </div>

            <div class="report-actions md:col-span-2 xl:col-span-3">
                <button type="submit" class="report-btn-primary">Filtrar</button>

                <a href="{{ route('reportes.gestiones.index') }}" class="report-btn-secondary">
                    Limpiar
                </a>

                <a href="{{ route('reportes.gestiones.xlsx', request()->query()) }}" class="report-btn-export">
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
                        <th>Cartera</th>
                        <th>Documento</th>
                        <th>Cliente/Socio</th>
                        <th>Telefono</th>
                        <th>Tipificacion</th>
                        <th>Resultado</th>
                        <th>Operacion</th>
                        <th>Gestor</th>
                        <th>Campana</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($registros as $r)
                        <tr>
                            <td class="whitespace-nowrap text-slate-700">
                                {{ $r->fecha_gestion ? \Carbon\Carbon::parse($r->fecha_gestion)->format('d/m/Y') : '' }}
                            </td>
                            <td class="whitespace-nowrap">{{ $r->cartera_nombre }}</td>
                            <td class="whitespace-nowrap">{{ $r->documento }}</td>
                            <td>{{ $r->cliente ?? $r->socio }}</td>
                            <td class="whitespace-nowrap">{{ $r->telefono }}</td>
                            <td>{{ $r->tipificacion }}</td>
                            <td>{{ $r->resultado }}</td>
                            <td class="whitespace-nowrap">{{ $r->operacion }}</td>
                            <td class="whitespace-nowrap">{{ $r->asesor }}</td>
                            <td>{{ $r->campaign }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="report-table-empty">Sin registros</td>
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
