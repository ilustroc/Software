@extends('layouts.app')

@section('title', 'Reporte Gestiones - Propia 4')
@section('page_title', 'Reporte · Gestiones - Propia 4')
@section('page_subtitle', 'Filtra por fecha, DNI, teléfono y tipificación, y exporta a XLSX.')

@section('content')

@if(session('msg'))
  <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
    {{ session('msg') }}
  </div>
@endif

@if(session('error'))
  <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
    {{ session('error') }}
  </div>
@endif

<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
  <form method="GET" action="{{ route('reportes.gestiones.propia4') }}" class="grid grid-cols-1 gap-3 md:grid-cols-12">
    <div class="md:col-span-3">
      <label class="text-xs font-semibold text-slate-600">Desde</label>
      <input type="date" name="desde" value="{{ $desde }}"
             class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-slate-200">
    </div>

    <div class="md:col-span-3">
      <label class="text-xs font-semibold text-slate-600">Hasta</label>
      <input type="date" name="hasta" value="{{ $hasta }}"
             class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-slate-200">
    </div>

    <div class="md:col-span-3">
      <label class="text-xs font-semibold text-slate-600">DNI</label>
      <input type="text" name="documento" value="{{ $documento ?? '' }}" placeholder="Ej: 12345678"
             class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-slate-200">
    </div>

    <div class="md:col-span-3">
      <label class="text-xs font-semibold text-slate-600">Teléfono</label>
      <input type="text" name="telefono" value="{{ $telefono ?? '' }}" placeholder="Ej: 9xxxxxxxx"
             class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-slate-200">
    </div>

    <div class="md:col-span-3">
      <label class="text-xs font-semibold text-slate-600">Tipificación</label>
      <input type="text" name="tipificacion" value="{{ $tipificacion ?? '' }}" placeholder="Ej: NO CONTESTA"
             class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-slate-200">
    </div>

    <div class="md:col-span-12 flex flex-wrap gap-2 pt-1">
      <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
        Filtrar
      </button>

      <a href="{{ route('reportes.gestiones.propia4') }}"
         class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
        Limpiar
      </a>

      <a href="{{ route('reportes.gestiones.propia4.xlsx', request()->query()) }}"
         class="ml-auto rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-100">
        Descargar XLSX
      </a>
    </div>
  </form>
</div>

<div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
  <div class="border-b border-slate-200 px-4 py-3">
    <div class="text-sm font-semibold text-slate-900">Listado</div>
    <div class="text-xs text-slate-500">
      Mostrando {{ $registros->count() ? $registros->firstItem() : 0 }} - {{ $registros->count() ? $registros->lastItem() : 0 }}
      de {{ $registros->total() }}
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-left text-sm">
      <thead class="bg-slate-50 text-xs font-semibold text-slate-600">
        <tr>
          <th class="px-4 py-3">Fecha</th>
          <th class="px-4 py-3">Documento</th>
          <th class="px-4 py-3">Cliente</th>
          <th class="px-4 py-3">Teléfono</th>
          <th class="px-4 py-3">Tipificación</th>
          <th class="px-4 py-3">Resultado</th>
          <th class="px-4 py-3">Operación</th>
          <th class="px-4 py-3">Usuario</th>
          <th class="px-4 py-3">Campaña</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-slate-100">
        @forelse($registros as $r)
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3 whitespace-nowrap text-slate-700">{{ \Carbon\Carbon::parse($r->dateprocessed)->format('d/m/Y') }}</td>
            <td class="px-4 py-3 whitespace-nowrap">{{ $r->documento }}</td>
            <td class="px-4 py-3">{{ $r->cliente }}</td>
            <td class="px-4 py-3 whitespace-nowrap">{{ $r->callerid }}</td>
            <td class="px-4 py-3">{{ $r->value2 }}</td>
            <td class="px-4 py-3">{{ $r->value1 }}</td>
            <td class="px-4 py-3 whitespace-nowrap">{{ $r->operacion }}</td>
            <td class="px-4 py-3 whitespace-nowrap">{{ $r->fullname }}</td>
            <td class="px-4 py-3">{{ $r->campaign }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="10" class="px-4 py-10 text-center text-slate-500">
              No hay registros con los filtros seleccionados.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="border-t border-slate-200 px-4 py-3">
    {{ $registros->onEachSide(1)->links('pagination::tailwind') }}
  </div>
</div>

@endsection
