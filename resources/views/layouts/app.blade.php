<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'ImpulseGo Reportes')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- BOOTSTRAP --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    {{-- ICONOS (OPCIONAL) --}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        :root {
            --impulse-primary: #0d6efd;
            --impulse-dark: #111827;
            --impulse-dark-soft: #1f2933;
        }

        body {
            font-size: 0.92rem;
        }

        .app-shell {
            min-height: 100vh;
            background-color: #f3f4f6;
        }

        /* SIDEBAR */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--impulse-dark) 0%, #020617 60%);
            color: #e5e7eb;
        }

        .sidebar-brand {
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: 0.03em;
        }

        .sidebar-subtitle {
            font-size: 0.75rem;
            color: #9ca3af;
        }

        .sidebar .accordion-item {
            border: none;
            background-color: transparent;
        }

        .sidebar .accordion-button {
            padding: 0.55rem 0;
            font-size: 0.86rem;
            background-color: transparent;
            color: #e5e7eb;
            box-shadow: none;
        }

        .sidebar .accordion-button::after {
            filter: invert(1) opacity(0.7);
        }

        .sidebar .accordion-button:not(.collapsed) {
            color: #ffffff;
        }

        .sidebar .accordion-body {
            padding: 0.25rem 0 0.75rem 0;
        }

        .nav-link-soft {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.30rem 0.5rem;
            border-radius: 0.35rem;
            color: #9ca3af;
            text-decoration: none;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .nav-link-soft:hover {
            background-color: rgba(148, 163, 184, 0.16);
            color: #e5e7eb;
        }

        .nav-link-soft.active {
            background-color: rgba(13, 110, 253, 0.18);
            color: #ffffff;
        }

        .sidebar-footer {
            border-top: 1px solid rgba(55, 65, 81, 0.9);
            padding-top: 1rem;
            margin-top: auto;
        }

        /* MAIN */
        .main-content {
            flex: 1;
            padding: 1.5rem 1.75rem;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .topbar-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: #111827;
        }

        .topbar-subtitle {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .content-card {
            border-radius: 0.75rem;
            border: 1px solid #e5e7eb;
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);
            padding: 1.25rem 1.5rem;
        }

        /* RESPONSIVE: en pantallas muy pequeñas, sidebar arriba */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                min-height: auto;
            }
            .app-shell {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<div class="app-shell d-flex">
    {{-- SIDEBAR IZQUIERDO --}}
    <aside class="sidebar d-flex flex-column p-3">

        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <div class="sidebar-brand">ImpulseGo</div>
                <div class="sidebar-subtitle">Panel de reportes</div>
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                v1.0
            </span>
        </div>

        @if(session()->has('usuario'))
            <div class="mb-3 small rounded border border-secondary-subtle p-2 bg-dark bg-opacity-25">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-success d-inline-block"
                          style="width: 7px; height: 7px;"></span>
                    <span class="fw-semibold">{{ session('usuario') }}</span>
                </div>
            </div>
        @endif

        {{-- Menú acordeón --}}
        <div class="accordion" id="accordionMenu">

            {{-- Cargas Gestiones --}}
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingGestiones">
                    <button class="accordion-button collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseGestiones"
                            aria-expanded="false"
                            aria-controls="collapseGestiones">
                        <i class="bi bi-telephone-outbound me-1"></i>
                        Cargas de Gestiones
                    </button>
                </h2>
                <div id="collapseGestiones"
                     class="accordion-collapse collapse"
                     aria-labelledby="headingGestiones"
                     data-bs-parent="#accordionMenu">
                    <div class="accordion-body">
                        <ul class="nav flex-column small">
                            <li class="nav-item mb-1">
                                <a href="{{ route('gestiones.propia12.form') }}"
                                   class="nav-link-soft">
                                    <i class="bi bi-folder2-open"></i>
                                    Propia 1 y 2
                                </a>
                            </li>
                            <li class="nav-item mb-1">
                                <a href="#"
                                   class="nav-link-soft">
                                    <i class="bi bi-folder2-open"></i>
                                    Propia 3
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#"
                                   class="nav-link-soft">
                                    <i class="bi bi-folder2-open"></i>
                                    Propia 4
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('gestiones.amd') }}"
                                   class="nav-link-soft">
                                    <i class="bi bi-folder2-open"></i>
                                    AMD
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('gestiones.abandonados') }}"
                                   class="nav-link-soft">
                                    <i class="bi bi-folder2-open"></i>
                                    Abadonados
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Cargas Pagos --}}
            <div class="accordion-item mt-1">
                <h2 class="accordion-header" id="headingPagos">
                    <button class="accordion-button collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapsePagos"
                            aria-expanded="false"
                            aria-controls="collapsePagos">
                        <i class="bi bi-cash-coin me-1"></i>
                        Cargas de Pagos
                    </button>
                </h2>
                <div id="collapsePagos"
                     class="accordion-collapse collapse"
                     aria-labelledby="headingPagos"
                     data-bs-parent="#accordionMenu">
                    <div class="accordion-body">
                        <ul class="nav flex-column small">
                            <li class="nav-item mb-1">
                                <a href="{{ route('pagos.propia12.index') }}"
                                   class="nav-link-soft {{ request()->routeIs('pagos.propia12.*') ? 'active' : '' }}">
                                    <i class="bi bi-credit-card"></i>
                                    Propia 1 y 2
                                </a>
                            </li>
                            <li class="nav-item mb-1">
                                <a href="#"
                                   class="nav-link-soft">
                                    <i class="bi bi-credit-card"></i>
                                    Propia 3
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#"
                                   class="nav-link-soft">
                                    <i class="bi bi-credit-card"></i>
                                    Propia 4
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Cargas --}}
            <div class="accordion-item mt-1">
                <h2 class="accordion-header" id="headingCartera">
                    <button class="accordion-button collapsed"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapseCartera"
                            aria-expanded="false"
                            aria-controls="collapseCartera">
                        <i class="bi bi-database-up me-1"></i>
                        Cargas de Cartera
                    </button>
                </h2>
                <div id="collapseCartera"
                     class="accordion-collapse collapse"
                     aria-labelledby="headingCartera"
                     data-bs-parent="#accordionMenu">
                    <div class="accordion-body">
                        <ul class="nav flex-column small">
                            <li class="nav-item mb-1">
                                <a href="#"
                                   class="nav-link-soft">
                                    <i class="bi bi-database-fill"></i>
                                    Propia 1 y 2
                                </a>
                            </li>
                            <li class="nav-item mb-1">
                                <a href="#"
                                   class="nav-link-soft">
                                    <i class="bi bi-database-fill"></i>
                                    Propia 3
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#"
                                   class="nav-link-soft">
                                    <i class="bi bi-database-fill"></i>
                                    Propia 4
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        {{-- FOOTER SIDEBAR --}}
        @if(session()->has('usuario'))
            <div class="sidebar-footer mt-3">
                <form action="{{ route('logout') }}" method="POST" class="d-grid gap-2">
                    @csrf
                    <button class="btn btn-outline-light btn-sm" type="submit">
                        <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
                    </button>
                </form>
            </div>
        @endif

    </aside>

    {{-- CONTENIDO PRINCIPAL --}}
    <main class="main-content">

        {{-- BARRA SUPERIOR DEL CONTENIDO --}}
        <div class="topbar">
            <div>
                <div class="topbar-title">
                    @yield('page_title', 'Panel de control')
                </div>
                <div class="topbar-subtitle">
                    @yield('page_subtitle', 'Gestión de cargas y reportes de propia.')
                </div>
            </div>
            {{-- Espacio para filtros, fecha o acciones rápidas --}}
            <div class="d-none d-md-flex align-items-center gap-2">
                <span class="badge text-bg-light border">
                    {{ now()->format('d/m/Y') }}
                </span>
            </div>
        </div>

        {{-- CONTENIDO ENVUELTO EN CARD --}}
        <div class="content-card">
            @yield('content')
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
