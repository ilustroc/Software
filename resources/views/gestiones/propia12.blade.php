@extends('layouts.app')

@section('title', 'Cargas Gestiones - Propia 1 y 2')
@section('page_title', 'Gestiones - Cartera Propia 1 y 2')
@section('page_subtitle', 'Carga desde CRM por rango de fechas o importación masiva desde Excel.')

@section('content')
<div class="space-y-5">

    {{-- ALERTAS --}}
    @if(session('msg'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <div class="flex items-start gap-2">
                <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-600 text-white text-xs">✓</span>
                <div class="flex-1">
                    <div class="font-semibold">Listo</div>
                    <div>{{ session('msg') }}</div>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <div class="flex items-start gap-2">
                <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-white text-xs">!</span>
                <div class="flex-1">
                    <div class="font-semibold">Atención</div>
                    <div>{{ session('error') }}</div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

        {{-- CARGA DESDE CRM --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold text-slate-900">Carga desde CRM</div>
                    <div class="mt-0.5 text-xs text-slate-500">
                        Ejecuta <span class="font-semibold text-slate-700">spGestionPropia(desde, hasta)</span> y copia a
                        <span class="font-semibold text-slate-700">Gestiones_1y2</span>.
                    </div>
                </div>
                <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-700">
                    SP
                </span>
            </div>

            <form method="POST" action="{{ route('gestiones.propia12.cargar') }}" class="mt-4">
                @csrf

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold text-slate-700">Fecha inicio</label>
                        <input type="date" name="desde"
                               value="{{ old('desde', now()->startOfMonth()->toDateString()) }}"
                               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100 @error('desde') border-red-300 @enderror">
                        @error('desde') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-700">Fecha fin</label>
                        <input type="date" name="hasta"
                               value="{{ old('hasta', now()->toDateString()) }}"
                               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100 @error('hasta') border-red-300 @enderror">
                        @error('hasta') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-xs text-slate-500">
                        Recomendado: rangos cortos si hay muchas gestiones.
                    </div>
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 transition">
                        Ejecutar carga
                    </button>
                </div>
            </form>
        </div>

        {{-- IMPORTAR DESDE EXCEL --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold text-slate-900">Subir gestiones desde Excel</div>
                    <div class="mt-0.5 text-xs text-slate-500">
                        Importación directa usando plantilla oficial (.xlsx).
                    </div>
                </div>

                <a href="{{ asset('plantillas/plantilla_gestiones_propia12.xlsx') }}" download
                   class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Descargar plantilla
                </a>
            </div>

            <form method="POST"
                  action="{{ route('gestiones.propia12.importExcel') }}"
                  enctype="multipart/form-data"
                  class="mt-4">
                @csrf

                <label class="text-xs font-semibold text-slate-700">Archivo Excel (.xlsx)</label>
                <input type="file" name="archivo" accept=".xlsx"
                       class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100 @error('archivo') border-red-300 @enderror">
                @error('archivo') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror

                <div class="mt-4 flex items-center justify-between gap-3">
                    <div class="text-xs text-slate-500">
                        Asegúrate de respetar los encabezados de la plantilla.
                    </div>
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition">
                        Importar Excel
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
