@extends('layouts.app')

@section('title', 'Llamadas Abandonadas')

@push('styles')
    @vite(['resources/css/gestiones.css'])
@endpush

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        
        <div class="admin-card">
            <div class="border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-sm font-bold text-slate-900 uppercase">Sincronización CRM</h3>
                <p class="text-[11px] text-slate-500 font-mono">Tabla: Llamadas_Abandonadas</p>
            </div>

            <form method="POST" action="{{ route('gestiones.generica.cargar', 'abandonados') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="admin-label">Desde</label>
                        <input type="date" name="desde" value="{{ $desde }}" class="admin-input" required>
                    </div>
                    <div>
                        <label class="admin-label">Hasta</label>
                        <input type="date" name="hasta" value="{{ $hasta }}" class="admin-input" required>
                    </div>
                </div>
                <div class="flex items-center justify-end pt-2">
                    <button type="submit" class="btn-primary w-full sm:w-auto">Sincronizar CRM</button>
                </div>
            </form>
        </div>

        <div class="admin-card">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase">Importación Manual</h3>
                    <p class="text-[11px] text-slate-500 font-mono">Formato: XLSX</p>
                </div>
                <a href="{{ route('gestiones.manual.plantilla', 'abandonados') }}" class="btn-outline">
                    Descargar Estructura
                </a>
            </div>

            <form method="POST" action="{{ route('gestiones.manual.cargar', 'abandonados') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="admin-label">Archivo de Excel</label>
                    <input type="file" name="archivo" accept=".xlsx" class="admin-input" required>
                </div>
                <div class="flex items-center justify-end">
                    <button type="submit" class="btn-success w-full sm:w-auto">Procesar Excel</button>
                </div>
            </form>
        </div>
    </div>

    <div class="admin-card !p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 border-b border-slate-100 text-[10px] uppercase font-bold text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Fecha Evento</th>
                        <th class="px-5 py-3">Cola (Queue)</th>
                        <th class="px-5 py-3">CallerID</th>
                        <th class="px-5 py-3">Espera (Seg)</th>
                        <th class="px-5 py-3">Documento</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($registros as $r)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-5 py-3 text-slate-900 font-medium">
                                {{ \Carbon\Carbon::parse($r->fecha_evento)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-5 py-3 text-slate-600 font-mono text-xs">{{ $r->queue }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $r->callerid }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $r->timewait }}s</td>
                            <td class="px-5 py-3 text-slate-600">{{ $r->documento }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-slate-400">No hay llamadas abandonadas registradas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($registros->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $registros->onEachSide(1)->links('components.pagination-sm') }}
            </div>
        @endif
    </div>
</div>
@endsection