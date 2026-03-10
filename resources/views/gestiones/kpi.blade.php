@extends('layouts.app')

@section('title', 'Cartera KPI')

@push('styles')
    @vite(['resources/css/gestiones.css'])
@endpush

@section('content')
<div class="space-y-6">
    @include('components.alerts')

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        
        <div class="admin-card">
            <div class="border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-sm font-bold text-slate-900 uppercase">Sincronización CRM</h3>
                <p class="text-[11px] text-slate-500 font-mono">SP: spGestionKpi</p>
            </div>

            <form method="POST" action="{{ route('gestiones.generica.cargar', 'kpi') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="admin-label">Fecha inicio</label>
                        <input type="date" name="desde" 
                               value="{{ old('desde', now()->toDateString()) }}" 
                               class="admin-input" required>
                    </div>
                    <div>
                        <label class="admin-label">Fecha fin</label>
                        <input type="date" name="hasta" 
                               value="{{ old('hasta', now()->toDateString()) }}" 
                               class="admin-input" required>
                    </div>
                </div>
                <div class="flex items-center justify-end pt-2">
                    <button type="submit" class="btn-primary w-full sm:w-auto">Ejecutar Sincronización</button>
                </div>
            </form>
        </div>

        <div class="admin-card">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase">Carga Manual XLSX</h3>
                    <p class="text-[11px] text-slate-500 font-mono">Tabla: Gestiones_Propia4</p>
                </div>
                <a href="{{ route('gestiones.manual.plantilla', 'kpi') }}" class="btn-outline">
                    Descargar Estructura
                </a>
            </div>

            <form method="POST" action="{{ route('gestiones.manual.cargar', 'kpi') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="admin-label">Seleccionar Archivo</label>
                    <input type="file" name="archivo" accept=".xlsx" 
                           class="admin-input" required>
                </div>
                <div class="flex items-center justify-end">
                    <button type="submit" class="btn-success w-full sm:w-auto">Procesar Importación</button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection