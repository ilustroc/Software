@extends('layouts.app')

@section('title', 'Pagos')
@section('page_title', 'Pagos')
@section('page_subtitle', 'Registro manual, carga XLSX y consulta unificada por cartera.')

@push('styles')
    @vite(['resources/css/pagos.css'])
@endpush

@section('content')
<div
    class="pagos-page"
    x-data="{ carteraModalOpen: @js($errors->cartera->any()) }"
>
    <section class="pagos-card">
        <div class="pagos-card-header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="pagos-title">Modulo unico de pagos</h2>
                <p class="pagos-subtitle">Selecciona una cartera para registrar o importar pagos.</p>
            </div>

            <button type="button" class="btn-primary w-auto px-4" @click="carteraModalOpen = true">
                Agregar cartera
            </button>
        </div>

        <form method="POST" action="{{ route('pagos.store') }}" class="grid gap-4">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-12">
                <div class="sm:col-span-3">
                    <label class="form-label">Cartera</label>
                    <select name="cartera_id" required class="form-input @error('cartera_id') form-input-error @enderror">
                        <option value="">Seleccionar</option>
                        @foreach($carteras as $item)
                            <option value="{{ $item->id }}" @selected((int) old('cartera_id', $carteraId) === $item->id)>
                                {{ $item->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('cartera_id') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">DNI</label>
                    <input type="text" name="dni" value="{{ old('dni') }}" inputmode="numeric" required class="form-input @error('dni') form-input-error @enderror">
                    @error('dni') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-3">
                    <label class="form-label">Operacion</label>
                    <input type="text" name="operacion" value="{{ old('operacion') }}" required class="form-input @error('operacion') form-input-error @enderror">
                    @error('operacion') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Moneda</label>
                    <select name="moneda" required class="form-input @error('moneda') form-input-error @enderror">
                        <option value="SOLES" @selected(old('moneda', 'SOLES') === 'SOLES')>SOLES</option>
                        <option value="DOLARES" @selected(old('moneda') === 'DOLARES')>DOLARES</option>
                    </select>
                    @error('moneda') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Fecha</label>
                    <input type="date" name="fecha" value="{{ old('fecha', now()->toDateString()) }}" required class="form-input @error('fecha') form-input-error @enderror">
                    @error('fecha') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="form-label">Monto</label>
                    <input type="number" step="0.01" name="monto" value="{{ old('monto') }}" required class="form-input @error('monto') form-input-error @enderror">
                    @error('monto') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-4">
                    <label class="form-label">Gestor</label>
                    <input type="text" name="gestor" value="{{ old('gestor') }}" class="form-input @error('gestor') form-input-error @enderror">
                    @error('gestor') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-6 flex items-end">
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
                <h2 class="pagos-title">Carga masiva XLSX</h2>
                <p class="pagos-subtitle">Columnas obligatorias: DNI, Operacion, Fecha, Moneda, Monto, Gestor.</p>
            </div>

            <form method="POST" action="{{ route('pagos.upload') }}" enctype="multipart/form-data" class="grid gap-4">
                @csrf

                <div>
                    <label class="form-label">Cartera</label>
                    <select name="cartera_id" required class="form-input @error('cartera_id') form-input-error @enderror">
                        <option value="">Seleccionar</option>
                        @foreach($carteras as $item)
                            <option value="{{ $item->id }}" @selected((int) old('cartera_id', $carteraId) === $item->id)>
                                {{ $item->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Archivo XLSX</label>
                    <input type="file" name="archivo" accept=".xlsx" required class="file-input @error('archivo') border-red-400 ring-2 ring-red-100 @enderror">
                    @error('archivo') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-2 sm:grid-cols-2">
                    <button type="submit" class="btn-primary">
                        Subir XLSX
                    </button>

                    <a href="{{ route('pagos.template') }}" class="btn-secondary">
                        Descargar plantilla
                    </a>
                </div>
            </form>
        </section>

        <section class="pagos-card">
            <div class="pagos-card-header">
                <h2 class="pagos-title">Filtros</h2>
                <p class="pagos-subtitle">Busca por DNI, operacion o gestor.</p>
            </div>

            <form method="GET" action="{{ route('pagos.index') }}" class="grid gap-4">
                <div>
                    <label class="form-label">Cartera</label>
                    <select name="cartera_id" class="form-input">
                        <option value="">Todas</option>
                        @foreach($carteras as $item)
                            <option value="{{ $item->id }}" @selected((int) $carteraId === $item->id)>
                                {{ $item->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Buscar</label>
                    <input type="text" name="buscar" value="{{ $buscar }}" placeholder="DNI, operacion o gestor" class="form-input">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn-secondary flex-1">
                        Filtrar
                    </button>

                    @if($buscar !== '' || $carteraId)
                        <a href="{{ route('pagos.index') }}" class="btn-muted">
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
                <p class="pagos-subtitle">Consulta, edicion y eliminacion de pagos en una sola tabla.</p>
            </div>
        </div>

        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cartera</th>
                        <th>DNI</th>
                        <th>Operacion</th>
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
                            <td class="font-medium text-slate-900">{{ $p->cartera?->nombre ?? 'Sin cartera' }}</td>
                            <td class="text-slate-900">{{ $p->dni }}</td>
                            <td class="text-slate-900">{{ $p->operacion }}</td>
                            <td class="text-slate-700">{{ optional($p->fecha)->format('d/m/Y') }}</td>
                            <td class="text-slate-700">{{ $p->moneda }}</td>
                            <td class="text-right font-medium text-slate-900">{{ number_format((float) $p->monto, 2) }}</td>
                            <td class="text-slate-700">{{ $p->gestor }}</td>
                            <td>
                                <div x-data="{ open: false }" class="action-group">
                                    <button type="button" class="icon-btn" @click="open = true" title="Editar">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                        </svg>
                                    </button>

                                    <form method="POST" action="{{ route('pagos.destroy', $p) }}" onsubmit="return confirm('Eliminar este pago?');">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="icon-btn-danger" title="Eliminar">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path d="M3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M8 6V4h8v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M6 6l1 16h10l1-16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                    </form>

                                    @include('pagos.partials.edit-modal', ['p' => $p, 'carteras' => $carteras])
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="data-table-empty">
                                Sin registros
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

    @include('carteras.partials.create-modal')
</div>
@endsection
