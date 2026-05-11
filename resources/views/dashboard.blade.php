@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Resumen operativo de pagos, gestiones y estado general.')

@push('styles')
    @vite(['resources/css/dashboard.css'])
@endpush

@section('content')
@php
    $stats = [
        ['label' => 'Pagos registrados hoy', 'value' => number_format($pagosRegistradosHoy), 'hint' => 'Por fecha de registro'],
        ['label' => 'Monto pagado hoy', 'value' => number_format($montoPagadoHoy, 2), 'hint' => 'Segun fecha de pago'],
        ['label' => 'Gestiones cargadas hoy', 'value' => number_format($gestionesCargadasHoy), 'hint' => 'Por fecha de carga'],
        ['label' => 'Estado general', 'value' => $estadoSistema['estado'], 'hint' => $estadoSistema['detalle']],
    ];

    $maxPagoDia = max((float) ($pagosPorCarteraDia->max('total') ?? 0), 1);
    $maxPagoMes = max((float) ($pagosPorCarteraMes->max('total') ?? 0), 1);
    $maxGestionDia = max((int) ($gestionesPorCarteraDia->max('total') ?? 0), 1);
@endphp

<div class="dashboard-page">
    <div class="dashboard-head">
        <div>
            <div class="dashboard-title">Resumen de hoy</div>
            <div class="dashboard-subtitle">
                {{ now()->format('d/m/Y') }} · {{ session('usuario', 'Usuario') }}
            </div>
        </div>

        <div class="dashboard-head-right">
            <a href="{{ route('pagos.index') }}" class="btn-secondary">
                Pagos
            </a>
            <a href="{{ route('gestiones.index') }}" class="btn-secondary">
                Gestiones
            </a>
        </div>
    </div>

    <section class="dashboard-section">
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

    <section class="dashboard-section">
        <div class="dashboard-grid-two">
            <div class="dashboard-panel">
                <div class="dashboard-panel-head">
                    <div>
                        <div class="dashboard-section-title">Pagos por cartera del dia</div>
                        <div class="dashboard-subtitle">Cantidad y monto pagado hoy.</div>
                    </div>
                </div>

                <div class="dashboard-list">
                    @forelse($pagosPorCarteraDia as $row)
                        <div class="dashboard-row">
                            <div class="min-w-0">
                                <div class="dashboard-row-title">{{ $row->cartera }}</div>
                                <div class="dashboard-row-subtitle">{{ number_format($row->cantidad) }} pagos</div>
                            </div>
                            <div class="dashboard-row-metric">{{ number_format((float) $row->total, 2) }}</div>
                            <div class="dashboard-bar">
                                <span style="width: {{ min(100, ((float) $row->total / $maxPagoDia) * 100) }}%"></span>
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-empty">Sin registros</div>
                    @endforelse
                </div>
            </div>

            <div class="dashboard-panel">
                <div class="dashboard-panel-head">
                    <div>
                        <div class="dashboard-section-title">Pagos por cartera del mes</div>
                        <div class="dashboard-subtitle">Acumulado del mes actual.</div>
                    </div>
                </div>

                <div class="dashboard-list">
                    @forelse($pagosPorCarteraMes as $row)
                        <div class="dashboard-row">
                            <div class="min-w-0">
                                <div class="dashboard-row-title">{{ $row->cartera }}</div>
                                <div class="dashboard-row-subtitle">{{ number_format($row->cantidad) }} pagos</div>
                            </div>
                            <div class="dashboard-row-metric">{{ number_format((float) $row->total, 2) }}</div>
                            <div class="dashboard-bar">
                                <span style="width: {{ min(100, ((float) $row->total / $maxPagoMes) * 100) }}%"></span>
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-empty">Sin registros</div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-grid-two">
            <div class="dashboard-panel">
                <div class="dashboard-panel-head">
                    <div>
                        <div class="dashboard-section-title">Gestiones por cartera del dia</div>
                        <div class="dashboard-subtitle">Segun fecha de gestion.</div>
                    </div>
                </div>

                <div class="dashboard-list">
                    @forelse($gestionesPorCarteraDia as $row)
                        <div class="dashboard-row">
                            <div class="min-w-0">
                                <div class="dashboard-row-title">{{ $row->cartera }}</div>
                                <div class="dashboard-row-subtitle">Gestiones</div>
                            </div>
                            <div class="dashboard-row-metric">{{ number_format($row->total) }}</div>
                            <div class="dashboard-bar">
                                <span style="width: {{ min(100, ((int) $row->total / $maxGestionDia) * 100) }}%"></span>
                            </div>
                        </div>
                    @empty
                        <div class="dashboard-empty">Sin registros</div>
                    @endforelse
                </div>
            </div>

            <div class="dashboard-panel">
                <div class="dashboard-panel-head">
                    <div>
                        <div class="dashboard-section-title">Sistema</div>
                        <div class="dashboard-subtitle">Ultima carga y cartera destacada.</div>
                    </div>
                </div>

                <div class="system-summary">
                    <div>
                        <span>Ultima carga registrada</span>
                        <strong>
                            @if($ultimaCarga)
                                {{ $ultimaCarga['tipo'] }} · {{ \Carbon\Carbon::parse($ultimaCarga['fecha'])->format('d/m/Y H:i') }}
                            @else
                                Sin registros
                            @endif
                        </strong>
                    </div>

                    <div>
                        <span>Cartera con mayor pago del dia</span>
                        <strong>
                            @if($carteraMayorPagoDia)
                                {{ $carteraMayorPagoDia->cartera }} · {{ number_format((float) $carteraMayorPagoDia->total, 2) }}
                            @else
                                Sin registros
                            @endif
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
