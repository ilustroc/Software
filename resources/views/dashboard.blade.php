@extends('layouts.app')

@section('title', 'Panel principal')

@section('page_title', 'Panel principal')
@section('page_subtitle', 'Accesos rápidos, estado del día y reportes.')

@section('content')
<div class="space-y-6">

    {{-- Header mini --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="text-lg font-semibold text-slate-900">Panel de Reportes</div>
            <div class="text-sm text-slate-500">
                Selecciona una acción rápida o entra a un módulo desde el menú.
            </div>
        </div>

        <div class="inline-flex items-center gap-2">
            <span class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-600">
                Hoy: {{ now()->format('d/m/Y') }}
            </span>

            @if(session()->has('usuario'))
                <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-sm">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    <span class="font-semibold text-slate-700">{{ session('usuario') }}</span>
                </span>
            @endif
        </div>
    </div>

    {{-- Acciones rápidas --}}
    <div>
        <div class="mb-3 text-sm font-semibold text-slate-800">Acciones rápidas</div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <a href="{{ route('gestiones.propia12.form') }}"
               class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:bg-slate-50 transition">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-slate-900/5 flex items-center justify-center">
                        <svg class="h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="none">
                            <path d="M8 6h13M8 12h13M8 18h13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M3 6h.01M3 12h.01M3 18h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-slate-900">Cargar Gestiones</div>
                        <div class="text-xs text-slate-500">Propia 1 y 2</div>
                    </div>
                </div>
            </a>

            <a href="{{ route('pagos.propia12.index') }}"
               class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:bg-slate-50 transition">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-slate-900/5 flex items-center justify-center">
                        <svg class="h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="none">
                            <path d="M3 7h18v10H3V7Z" stroke="currentColor" stroke-width="2"/>
                            <path d="M7 11h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-slate-900">Cargar Pagos</div>
                        <div class="text-xs text-slate-500">Propia 1 y 2</div>
                    </div>
                </div>
            </a>

            <a href="{{ route('parametros.tipificaciones.index') }}"
               class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:bg-slate-50 transition">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-slate-900/5 flex items-center justify-center">
                        <svg class="h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="none">
                            <path d="M9 11l3 3L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-slate-900">Tipificaciones</div>
                        <div class="text-xs text-slate-500">Parámetros del sistema</div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Resumen del día (placeholder sin data real) --}}
    <div>
        <div class="mb-3 text-sm font-semibold text-slate-800">Resumen de hoy</div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $cards = [
                    ['label'=>'Gestiones cargadas', 'value'=>'—', 'hint'=>'Total del día'],
                    ['label'=>'Pagos cargados', 'value'=>'—', 'hint'=>'Total del día'],
                    ['label'=>'Última carga', 'value'=>'—', 'hint'=>'Fecha / hora'],
                    ['label'=>'Estado', 'value'=>'OK', 'hint'=>'Sistema operativo'],
                ];
            @endphp

            @foreach($cards as $c)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="text-xs font-semibold text-slate-600">{{ $c['label'] }}</div>
                    <div class="mt-1 text-2xl font-semibold text-slate-900">{{ $c['value'] }}</div>
                    <div class="mt-1 text-xs text-slate-500">{{ $c['hint'] }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Acceso a reportes --}}
    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="text-sm font-semibold text-slate-900">Reportes</div>
                <div class="text-xs text-slate-600">Consulta rápida por cartera.</div>
            </div>
            <div class="flex flex-wrap gap-2">
                {{-- Si aún no existen rutas, esto no rompe --}}
                @php
                    $rg12 = 'reportes.gestiones.propia12';
                    $rp   = 'reportes.pagos.index';
                @endphp

                <a href="{{ \Illuminate\Support\Facades\Route::has($rg12) ? route($rg12) : '#' }}"
                   class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-100 transition">
                    Reporte Gestiones P1-2
                </a>

                <a href="{{ \Illuminate\Support\Facades\Route::has($rp) ? route($rp) : '#' }}"
                   class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition">
                    Reporte Pagos
                </a>
            </div>
        </div>
    </div>

</div>
@endsection
