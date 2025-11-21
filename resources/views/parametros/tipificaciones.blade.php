@extends('layouts.app')

@section('title', 'Parámetros - Tipificaciones')
@section('page_title', 'Parámetros · Tipificaciones')
@section('page_subtitle', 'Configura el resultado, MC, peso y orden para cada tipificación.')

@section('content')

    {{-- ALERTAS --}}
    @if(session('msg'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('msg') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif


    {{-- FORM NUEVA TIPIFICACIÓN --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header">
            <strong>Nueva tipificación</strong>
        </div>
        <div class="card-body">
            <form method="POST"
                  action="{{ route('parametros.tipificaciones.store') }}"
                  class="row g-3 align-items-end">
                @csrf

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tipificación</label>
                    <input type="text"
                           name="tipificacion"
                           class="form-control form-control-sm"
                           required>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Resultado</label>
                    <input type="text"
                           name="resultado"
                           class="form-control form-control-sm"
                           required
                           placeholder="CONTACTO / NO CONTACTO / ...">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">MC</label>
                    <input type="text"
                           name="mc"
                           class="form-control form-control-sm"
                           required
                           placeholder="1 = CD+">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Peso</label>
                    <input type="number"
                           name="peso"
                           class="form-control form-control-sm"
                           required
                           min="0">
                </div>

                <div class="col-md-1 d-grid">
                    <button class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-circle me-1"></i> Agregar
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- TABLA TIPIFICACIONES --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header">
            <strong>Listado de tipificaciones</strong>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover table-striped mb-0 align-middle">
                    <thead class="table-light">
                        <tr class="text-nowrap">
                            <th style="width: 60px;">ID</th>
                            <th>Tipificación</th>
                            <th>Resultado</th>
                            <th>MC</th>
                            <th class="text-end">Peso</th>
                            <th class="text-end">Orden</th>
                            <th class="text-center" style="width: 140px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tipificaciones as $t)
                            <tr>
                                {{-- ID --}}
                                <td class="text-muted">
                                    {{ $t->id }}
                                </td>

                                {{-- Tipificación --}}
                                <td>
                                    <input type="text"
                                           name="tipificacion"
                                           form="form-tip-{{ $t->id }}"
                                           value="{{ $t->tipificacion }}"
                                           class="form-control form-control-sm">
                                </td>

                                {{-- Resultado --}}
                                <td>
                                    <input type="text"
                                           name="resultado"
                                           form="form-tip-{{ $t->id }}"
                                           value="{{ $t->resultado }}"
                                           class="form-control form-control-sm">
                                </td>

                                {{-- MC --}}
                                <td>
                                    <input type="text"
                                           name="mc"
                                           form="form-tip-{{ $t->id }}"
                                           value="{{ $t->mc }}"
                                           class="form-control form-control-sm">
                                </td>

                                {{-- Peso --}}
                                <td>
                                    <input type="number"
                                           name="peso"
                                           form="form-tip-{{ $t->id }}"
                                           value="{{ $t->peso }}"
                                           class="form-control form-control-sm text-end">
                                </td>

                                {{-- Orden --}}
                                <td>
                                    <input type="number"
                                           name="orden"
                                           form="form-tip-{{ $t->id }}"
                                           value="{{ $t->orden }}"
                                           class="form-control form-control-sm text-end">
                                </td>

                                {{-- Acciones --}}
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        {{-- FORM ACTUALIZAR --}}
                                        <form id="form-tip-{{ $t->id }}"
                                              method="POST"
                                              action="{{ route('parametros.tipificaciones.update', $t) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-outline-primary"
                                                    title="Guardar cambios">
                                                <i class="bi bi-save"></i>
                                            </button>
                                        </form>

                                        {{-- FORM ELIMINAR --}}
                                        <form method="POST"
                                              action="{{ route('parametros.tipificaciones.destroy', $t) }}"
                                              onsubmit="return confirm('¿Eliminar esta tipificación?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-outline-danger"
                                                    title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
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


