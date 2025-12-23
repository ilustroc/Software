@extends('layouts.app')

@section('title', 'Reporte Pagos')
@section('page_title', 'Reporte · Pagos')
@section('page_subtitle', 'Filtra por fecha, agente y cartera, y exporta a XLSX.')

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
  <form method="GET" action="{{ route('reportes.pagos.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-12">

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
      <label class="text-xs font-semibold text-slate-600">Agente</label>
      <input type="text" name="agente" value="{{ $agente }}" placeholder="Ej: CARLOS"
             class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-slate-200">
    </div>

    <div class="md:col-span-3">
      <label class="text-xs font-semibold text-slate-600">Cartera</label>
      <select name="cartera"
              class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-slate-200">
        <option value="">Todas</option>
        @foreach($carteras as $c)
          <option value="{{ $c }}" @selected($cartera === $c)>{{ $c }}</option>
        @endforeach
      </select>
    </div>

    <div class="md:col-span-12 flex flex-wrap gap-2 pt-1">
      <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
        Filtrar
      </button>

      <a href="{{ route('reportes.pagos.index') }}"
         class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
        Limpiar
      </a>

      <a href="{{ route('reportes.pagos.xlsx', request()->query()) }}"
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
          <th class="px-4 py-3">Cartera</th>
          <th class="px-4 py-3">DNI</th>
          <th class="px-4 py-3">Operación</th>
          <th class="px-4 py-3">Moneda</th>
          <th class="px-4 py-3">Monto</th>
          <th class="px-4 py-3">Gestor</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($registros as $r)
          <tr class="hover:bg-slate-50">
            <td class="px-4 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($r->FECHA ?? $r->fecha)->format('d/m/Y') }}</td>
            <td class="px-4 py-3 whitespace-nowrap">{{ $r->cartera }}</td>
            <td class="px-4 py-3 whitespace-nowrap">{{ $r->DNI ?? $r->dni }}</td>
            <td class="px-4 py-3 whitespace-nowrap">{{ $r->OPERACION ?? $r->operacion }}</td>
            <td class="px-4 py-3 whitespace-nowrap">{{ $r->MONEDA ?? $r->moneda }}</td>
            <td class="px-4 py-3 whitespace-nowrap">{{ number_format((float)($r->MONTO ?? $r->monto), 2, '.', ',') }}</td>
            <td class="px-4 py-3 whitespace-nowrap">{{ $r->GESTOR ?? $r->gestor }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-4 py-10 text-center text-slate-500">
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