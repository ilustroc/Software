@extends('layouts.app')

@section('title', 'Gestiones - Cartera Propia 3')
@section('page_title', 'Gestiones - Cartera Propia 3 (Zigor)')
@section('page_subtitle', 'Carga desde CRM (SP) o carga masiva de gestiones SMS desde XLSX.')

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
                    <div class="text-sm font-semibold text-slate-900">Cargar gestiones desde CRM</div>
                    <div class="mt-0.5 text-xs text-slate-500">
                        Ejecuta <span class="font-semibold text-slate-700">spGestionZigor(desde, hasta)</span>.
                    </div>
                </div>
                <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-700">
                    SP
                </span>
            </div>

            <form method="POST" action="{{ route('gestiones.propia3.cargar') }}" class="mt-4">
                @csrf

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold text-slate-700">Desde</label>
                        <input type="date" name="desde"
                               value="{{ old('desde', now()->toDateString()) }}"
                               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100"
                               required>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-700">Hasta</label>
                        <input type="date" name="hasta"
                               value="{{ old('hasta', now()->toDateString()) }}"
                               class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100"
                               required>
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-end">
                    <button class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 transition">
                        Cargar gestiones
                    </button>
                </div>
            </form>
        </div>

        {{-- CARGA SMS XLSX --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold text-slate-900">Cargar gestiones SMS (XLSX)</div>
                    <div class="mt-0.5 text-xs text-slate-500">
                        Inserta en <span class="font-semibold text-slate-700">Gestiones_Propia3</span> respetando la plantilla.
                    </div>
                </div>

                <a href="{{ route('gestiones.propia3.plantillaSms') }}"
                   class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Descargar plantilla
                </a>
            </div>

            <form method="POST"
                  action="{{ route('gestiones.propia3.cargarSms') }}"
                  enctype="multipart/form-data"
                  class="mt-4">
                @csrf

                <label class="text-xs font-semibold text-slate-700">Archivo XLSX</label>
                <input type="file" name="archivo" accept=".xlsx"
                       class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100 @error('archivo') border-red-300 @enderror"
                       required>
                @error('archivo') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror

                <div class="mt-4 flex items-center justify-between gap-3">
                    <div class="text-xs text-slate-500">Solo .xlsx, columnas exactas.</div>
                    <button type="submit"
                            class="rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition">
                        Cargar SMS
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
