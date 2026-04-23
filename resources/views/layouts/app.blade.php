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
    $openGestiones = request()->routeIs('gestiones.*');
    $openPagos     = request()->routeIs('pagos.*');
    $openReportes  = request()->routeIs('reportes.*');

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
        openReportes: @js($openReportes)
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
                    <button
                        type="button"
                        class="{{ $linkBase }} {{ $openGestiones ? $linkActive : '' }}"
                        @click="toggle('openGestiones')"
                    >
                        <span class="nav-link-left">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="none">
                                <path d="M16 2H8a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M9 6h6M9 10h6M9 14h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Cargas de Gestiones</span>
                        </span>

                        <svg class="nav-chevron" :class="openGestiones ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none">
                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div x-cloak x-show="openGestiones" x-collapse class="nav-submenu">
                        <a href="{{ route('gestiones.propia12.form') }}"
                           class="{{ $subBase }} {{ request()->routeIs('gestiones.propia12.*') ? $subActive : '' }}">
                            Propia 1 y 2
                        </a>

                        <a href="{{ route('gestiones.propia3.form') }}"
                           class="{{ $subBase }} {{ request()->routeIs('gestiones.propia3.*') ? $subActive : '' }}">
                            Propia 3
                        </a>

                        <a href="{{ route('gestiones.kpi.form') }}"
                           class="{{ $subBase }} {{ request()->routeIs('gestiones.kpi.*') ? $subActive : '' }}">
                            Propia 4
                        </a>

                        <a href="{{ route('gestiones.amd') }}"
                           class="{{ $subBase }} {{ request()->routeIs('gestiones.amd') ? $subActive : '' }}">
                            AMD
                        </a>

                        <a href="{{ route('gestiones.ivr') }}"
                        class="{{ $subBase }} {{ request()->routeIs('gestiones.ivr') ? $subActive : '' }}">
                            IVR
                        </a>
                        
                        <a href="{{ route('gestiones.abandonados') }}"
                           class="{{ $subBase }} {{ request()->routeIs('gestiones.abandonados') ? $subActive : '' }}">
                            Abandonados
                        </a>
                    </div>
                </div>

                <div class="nav-section">
                    <button
                        type="button"
                        class="{{ $linkBase }} {{ $openPagos ? $linkActive : '' }}"
                        @click="toggle('openPagos')"
                    >
                        <span class="nav-link-left">
                            <svg class="nav-icon" viewBox="0 0 24 24" fill="none">
                                <path d="M3 7h18v10H3V7Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M7 11h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span>Cargas de Pagos</span>
                        </span>

                        <svg class="nav-chevron" :class="openPagos ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none">
                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <div x-cloak x-show="openPagos" x-collapse class="nav-submenu">
                        {{-- Propia 1 y 2 --}}
                        <a href="{{ route('pagos.index', 'propia12') }}"
                        class="{{ $subBase }} {{ request()->is('pagos/propia12*') ? $subActive : '' }}">
                            Propia 1 y 2
                        </a>

                        {{-- Propia 3 --}}
                        <a href="{{ route('pagos.index', 'propia3') }}"
                        class="{{ $subBase }} {{ request()->is('pagos/propia3*') ? $subActive : '' }}">
                            Propia 3
                        </a>

                        {{-- Propia 4 --}}
                        <a href="{{ route('pagos.index', 'propia4') }}"
                        class="{{ $subBase }} {{ request()->is('pagos/propia4*') ? $subActive : '' }}">
                            Propia 4
                        </a>
                    </div>
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
                        <div class="nav-subtitle">Gestiones</div>

                        {{-- Reporte Propia 1 y 2 --}}
                        <a href="{{ route('reportes.gestiones.index', 'propia12') }}"
                        class="{{ $subBase }} {{ request()->is('reportes/gestiones/propia12*') ? $subActive : '' }}">
                            Propia 1 y 2
                        </a>

                        {{-- Reporte Propia 3 --}}
                        <a href="{{ route('reportes.gestiones.index', 'propia3') }}"
                        class="{{ $subBase }} {{ request()->is('reportes/gestiones/propia3*') ? $subActive : '' }}">
                            Propia 3
                        </a>

                        {{-- Reporte Propia 4 --}}
                        <a href="{{ route('reportes.gestiones.index', 'propia4') }}"
                        class="{{ $subBase }} {{ request()->is('reportes/gestiones/propia4*') ? $subActive : '' }}">
                            Propia 4
                        </a>

                        <div class="nav-subtitle mt-2">Pagos</div>

                        {{-- Reporte General de Pagos --}}
                        <a href="{{ route('reportes.pagos.index') }}"
                        class="{{ $subBase }} {{ request()->routeIs('reportes.pagos.*') ? $subActive : '' }}">
                            Reporte de pagos
                        </a>
                    </div>
                </div>

                <div class="nav-divider"></div>

                <div class="nav-section">
                    <div class="nav-subtitle px-3">Configuración</div>

                    <a href="{{ route('parametros.tipificaciones.index') }}"
                       class="{{ $linkBase }} mt-1 {{ request()->routeIs('parametros.tipificaciones.*') ? $linkActive : '' }}">
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