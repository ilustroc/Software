@extends('layouts.app')

@section('title', 'Reporte de pagos')
@section('page_title', 'Reporte de pagos')
@section('page_subtitle', 'Filtra por cartera, fechas, DNI y gestor.')

@push('styles')
    @vite(['resources/css/reportes.css'])
@endpush

@section('content')
<div class="report-page">
    <section class="report-card">
        <div class="report-card-header">
            <h2 class="report-title">Filtros</h2>
            <p class="report-subtitle">Reporte unico de pagos para todas las carteras.</p>
        </div>

        <form method="GET" action="{{ route('reportes.pagos.index') }}" class="report-form-grid">
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

                <a href="{{ route('reportes.pagos.index') }}" class="report-btn-secondary">
                    Limpiar
                </a>

                <a href="{{ route('reportes.pagos.xlsx', request()->query()) }}" class="report-btn-export">
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
                        <th>DNI</th>
                        <th>Operacion</th>
                        <th>Moneda</th>
                        <th class="text-right">Monto</th>
                        <th>Gestor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $r)
                        <tr>
                            <td class="whitespace-nowrap">{{ optional($r->fecha)->format('d/m/Y') }}</td>
                            <td class="whitespace-nowrap">{{ $r->cartera?->nombre ?? 'Sin cartera' }}</td>
                            <td class="whitespace-nowrap">{{ $r->dni }}</td>
                            <td class="whitespace-nowrap">{{ $r->operacion }}</td>
                            <td class="whitespace-nowrap">{{ $r->moneda }}</td>
                            <td class="whitespace-nowrap text-right">{{ number_format((float) $r->monto, 2, '.', ',') }}</td>
                            <td class="whitespace-nowrap">{{ $r->gestor }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="report-table-empty">Sin registros</td>
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
