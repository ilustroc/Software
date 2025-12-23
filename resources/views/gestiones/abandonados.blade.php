@extends('layouts.app')

@section('title', 'Llamadas Abandonadas')
@section('page_title', 'Llamadas Abandonadas')
@section('page_subtitle', 'Consulta y exportación de llamadas abandonadas desde el CRM.')

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

    {{-- FILTRO + ACCIONES --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="text-sm font-semibold text-slate-900">Cargar desde CRM</div>
                <div class="mt-0.5 text-xs text-slate-500">
                    Selecciona el rango de fechas para traer y exportar llamadas abandonadas.
                </div>
            </div>
            <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-700">
                CSV
            </span>
        </div>

        <form method="POST" action="{{ route('gestiones.abandonados.cargar') }}" class="mt-4">
            @csrf

            <div class="grid grid-cols-1 gap-3 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-3">
                    <label class="text-xs font-semibold text-slate-700">Desde</label>
                    <input type="date" name="desde" value="{{ $desde }}" required
                           class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100">
                </div>

                <div class="lg:col-span-3">
                    <label class="text-xs font-semibold text-slate-700">Hasta</label>
                    <input type="date" name="hasta" value="{{ $hasta }}" required
                           class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100">
                </div>

                <div class="lg:col-span-3">
                    <button class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 transition">
                        Cargar abandonados
                    </button>
                </div>

                <div class="lg:col-span-3">
                    <a href="{{ route('gestiones.abandonados.descargar', ['desde' => $desde, 'hasta' => $hasta]) }}"
                       class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 hover:bg-slate-50 transition">
                        Descargar CSV
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- TABLA --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
            <div>
                <div class="text-sm font-semibold text-slate-900">Listado de llamadas abandonadas</div>
                <div class="text-xs text-slate-500">Resultados según el rango seleccionado.</div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr class="text-left">
                        <th class="px-4 py-3">Fecha evento</th>
                        <th class="px-4 py-3">Evento</th>
                        <th class="px-4 py-3">CallID</th>
                        <th class="px-4 py-3">Queue</th>
                        <th class="px-4 py-3">CallerID</th>
                        <th class="px-4 py-3">TimeWait (s)</th>
                        <th class="px-4 py-3">Documento</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($registros as $r)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-900 whitespace-nowrap">{{ $r->fecha_evento }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $r->event }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $r->callidnum }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $r->queue }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $r->callerid }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $r->timewait }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $r->documento }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">
                                No se encontraron llamadas abandonadas en el rango seleccionado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-2 border-t border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-xs text-slate-500">
                @if($registros->total() > 0)
                    Mostrando {{ $registros->firstItem() }} al {{ $registros->lastItem() }} de {{ $registros->total() }} registros
                @else
                    Sin registros para el rango seleccionado
                @endif
            </div>
            <div>
                {{ $registros->onEachSide(1)->links('components.pagination-sm') }}
            </div>
        </div>
    </div>

</div>
@endsection
