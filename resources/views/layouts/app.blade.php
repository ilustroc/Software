{{-- resources/views/layouts/app.blade.php --}}
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'ImpulseGo Reportes')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Tailwind (CDN para arrancar rápido) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Alpine.js (para acordeones / sidebar mobile) --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('styles')
</head>

@php
    // Abrir acordeones según la ruta actual
    $openGestiones = request()->routeIs('gestiones.*');
    $openPagos     = request()->routeIs('pagos.*');
    $openCartera   = request()->routeIs('cartera.*') || request()->routeIs('cargas.cartera.*');
    $openReportes  = request()->routeIs('reportes.*');
@endphp

<body class="bg-slate-50 text-slate-900">
<div
    x-data="{
        mobileOpen: false,
        openGestiones: {{ $openGestiones ? 'true' : 'false' }},
        openPagos: {{ $openPagos ? 'true' : 'false' }},
        openCartera: {{ $openCartera ? 'true' : 'false' }},
        openReportes: {{ $openReportes ? 'true' : 'false' }},
    }"
    class="min-h-screen"
>

    {{-- Overlay mobile --}}
    <div
        x-show="mobileOpen"
        x-transition.opacity
        class="fixed inset-0 z-40 bg-slate-900/50 lg:hidden"
        @click="mobileOpen = false"
        style="display:none;"
    ></div>

    <div class="flex min-h-screen">

        {{-- SIDEBAR --}}
        <aside
            class="fixed inset-y-0 left-0 z-50 w-[280px] lg:static lg:z-auto
                   -translate-x-full lg:translate-x-0 transition-transform duration-200
                   bg-gradient-to-b from-slate-950 via-slate-950 to-slate-900 text-slate-100"
            :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="h-full flex flex-col px-4 py-4">

                {{-- Brand --}}
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-lg font-semibold tracking-tight">ImpulseGo</div>
                        <div class="text-xs text-slate-400">Panel de reportes</div>
                    </div>

                    <div class="shrink-0">
                        <span class="inline-flex items-center rounded-lg border border-sky-400/30 bg-sky-500/10 px-2 py-1 text-xs font-semibold text-sky-200">
                            v1.0
                        </span>
                    </div>
                </div>

                {{-- User pill --}}
                @if(session()->has('usuario'))
                    <div class="mt-4 rounded-xl border border-white/10 bg-white/5 px-3 py-2">
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            <span class="text-sm font-semibold">{{ session('usuario') }}</span>
                        </div>
                    </div>
                @endif

                {{-- Menú --}}
                <nav class="mt-4 flex-1 space-y-1 overflow-y-auto pr-1">

                    {{-- Helper classes --}}
                    @php
                        $linkBase = "group flex items-center gap-2 rounded-xl px-3 py-2 text-sm transition";
                        $linkIdle = "text-slate-300 hover:text-white hover:bg-white/5";
                        $linkActive = "bg-white/10 text-white ring-1 ring-white/10";
                        $subLinkBase = "flex items-center gap-2 rounded-lg px-3 py-1.5 text-[13px] transition";
                        $subIdle = "text-slate-300/90 hover:text-white hover:bg-white/5";
                        $subActive = "bg-sky-500/10 text-sky-100 ring-1 ring-sky-400/20";
                    @endphp

                    {{-- Cargas de Gestiones --}}
                    <div class="rounded-2xl">
                        <button
                            type="button"
                            class="{{ $linkBase }} w-full justify-between {{ $openGestiones ? $linkActive : $linkIdle }}"
                            @click="openGestiones = !openGestiones"
                        >
                            <span class="flex items-center gap-2">
                                {{-- icon --}}
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none">
                                    <path d="M16 2H8a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2Z" stroke="currentColor" stroke-width="2"/>
                                    <path d="M9 6h6M9 10h6M9 14h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <span class="font-semibold">Cargas de Gestiones</span>
                            </span>

                            <svg class="h-4 w-4 transition" :class="openGestiones ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none">
                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>

                        <div x-show="openGestiones" x-collapse style="display:none;" class="mt-1 pl-1 space-y-1">
                            <a href="{{ route('gestiones.propia12.form') }}"
                               class="{{ $subLinkBase }} {{ request()->routeIs('gestiones.propia12.*') ? $subActive : $subIdle }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400/70"></span>
                                Propia 1 y 2
                            </a>
                            <a href="{{ route('gestiones.propia3.form') }}"
                               class="{{ $subLinkBase }} {{ request()->routeIs('gestiones.propia3.*') ? $subActive : $subIdle }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400/70"></span>
                                Propia 3
                            </a>
                            <a href="{{ route('gestiones.propia4.form') }}"
                               class="{{ $subLinkBase }} {{ request()->routeIs('gestiones.propia4.*') ? $subActive : $subIdle }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400/70"></span>
                                Propia 4
                            </a>
                            <a href="{{ route('gestiones.amd') }}"
                               class="{{ $subLinkBase }} {{ request()->routeIs('gestiones.amd') ? $subActive : $subIdle }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400/70"></span>
                                AMD
                            </a>
                            <a href="{{ route('gestiones.abandonados') }}"
                               class="{{ $subLinkBase }} {{ request()->routeIs('gestiones.abandonados') ? $subActive : $subIdle }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400/70"></span>
                                Abandonados
                            </a>
                        </div>
                    </div>

                    {{-- Cargas de Pagos --}}
                    <div class="rounded-2xl">
                        <button
                            type="button"
                            class="{{ $linkBase }} w-full justify-between {{ $openPagos ? $linkActive : $linkIdle }}"
                            @click="openPagos = !openPagos"
                        >
                            <span class="flex items-center gap-2">
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 7h18v10H3V7Z" stroke="currentColor" stroke-width="2"/>
                                    <path d="M7 11h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <span class="font-semibold">Cargas de Pagos</span>
                            </span>
                            <svg class="h-4 w-4 transition" :class="openPagos ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none">
                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>

                        <div x-show="openPagos" x-collapse style="display:none;" class="mt-1 pl-1 space-y-1">
                            <a href="{{ route('pagos.propia12.index') }}"
                               class="{{ $subLinkBase }} {{ request()->routeIs('pagos.propia12.*') ? $subActive : $subIdle }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400/70"></span>
                                Propia 1 y 2
                            </a>
                            <a href="{{ route('pagos.propia3.index') }}"
                               class="{{ $subLinkBase }} {{ request()->routeIs('pagos.propia3.*') ? $subActive : $subIdle }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400/70"></span>
                                Propia 3
                            </a>
                            <a href="{{ route('pagos.propia4.index') }}"
                               class="{{ $subLinkBase }} {{ request()->routeIs('pagos.propia4.*') ? $subActive : $subIdle }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400/70"></span>
                                Propia 4
                            </a>
                        </div>
                    </div>

                    {{-- Cargas de Cartera --}}
                    <div class="rounded-2xl">
                        <button
                            type="button"
                            class="{{ $linkBase }} w-full justify-between {{ $openCartera ? $linkActive : $linkIdle }}"
                            @click="openCartera = !openCartera"
                        >
                            <span class="flex items-center gap-2">
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none">
                                    <path d="M4 6h16M4 12h16M4 18h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <span class="font-semibold">Cargas de Cartera</span>
                            </span>
                            <svg class="h-4 w-4 transition" :class="openCartera ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none">
                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>

                        <div x-show="openCartera" x-collapse style="display:none;" class="mt-1 pl-1 space-y-1">
                            <a href="#"
                               class="{{ $subLinkBase }} {{ $subIdle }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400/70"></span>
                                Propia 1 y 2
                            </a>
                            <a href="#"
                               class="{{ $subLinkBase }} {{ $subIdle }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400/70"></span>
                                Propia 3
                            </a>
                            <a href="#"
                               class="{{ $subLinkBase }} {{ $subIdle }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400/70"></span>
                                Propia 4
                            </a>
                        </div>
                    </div>

                    {{-- REPORTES (NUEVO) --}}
                    <div class="rounded-2xl">
                        <button
                            type="button"
                            class="{{ $linkBase }} w-full justify-between {{ $openReportes ? $linkActive : $linkIdle }}"
                            @click="openReportes = !openReportes"
                        >
                            <span class="flex items-center gap-2">
                                <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none">
                                    <path d="M4 19V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14" stroke="currentColor" stroke-width="2"/>
                                    <path d="M8 11h8M8 15h6M8 7h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                <span class="font-semibold">Reportes</span>
                            </span>
                            <svg class="h-4 w-4 transition" :class="openReportes ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none">
                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>

                        <div x-show="openReportes" x-collapse style="display:none;" class="mt-1 pl-1 space-y-2">

                            {{-- Subtitulo --}}
                            <div class="px-3 pt-1 text-[11px] uppercase tracking-wider text-slate-400">
                                Reporte de Gestiones
                            </div>

                            {{-- Para evitar error si aún no existen rutas, usamos Route::has --}}
                            @php
                                $rg12 = 'reportes.gestiones.propia12';
                                $rg3  = 'reportes.gestiones.propia3';
                                $rg4  = 'reportes.gestiones.propia4';
                            @endphp

                            <a href="{{ \Illuminate\Support\Facades\Route::has($rg12) ? route($rg12) : '#' }}"
                               class="{{ $subLinkBase }} {{ request()->routeIs($rg12) ? $subActive : $subIdle }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400/70"></span>
                                Gestiones Propia 1 y 2
                            </a>
                            <a href="{{ \Illuminate\Support\Facades\Route::has($rg3) ? route($rg3) : '#' }}"
                               class="{{ $subLinkBase }} {{ request()->routeIs($rg3) ? $subActive : $subIdle }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400/70"></span>
                                Gestiones Propia 3
                            </a>
                            <a href="{{ \Illuminate\Support\Facades\Route::has($rg4) ? route($rg4) : '#' }}"
                               class="{{ $subLinkBase }} {{ request()->routeIs($rg4) ? $subActive : $subIdle }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400/70"></span>
                                Gestiones Propia 4
                            </a>

                            <div class="px-3 pt-2 text-[11px] uppercase tracking-wider text-slate-400">
                                Reporte de Pagos
                            </div>

                            @php
                                $rp = 'reportes.pagos.index';
                            @endphp

                            <a href="{{ \Illuminate\Support\Facades\Route::has($rp) ? route($rp) : '#' }}"
                               class="{{ $subLinkBase }} {{ request()->routeIs('reportes.pagos.*') ? $subActive : $subIdle }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-slate-400/70"></span>
                                Reporte de Pagos
                            </a>

                        </div>
                    </div>

                    {{-- PARÁMETROS --}}
                    <div class="pt-3">
                        <div class="px-3 text-[11px] uppercase tracking-wider text-slate-500">Parámetros</div>

                        <a href="{{ route('parametros.tipificaciones.index') }}"
                           class="{{ $linkBase }} mt-2 {{ request()->routeIs('parametros.tipificaciones.*') ? $linkActive : $linkIdle }}">
                            <svg class="h-4 w-4 opacity-90" viewBox="0 0 24 24" fill="none">
                                <path d="M9 11l3 3L22 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <span class="font-semibold">Tipificaciones</span>
                        </a>
                    </div>

                </nav>

                {{-- Footer / Logout --}}
                @if(session()->has('usuario'))
                    <div class="pt-3 border-t border-white/10">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button
                                type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl
                                       border border-white/15 bg-white/5 px-3 py-2 text-sm font-semibold
                                       text-white hover:bg-white/10 transition"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                    <path d="M10 17l5-5-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M15 12H3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M21 19V5a2 2 0 0 0-2-2h-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                                Cerrar sesión
                            </button>
                        </form>
                    </div>
                @endif

            </div>
        </aside>

        {{-- MAIN --}}
        <main class="flex-1 lg:pl-0 lg:ml-0">
            {{-- Topbar --}}
            <div class="sticky top-0 z-30 border-b border-slate-200 bg-slate-50/80 backdrop-blur">
                <div class="mx-auto max-w-7xl px-4 py-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3">
                            {{-- Mobile button --}}
                            <button
                                type="button"
                                class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white hover:bg-slate-50"
                                @click="mobileOpen = true"
                            >
                                <svg class="h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="none">
                                    <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </button>

                            <div>
                                <div class="text-base font-semibold text-slate-900">
                                    @yield('page_title', 'Panel de control')
                                </div>
                                <div class="text-sm text-slate-500">
                                    @yield('page_subtitle', 'Gestión de cargas y reportes de propia.')
                                </div>
                            </div>
                        </div>

                        <div class="hidden sm:flex items-center gap-2">
                            <span class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-600">
                                {{ now()->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Content --}}
            <div class="mx-auto max-w-7xl px-4 py-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    @yield('content')
                </div>
            </div>
        </main>

    </div>

    @stack('scripts')
</div>
</body>
</html>
