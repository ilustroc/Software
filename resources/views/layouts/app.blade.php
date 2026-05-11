<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'ImpulseGo Reportes')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('img/logotipo.png') }}">

    @vite(['resources/css/panel.css', 'resources/js/app.js'])
    @stack('styles')
</head>

@php
    $isDashboard = request()->routeIs('dashboard') || request()->routeIs('dashboard.index');
    $openGestiones = request()->routeIs('gestiones.*');
    $openPagos     = request()->routeIs('pagos.*');
    $openReportes  = request()->routeIs('reportes.*');
    $openConfig    = request()->routeIs('configuracion.*');

    $layoutHoy = now()->toDateString();
    $layoutPagosHoy = \App\Models\Pago::query()->whereDate('fecha', $layoutHoy)->count();
    $layoutGestionesHoy = \App\Models\Gestion::query()->whereDate('fecha_gestion', $layoutHoy)->count();
    $layoutCarteraMayor = \Illuminate\Support\Facades\DB::table('pagos')
        ->join('carteras', 'carteras.id', '=', 'pagos.cartera_id')
        ->whereNull('pagos.deleted_at')
        ->whereDate('pagos.fecha', $layoutHoy)
        ->select('carteras.nombre', \Illuminate\Support\Facades\DB::raw('sum(pagos.monto) as total'))
        ->groupBy('carteras.id', 'carteras.nombre')
        ->orderByDesc('total')
        ->first();

    $linkBase   = 'nav-link';
    $linkActive = 'is-active';
    $subBase    = 'nav-sublink';
    $subActive  = 'is-active';
@endphp

<body class="panel-page">
<div
    x-data="sidebarLayout({
        openGestiones: @js($openGestiones),
        openPagos: @js($openPagos),
        openReportes: @js($openReportes),
        openConfig: @js($openConfig)
    })"
    class="panel-shell"
>
    {{-- Overlay mobile --}}
    <div
        x-cloak
        x-show="mobileOpen"
        x-transition.opacity
        class="panel-overlay lg:hidden"
        @click="closeSidebar()"></div>

    {{-- Sidebar --}}
    <aside
        class="panel-sidebar"
        :class="mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        <div class="panel-sidebar-inner">

            {{-- Marca --}}
            <div class="panel-brand">
                <div class="panel-brand-row">
                    <img src="{{ asset('img/logotipo.png') }}" alt="ImpulseGo" class="panel-brand-mark">
                    <div class="min-w-0">
                        <div class="panel-brand-title">ImpulseGo</div>
                        <div class="panel-brand-subtitle">Panel administrativo</div>
                    </div>
                </div>
            </div>

            {{-- Navegación --}}
            <nav class="panel-nav">
                <div class="nav-section">
                    <a href="{{ route('dashboard') }}"
                       class="{{ $linkBase }} {{ $isDashboard ? $linkActive : '' }}">
                        <span class="nav-link-left">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="none">
                                <path d="M4 13h6V4H4v9ZM14 20h6V4h-6v16ZM4 20h6v-3H4v3Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            </svg>
                            <span>Dashboard</span>
                        </span>
                    </a>
                </div>

                <div class="nav-section">
                    <a href="{{ route('gestiones.index') }}"
                       class="{{ $linkBase }} {{ $openGestiones ? $linkActive : '' }}">
                        <span class="nav-link-left">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="none">
                                <path d="M16 2H8a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M9 6h6M9 10h6M9 14h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Cargas de Gestiones</span>
                        </span>
                    </a>
                </div>

                <div class="nav-section">
                    <a href="{{ route('pagos.index') }}"
                       class="{{ $linkBase }} {{ $openPagos ? $linkActive : '' }}">
                        <span class="nav-link-left">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="none">
                                <path d="M3 7h18v10H3V7Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M7 11h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Cargas de Pagos</span>
                        </span>
                    </a>
                </div>

                <div class="nav-section">
                    <button
                        type="button"
                        class="{{ $linkBase }} {{ $openReportes ? $linkActive : '' }}"
                        @click="toggle('openReportes')"
                    >
                        <span class="nav-link-left">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="none">
                                <path d="M4 19V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14" stroke="currentColor" stroke-width="2"/>
                                <path d="M8 11h8M8 15h6M8 7h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Reportes</span>
                        </span>

                        <svg class="nav-chevron" :class="openReportes ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none">
                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div x-cloak x-show="openReportes" x-collapse class="nav-submenu">
                        <a href="{{ route('reportes.pagos.index') }}"
                           class="{{ $subBase }} {{ request()->routeIs('reportes.pagos.*') ? $subActive : '' }}">
                            Reporte de pagos
                        </a>

                        <a href="{{ route('reportes.gestiones.index') }}"
                           class="{{ $subBase }} {{ request()->routeIs('reportes.gestiones.*') ? $subActive : '' }}">
                            Reporte de gestiones
                        </a>
                    </div>
                </div>

                <div class="nav-divider"></div>

                <div class="nav-section">
                    <div class="nav-subtitle px-3">Configuración</div>

                    <a href="{{ route('configuracion.carteras.index') }}"
                       class="{{ $linkBase }} mt-1 {{ request()->routeIs('configuracion.carteras.*') ? $linkActive : '' }}">
                        <span class="nav-link-left">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="none">
                                <path d="M9 11l3 3L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Carteras</span>
                        </span>
                    </a>

                    <a href="{{ route('configuracion.tipificaciones.index') }}"
                       class="{{ $linkBase }} mt-1 {{ request()->routeIs('configuracion.tipificaciones.*') ? $linkActive : '' }}">
                        <span class="nav-link-left">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="none">
                                <path d="M9 11l3 3L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Tipificaciones</span>
                        </span>
                    </a>
                </div>
            </nav>

            {{-- Logout --}}
            @if(session()->has('usuario'))
                <div class="panel-sidebar-footer">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="panel-logout-btn">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                <path d="M10 17l5-5-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M21 19V5a2 2 0 0 0-2-2h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Cerrar sesión</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </aside>

    {{-- Main --}}
    <main class="panel-main">
        <header class="panel-topbar">
            <div class="panel-topbar-inner">
                <div class="flex items-start gap-3">
                    <button
                        type="button"
                        class="panel-menu-btn lg:hidden"
                        @click="mobileOpen = true"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>

                    <div>
                        <h1 class="panel-page-title">
                            @yield('page_title', 'Panel de control')
                        </h1>
                        <p class="panel-page-subtitle">
                            @yield('page_subtitle', 'Gestión de cargas y reportes.')
                        </p>
                    </div>
                </div>

                <div class="panel-topbar-right">
                    <div class="panel-mini-summary">
                        <span>Pagos hoy: <strong>{{ number_format($layoutPagosHoy) }}</strong></span>
                        <span>Gestiones hoy: <strong>{{ number_format($layoutGestionesHoy) }}</strong></span>
                        <span>Mayor pago: <strong>{{ $layoutCarteraMayor->nombre ?? 'Sin registros' }}</strong></span>
                    </div>

                    <span class="panel-topbar-chip">
                        {{ now()->format('d/m/Y') }}
                    </span>
                </div>
            </div>
        </header>

        <section class="panel-content">
            @include('components.alerts')
            @yield('content')
        </section>
    </main>

    @stack('scripts')
</div>
</body>
</html>
