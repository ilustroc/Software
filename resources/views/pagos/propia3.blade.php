@extends('layouts.app')

@php($cartera = 'propia3')

@section('title', 'Pagos Propia 3')
@section('page_title', 'Pagos - Cartera Propia 3')
@section('page_subtitle', 'Carga, búsqueda y mantenimiento de pagos.')

@push('styles')
    @vite(['resources/css/pagos.css'])
@endpush

@section('content')
<div class="pagos-page">

    <section class="pagos-card">
        <div class="pagos-card-header">
            <h2 class="pagos-title">Registrar pago manual</h2>
            <p class="pagos-subtitle">Registro individual de pagos.</p>
        </div>

        <form method="POST" action="{{ route('pagos.store', $cartera) }}" class="grid gap-4">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
                <div class="sm:col-span-3">
                    <label class="form-label">DNI</label>
                    <input
                        type="text"
                        name="dni"
                        value="{{ old('dni') }}"
                        inputmode="numeric"
                        required
                        placeholder="12345678"
                        class="form-input @error('dni') form-input-error @enderror"
                    >
                    @error('dni') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label class="form-label">Operación</label>
                    <input
                        type="text"
                        name="operacion"
                        value="{{ old('operacion') }}"
                        required
                        placeholder="N° operación"
                        class="form-input @error('operacion') form-input-error @enderror"
                    >
                    @error('operacion') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label class="form-label">Moneda</label>
                    <select name="moneda" class="form-input @error('moneda') form-input-error @enderror">
                        <option value="SOLES" {{ old('moneda', 'SOLES') === 'SOLES' ? 'selected' : '' }}>SOLES</option>
                        <option value="DOLARES" {{ old('moneda') === 'DOLARES' ? 'selected' : '' }}>DOLARES</option>
                    </select>
                    @error('moneda') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label class="form-label">Fecha</label>
                    <input
                        type="date"
                        name="fecha"
                        value="{{ old('fecha', now()->toDateString()) }}"
                        required
                        class="form-input @error('fecha') form-input-error @enderror"
                    >
                    @error('fecha') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label class="form-label">Monto</label>
                    <input
                        type="number"
                        step="0.01"
                        name="monto"
                        value="{{ old('monto') }}"
                        required
                        placeholder="0.00"
                        class="form-input @error('monto') form-input-error @enderror"
                    >
                    @error('monto') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-5">
                    <label class="form-label">Gestor</label>
                    <input
                        type="text"
                        name="gestor"
                        value="{{ old('gestor') }}"
                        placeholder="Opcional"
                        class="form-input @error('gestor') form-input-error @enderror"
                    >
                    @error('gestor') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-4 flex items-end">
                    <button type="submit" class="btn-primary">
                        Registrar pago
                    </button>
                </div>
            </div>
        </form>
    </section>

    <div class="pagos-tools">
        <section class="pagos-card">
            <div class="pagos-card-header">
                <h2 class="pagos-title">Carga masiva</h2>
                <p class="pagos-subtitle">Importa un archivo CSV o TXT.</p>
            </div>

            <form method="POST"
                  action="{{ route('pagos.upload', $cartera) }}"
                  enctype="multipart/form-data"
                  class="grid gap-4">
                @csrf

                <div>
                    <label class="form-label">Archivo</label>
                    <input
                        type="file"
                        name="archivo"
                        accept=".csv,.txt"
                        required
                        class="file-input @error('archivo') border-red-400 ring-2 ring-red-100 @enderror"
                    >
                    @error('archivo')
                        <p class="mt-2 text-xs text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-2 sm:grid-cols-2">
                    <button type="submit" class="btn-primary">
                        Subir archivo
                    </button>

                    <a href="{{ route('pagos.template', $cartera) }}" class="btn-secondary">
                        Descargar plantilla CSV
                    </a>
                </div>
            </form>
        </section>

        <section class="pagos-card">
            <div class="pagos-card-header">
                <h2 class="pagos-title">Buscar</h2>
                <p class="pagos-subtitle">Consulta pagos por DNI.</p>
            </div>

            <form method="GET" action="{{ route('pagos.index', $cartera) }}" class="grid gap-4">
                <div>
                    <label for="dni" class="form-label">DNI</label>
                    <input
                        id="dni"
                        type="text"
                        name="dni"
                        value="{{ $dni }}"
                        inputmode="numeric"
                        placeholder="12345678"
                        class="form-input"
                    >
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn-secondary flex-1">
                        Buscar
                    </button>

                    @if($dni)
                        <a href="{{ route('pagos.index', $cartera) }}" class="btn-muted">
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </section>
    </div>

    <section class="pagos-list-card">
        <div class="pagos-list-head">
            <div>
                <h2 class="pagos-title">Listado de pagos</h2>
                <p class="pagos-subtitle">Consulta, edición y eliminación de registros.</p>
            </div>
        </div>

        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>DNI</th>
                        <th>Operación</th>
                        <th>Fecha</th>
                        <th>Moneda</th>
                        <th class="text-right">Monto</th>
                        <th>Gestor</th>
                        <th class="text-center w-[160px]">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($pagos as $p)
                        <tr>
                            <td class="text-slate-900">{{ $p->DNI }}</td>
                            <td class="text-slate-900">{{ $p->OPERACION }}</td>
                            <td class="text-slate-700">{{ \Carbon\Carbon::parse($p->FECHA)->format('d/m/Y') }}</td>
                            <td class="text-slate-700">{{ $p->MONEDA }}</td>
                            <td class="text-right font-medium text-slate-900">{{ number_format($p->MONTO, 2) }}</td>
                            <td class="text-slate-700">{{ $p->GESTOR }}</td>
                            <td>
                                <div x-data="{ open: false }" class="action-group">
                                    <button type="button" class="icon-btn" @click="open = true">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                        </svg>
                                    </button>

                                    <form method="POST"
                                          action="{{ route('pagos.destroy', [$cartera, $p->id]) }}"
                                          onsubmit="return confirm('¿Eliminar este pago?');">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="icon-btn-danger">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path d="M3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M8 6V4h8v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M6 6l1 16h10l1-16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                    </form>

                                    @include('pagos.partials.edit-modal', ['cartera' => $cartera, 'p' => $p])
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="data-table-empty">
                                No se encontraron registros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="data-table-footer">
            <div class="data-table-meta">
                @if($pagos->count())
                    Mostrando {{ $pagos->firstItem() }} a {{ $pagos->lastItem() }} de {{ $pagos->total() }} registros
                @else
                    Sin registros para mostrar
                @endif
            </div>

            <div class="text-sm">
                {{ $pagos->onEachSide(1)->links() }}
            </div>
        </div>
    </section>
</div>
@endsection