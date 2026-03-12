@extends('layouts.app')

@section('title', 'Panel principal')
@section('page_title', 'Panel principal')
@section('page_subtitle', 'Accesos rápidos, estado del día y reportes.')

@push('styles')
    @vite(['resources/css/dashboard.css'])
@endpush

@section('content')
@php
    $quickActions = [
        [
            'title' => 'Cargar Gestiones',
            'subtitle' => 'Propia 1 y 2',
            'href' => route('gestiones.propia12.form'),
            'icon' => 'gestiones',
        ],
        [
            'title' => 'Cargar Pagos',
            'subtitle' => 'Propia 1 y 2',
            'href' => route('pagos.index', 'propia12'),
            'icon' => 'pagos',
        ],
        [
            'title' => 'Tipificaciones',
            'subtitle' => 'Parámetros del sistema',
            'href' => route('parametros.tipificaciones.index'),
            'icon' => 'check',
        ],
    ];

    $stats = [
        ['label' => 'Gestiones cargadas', 'value' => '—', 'hint' => 'Total del día'],
        ['label' => 'Pagos cargados', 'value' => '—', 'hint' => 'Total del día'],
        ['label' => 'Última carga', 'value' => '—', 'hint' => 'Fecha / hora'],
        ['label' => 'Estado', 'value' => 'OK', 'hint' => 'Sistema operativo'],
    ];

    $rg12 = 'reportes.gestiones.propia12';
    $rp   = 'reportes.pagos.index';
@endphp

<div class="dashboard-page">

    @include('components.alerts')

    <div class="dashboard-head">
        <div>
            <div class="dashboard-title">Panel de Reportes</div>
            <div class="dashboard-subtitle">
                Selecciona una acción rápida o entra a un módulo desde el menú.
            </div>
        </div>

        <div class="dashboard-head-right">
            <span class="dashboard-chip">
                Hoy: {{ now()->format('d/m/Y') }}
            </span>

            @if(session()->has('usuario'))
                <span class="dashboard-chip-user">
                    <span class="dashboard-chip-dot"></span>
                    <span class="font-semibold">{{ session('usuario') }}</span>
                </span>
            @endif
        </div>
    </div>

    <section class="dashboard-section">
        <div class="dashboard-section-title">Acciones rápidas</div>

        <div class="quick-grid">
            @foreach($quickActions as $item)
                <a href="{{ $item['href'] }}" class="quick-card">
                    <div class="quick-card-row">
                        <div class="quick-card-icon">
                            @if($item['icon'] === 'gestiones')
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M8 6h13M8 12h13M8 18h13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M3 6h.01M3 12h.01M3 18h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                                </svg>
                            @elseif($item['icon'] === 'pagos')
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 7h18v10H3V7Z" stroke="currentColor" stroke-width="2"/>
                                    <path d="M7 11h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            @else
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M9 11l3 3L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            @endif
                        </div>

                        <div>
                            <div class="quick-card-title">{{ $item['title'] }}</div>
                            <div class="quick-card-subtitle">{{ $item['subtitle'] }}</div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-section-title">Resumen de hoy</div>

        <div class="stats-grid">
            @foreach($stats as $item)
                <div class="stat-card">
                    <div class="stat-label">{{ $item['label'] }}</div>
                    <div class="stat-value">{{ $item['value'] }}</div>
                    <div class="stat-hint">{{ $item['hint'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="report-box">
        <div class="report-box-row">
            <div>
                <div class="dashboard-section-title">Reportes</div>
                <div class="dashboard-subtitle">Consulta rápida por cartera.</div>
            </div>

            <div class="report-actions">
                <a href="{{ \Illuminate\Support\Facades\Route::has($rg12) ? route($rg12) : '#' }}"
                   class="btn-secondary">
                    Reporte Gestiones P1-2
                </a>

                <a href="{{ \Illuminate\Support\Facades\Route::has($rp) ? route($rp) : '#' }}"
                   class="btn-primary w-auto px-4">
                    Reporte Pagos
                </a>
            </div>
        </div>
    </section>

</div>
@endsection