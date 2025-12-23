@extends('layouts.app')

@section('title', 'Pagos Propia 4')
@section('page_title', 'Pagos - Cartera Propia 4')
@section('page_subtitle', 'Carga masiva, registro manual y edición rápida de pagos.')

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

    {{-- TOP --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="text-sm text-slate-600">
            @if($dni)
                <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5">
                    <span class="h-2 w-2 rounded-full bg-sky-500"></span>
                    Filtro activo DNI: <span class="font-semibold text-slate-900">{{ $dni }}</span>
                </span>
            @else
                <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5">
                    Total registros: <span class="font-semibold text-slate-900">{{ $pagos->total() }}</span>
                </span>
            @endif
        </div>

        <a href="{{ route('pagos.propia4.template') }}"
           class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 transition">
            Descargar plantilla CSV
        </a>
    </div>

    {{-- GRID --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">

        {{-- IZQUIERDA --}}
        <div class="lg:col-span-5 space-y-4">

            {{-- CARGA MASIVA --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold text-slate-900">Carga masiva de pagos</div>
                        <div class="mt-0.5 text-xs text-slate-500">
                            Importa un archivo CSV/TXT para Cartera Propia 4 (delimitado por comas).
                        </div>
                    </div>
                    <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-700">
                        CSV/TXT
                    </span>
                </div>

                <form method="POST" action="{{ route('pagos.propia4.upload') }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                    @csrf

                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4">
                        <label class="block text-xs font-semibold text-slate-700">Archivo CSV / TXT</label>

                        <input
                            type="file"
                            name="archivo"
                            accept=".csv,.txt"
                            required
                            class="mt-2 block w-full text-sm
                                   file:mr-4 file:rounded-xl file:border-0 file:bg-slate-900 file:px-4 file:py-2
                                   file:text-sm file:font-semibold file:text-white hover:file:bg-slate-800
                                   text-slate-600
                                   @error('archivo') ring-2 ring-red-200 rounded-xl @enderror"
                        >
                        @error('archivo')
                            <p class="mt-2 text-xs text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 transition"
                        type="submit"
                    >
                        Subir archivo
                    </button>
                </form>
            </div>

            {{-- BUSCAR --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold text-slate-900">Buscar pagos / cliente</div>
                    </div>
                    @if($dni)
                        <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-700">
                            Filtro
                        </span>
                    @endif
                </div>

                <form method="GET" action="{{ route('pagos.propia4.index') }}" class="mt-4 space-y-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-700">DNI</label>
                        <input
                            type="text"
                            name="dni"
                            value="{{ $dni }}"
                            inputmode="numeric"
                            placeholder="Ej: 12345678"
                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none
                                   focus:border-slate-300 focus:ring-4 focus:ring-slate-100"
                        >
                    </div>

                    <div class="flex gap-2">
                        <button
                            class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 transition"
                            type="submit"
                        >
                            Buscar
                        </button>

                        @if($dni)
                            <a
                                href="{{ route('pagos.propia4.index') }}"
                                class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 transition"
                            >
                                Limpiar
                            </a>
                        @endif
                    </div>
                </form>
            </div>

        </div>

        {{-- DERECHA --}}
        <div class="lg:col-span-7">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="text-sm font-semibold text-slate-900">Registrar pago manual</div>
                        <div class="mt-0.5 text-xs text-slate-500">
                            Usa este formulario para corregir o registrar un pago puntual.
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('pagos.propia4.store') }}" class="mt-4">
                    @csrf

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-12">

                        <div class="sm:col-span-3">
                            <label class="text-xs font-semibold text-slate-700">DNI</label>
                            <input type="text" name="dni" value="{{ old('dni') }}" required inputmode="numeric"
                                   class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100 @error('dni') border-red-300 ring-2 ring-red-100 @enderror"
                                   placeholder="12345678">
                            @error('dni') <p class="mt-1 text-xs text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label class="text-xs font-semibold text-slate-700">Operación</label>
                            <input type="text" name="operacion" value="{{ old('operacion') }}" required
                                   class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100 @error('operacion') border-red-300 ring-2 ring-red-100 @enderror"
                                   placeholder="N° operación">
                            @error('operacion') <p class="mt-1 text-xs text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label class="text-xs font-semibold text-slate-700">Moneda</label>
                            <select name="moneda"
                                    class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100 @error('moneda') border-red-300 ring-2 ring-red-100 @enderror">
                                <option value="SOLES" {{ old('moneda','SOLES')=='SOLES'?'selected':'' }}>SOLES</option>
                                <option value="DOLARES" {{ old('moneda')=='DOLARES'?'selected':'' }}>DOLARES</option>
                            </select>
                            @error('moneda') <p class="mt-1 text-xs text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label class="text-xs font-semibold text-slate-700">Fecha</label>
                            <input type="date" name="fecha" value="{{ old('fecha', now()->toDateString()) }}" required
                                   class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100 @error('fecha') border-red-300 ring-2 ring-red-100 @enderror">
                            @error('fecha') <p class="mt-1 text-xs text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label class="text-xs font-semibold text-slate-700">Monto</label>
                            <input type="number" step="0.01" name="monto" value="{{ old('monto') }}" required
                                   class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100 @error('monto') border-red-300 ring-2 ring-red-100 @enderror"
                                   placeholder="0.00">
                            @error('monto') <p class="mt-1 text-xs text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-5">
                            <label class="text-xs font-semibold text-slate-700">Gestor</label>
                            <input type="text" name="gestor" value="{{ old('gestor') }}" placeholder="Opcional"
                                   class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100 @error('gestor') border-red-300 ring-2 ring-red-100 @enderror">
                            @error('gestor') <p class="mt-1 text-xs text-red-700">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-4 flex items-end">
                            <button type="submit"
                                    class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition">
                                Registrar pago
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
            <div>
                <div class="text-sm font-semibold text-slate-900">Listado de pagos</div>
                <div class="text-xs text-slate-500">Edita o elimina registros.</div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[980px] w-full text-sm">
                <thead class="bg-slate-50 text-slate-700">
                    <tr class="text-left">
                        <th class="px-4 py-3 font-semibold">DNI</th>
                        <th class="px-4 py-3 font-semibold">Operación</th>
                        <th class="px-4 py-3 font-semibold">Fecha</th>
                        <th class="px-4 py-3 font-semibold">Moneda</th>
                        <th class="px-4 py-3 font-semibold text-right">Monto</th>
                        <th class="px-4 py-3 font-semibold">Gestor</th>
                        <th class="px-4 py-3 font-semibold text-center w-[160px]">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200">
                    @forelse($pagos as $p)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3 text-slate-900">{{ $p->DNI }}</td>
                            <td class="px-4 py-3 text-slate-900">{{ $p->OPERACION }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ \Carbon\Carbon::parse($p->FECHA)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $p->MONEDA }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-900">{{ number_format($p->MONTO, 2) }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $p->GESTOR }}</td>

                            <td class="px-4 py-3 text-center">
                                <div x-data="{ open:false }" class="inline-flex items-center gap-2">

                                    {{-- EDIT --}}
                                    <button type="button"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition"
                                            @click="open = true">
                                        <svg class="h-4 w-4 text-slate-700" viewBox="0 0 24 24" fill="none">
                                            <path d="M12 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                        </svg>
                                    </button>

                                    {{-- DELETE --}}
                                    <form method="POST"
                                          action="{{ route('pagos.propia4.destroy', $p->id) }}"
                                          onsubmit="return confirm('¿Eliminar este pago?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 transition">
                                            <svg class="h-4 w-4 text-red-700" viewBox="0 0 24 24" fill="none">
                                                <path d="M3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M8 6V4h8v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M6 6l1 16h10l1-16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                    </form>

                                    {{-- MODAL EDIT (PUT) --}}
                                    <div x-show="open" x-transition.opacity style="display:none;"
                                         class="fixed inset-0 z-50 flex items-center justify-center px-4"
                                         aria-modal="true" role="dialog">
                                        <div class="absolute inset-0 bg-slate-900/50" @click="open=false"></div>

                                        <div class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-xl">
                                            <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-4 py-3">
                                                <div>
                                                    <div class="text-sm font-semibold text-slate-900">Editar pago</div>
                                                    <div class="text-xs text-slate-500">DNI y Operación no se modifican.</div>
                                                </div>
                                                <button class="rounded-xl border border-slate-200 bg-white p-2 hover:bg-slate-50" @click="open=false">
                                                    <svg class="h-4 w-4 text-slate-700" viewBox="0 0 24 24" fill="none">
                                                        <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                    </svg>
                                                </button>
                                            </div>

                                            <form method="POST" action="{{ route('pagos.propia4.update', $p->id) }}">
                                                @csrf
                                                @method('PUT')

                                                <div class="px-4 py-4 space-y-3">
                                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                        <div>
                                                            <label class="text-xs font-semibold text-slate-700">DNI</label>
                                                            <input type="text" disabled value="{{ $p->DNI }}"
                                                                   class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                                        </div>
                                                        <div>
                                                            <label class="text-xs font-semibold text-slate-700">Operación</label>
                                                            <input type="text" disabled value="{{ $p->OPERACION }}"
                                                                   class="mt-1 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                                        </div>
                                                    </div>

                                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                        <div>
                                                            <label class="text-xs font-semibold text-slate-700">Fecha</label>
                                                            <input type="date" name="fecha" value="{{ $p->FECHA }}" required
                                                                   class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100">
                                                        </div>
                                                        <div>
                                                            <label class="text-xs font-semibold text-slate-700">Moneda</label>
                                                            <input type="text" name="moneda" value="{{ $p->MONEDA }}"
                                                                   class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100">
                                                        </div>
                                                    </div>

                                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                                        <div>
                                                            <label class="text-xs font-semibold text-slate-700">Monto</label>
                                                            <input type="number" step="0.01" name="monto" value="{{ $p->MONTO }}" required
                                                                   class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-right outline-none focus:ring-4 focus:ring-slate-100">
                                                        </div>
                                                        <div>
                                                            <label class="text-xs font-semibold text-slate-700">Gestor</label>
                                                            <input type="text" name="gestor" value="{{ $p->GESTOR }}"
                                                                   class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex items-center justify-end gap-2 border-t border-slate-200 px-4 py-3">
                                                    <button type="button"
                                                            class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 transition"
                                                            @click="open=false">
                                                        Cancelar
                                                    </button>
                                                    <button type="submit"
                                                            class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 transition">
                                                        Guardar cambios
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-sm text-slate-500">
                                No se encontraron pagos para los filtros seleccionados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-2 border-t border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-xs text-slate-500">
                @if($pagos->count())
                    Mostrando {{ $pagos->firstItem() }} al {{ $pagos->lastItem() }} de {{ $pagos->total() }} registros
                @else
                    Sin registros para mostrar.
                @endif
            </div>

            <div class="text-sm">
                {{ $pagos->onEachSide(1)->links() }}
            </div>
        </div>
    </div>

</div>
@endsection
