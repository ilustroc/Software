@extends('layouts.app')

@section('title', 'Gestiones')
@section('page_title', 'Gestiones')
@section('page_subtitle', 'Carga CRM y carga manual XLSX por cartera.')

@push('styles')
    @vite(['resources/css/gestiones.css'])
@endpush

@section('content')
<div class="space-y-6">
    <div class="admin-card">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <form method="GET" action="{{ route('gestiones.index') }}" class="w-full max-w-md">
                <label class="admin-label">Cartera</label>
                <select name="cartera_id" class="admin-input" onchange="this.form.submit()">
                    @forelse($carteras as $item)
                        <option value="{{ $item->id }}" @selected((int) $carteraId === $item->id)>
                            {{ $item->nombre }}
                        </option>
                    @empty
                        <option value="">Sin carteras activas</option>
                    @endforelse
                </select>
            </form>

            @if($cartera)
                <span class="{{ $tieneSincronizacion ? 'status-pill status-pill-success' : 'status-pill status-pill-muted' }}">
                    {{ $tieneSincronizacion ? 'CRM configurado' : 'Solo carga manual' }}
                </span>
            @endif
        </div>
    </div>

    @if($cartera)
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="admin-card">
                <div class="border-b border-slate-100 pb-3 mb-4">
                    <h3 class="text-sm font-bold text-slate-900 uppercase">Sincronizacion CRM</h3>
                    <p class="text-[11px] text-slate-500 font-mono">{{ $cartera->nombre }}</p>
                </div>

                <form method="POST" action="{{ route('gestiones.cargar') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="cartera_id" value="{{ $cartera->id }}">

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="admin-label">Fecha inicio</label>
                            <input type="date" name="desde" value="{{ old('desde', now()->startOfMonth()->toDateString()) }}" class="admin-input" required>
                        </div>
                        <div>
                            <label class="admin-label">Fecha fin</label>
                            <input type="date" name="hasta" value="{{ old('hasta', now()->toDateString()) }}" class="admin-input" required>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-2">
                        <button type="submit" class="btn-primary w-full sm:w-auto" @disabled(!$tieneSincronizacion)>
                            Ejecutar sincronizacion
                        </button>
                    </div>
                </form>
            </div>

            <div class="admin-card">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase">Carga Manual XLSX</h3>
                        <p class="text-[11px] text-slate-500 font-mono">Tabla destino: gestiones</p>
                    </div>
                    <a href="{{ route('gestiones.manual.plantilla', ['cartera_id' => $cartera->id]) }}" class="btn-outline">
                        Descargar estructura
                    </a>
                </div>

                <form method="POST" action="{{ route('gestiones.manual.cargar') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="cartera_id" value="{{ $cartera->id }}">

                    <div>
                        <label class="admin-label">Seleccionar archivo</label>
                        <input type="file" name="archivo" accept=".xlsx" class="admin-input @error('archivo') border-red-300 @enderror" required>
                        @error('archivo') <p class="mt-1 text-[11px] text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit" class="btn-success w-full sm:w-auto">
                            Procesar importacion
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="admin-card text-center text-sm text-slate-500">
            Sin registros
        </div>
    @endif
</div>
@endsection
