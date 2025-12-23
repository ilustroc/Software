@extends('layouts.app')

@section('title', 'Parámetros - Tipificaciones')
@section('page_title', 'Parámetros · Tipificaciones')
@section('page_subtitle', 'Configura el resultado, MC y peso para cada tipificación.')

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

    {{-- NUEVA TIPIFICACIÓN --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div>
                <div class="text-sm font-semibold text-slate-900">Nueva tipificación</div>
                <div class="mt-0.5 text-xs text-slate-500">
                    Agrega una tipificación y define su clasificación.
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('parametros.tipificaciones.store') }}" class="mt-4">
            @csrf

            <div class="grid grid-cols-1 gap-3 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <label class="text-xs font-semibold text-slate-700">Tipificación</label>
                    <input type="text" name="tipificacion" required
                           class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100"
                           placeholder="Ej: PROMESA DE PAGO">
                </div>

                <div class="lg:col-span-4">
                    <label class="text-xs font-semibold text-slate-700">Resultado</label>
                    <input type="text" name="resultado" required
                           class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100"
                           placeholder="CONTACTO / NO CONTACTO / ...">
                </div>

                <div class="lg:col-span-2">
                    <label class="text-xs font-semibold text-slate-700">MC</label>
                    <input type="text" name="mc" required
                           class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100"
                           placeholder="1 = CD+">
                </div>

                <div class="lg:col-span-1">
                    <label class="text-xs font-semibold text-slate-700">Peso</label>
                    <input type="number" name="peso" required min="0"
                           class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-right outline-none focus:ring-4 focus:ring-slate-100"
                           placeholder="0">
                </div>

                <div class="lg:col-span-1 flex items-end">
                    <button type="submit"
                            class="w-full rounded-xl bg-slate-900 px-3 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 transition">
                        Agregar
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- TABLA --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
            <div>
                <div class="text-sm font-semibold text-slate-900">Listado de tipificaciones</div>
                <div class="text-xs text-slate-500">Edita valores en línea y guarda por fila.</div>
            </div>
        </div>

        {{-- IMPORTANTE: quitamos min-w enorme y usamos un min-w más corto --}}
        <div class="overflow-x-auto">
            <table class="min-w-[820px] w-full text-sm">
                <thead class="bg-slate-50 text-slate-700">
                    <tr class="text-left">
                        <th class="px-3 py-3 font-semibold w-[60px]">ID</th>
                        <th class="px-3 py-3 font-semibold">Tipificación</th>
                        <th class="px-3 py-3 font-semibold">Resultado</th>
                        <th class="px-3 py-3 font-semibold w-[140px]">MC</th>
                        <th class="px-3 py-3 font-semibold text-right w-[110px]">Peso</th>
                        <th class="px-3 py-3 font-semibold text-center w-[120px]">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200">
                    @forelse($tipificaciones as $t)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-3 py-3 text-slate-500">{{ $t->id }}</td>

                            <td class="px-3 py-3">
                                <input type="text" name="tipificacion" form="form-tip-{{ $t->id }}"
                                       value="{{ $t->tipificacion }}"
                                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100">
                            </td>

                            <td class="px-3 py-3">
                                <input type="text" name="resultado" form="form-tip-{{ $t->id }}"
                                       value="{{ $t->resultado }}"
                                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100">
                            </td>

                            <td class="px-3 py-3">
                                <input type="text" name="mc" form="form-tip-{{ $t->id }}"
                                       value="{{ $t->mc }}"
                                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm outline-none focus:ring-4 focus:ring-slate-100">
                            </td>

                            <td class="px-3 py-3">
                                <input type="number" name="peso" form="form-tip-{{ $t->id }}"
                                       value="{{ $t->peso }}"
                                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-right outline-none focus:ring-4 focus:ring-slate-100">
                            </td>

                            <td class="px-3 py-3">
                                <div class="flex items-center justify-center gap-2">

                                    <form id="form-tip-{{ $t->id }}" method="POST"
                                          action="{{ route('parametros.tipificaciones.update', $t) }}">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition"
                                                title="Guardar cambios">
                                            <svg class="h-4 w-4 text-slate-700" viewBox="0 0 24 24" fill="none">
                                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z" stroke="currentColor" stroke-width="2"/>
                                                <path d="M17 21v-8H7v8" stroke="currentColor" stroke-width="2"/>
                                                <path d="M7 3v5h8" stroke="currentColor" stroke-width="2"/>
                                            </svg>
                                        </button>
                                    </form>

                                    <form method="POST"
                                          action="{{ route('parametros.tipificaciones.destroy', $t) }}"
                                          onsubmit="return confirm('¿Eliminar esta tipificación?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 transition"
                                                title="Eliminar">
                                            <svg class="h-4 w-4 text-red-700" viewBox="0 0 24 24" fill="none">
                                                <path d="M3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M8 6V4h8v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M6 6l1 16h10l1-16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                                <path d="M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-sm text-slate-500">
                                No hay tipificaciones registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
