@extends('layouts.app')

@section('title', 'Configuracion - Carteras')
@section('page_title', 'Carteras')
@section('page_subtitle', 'Administra las carteras disponibles para pagos, gestiones y reportes.')

@push('styles')
    @vite(['resources/css/pagos.css'])
@endpush

@section('content')
<div
    class="pagos-page"
    x-data="{ carteraModalOpen: @js($errors->cartera->any()) }"
>
    <section class="pagos-card">
        <div class="pagos-card-header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="pagos-title">Carteras registradas</h2>
                <p class="pagos-subtitle">Las carteras activas aparecen automaticamente en los selectores.</p>
            </div>

            <button type="button" class="btn-primary w-auto px-4" @click="carteraModalOpen = true">
                Agregar cartera
            </button>
        </div>

        <div class="data-table-wrap">
            <table class="data-table min-w-[720px]">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Slug</th>
                        <th>Estado</th>
                        <th class="text-right">Pagos</th>
                        <th class="text-right">Gestiones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($carteras as $cartera)
                        <tr>
                            <td class="font-medium text-slate-900">{{ $cartera->nombre }}</td>
                            <td class="text-slate-600">{{ $cartera->slug }}</td>
                            <td>
                                <span class="{{ $cartera->activa ? 'status-pill status-pill-success' : 'status-pill status-pill-muted' }}">
                                    {{ $cartera->activa ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="text-right text-slate-700">{{ number_format($cartera->pagos()->count()) }}</td>
                            <td class="text-right text-slate-700">{{ number_format($cartera->gestiones()->count()) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="data-table-empty">Sin registros</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @include('carteras.partials.create-modal')
</div>
@endsection
