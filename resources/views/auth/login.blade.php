<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - ImpulseGo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('img/logotipo.png') }}">

    @vite(['resources/css/auth.css', 'resources/js/app.js'])
</head>
<body class="auth-page">

    <main class="auth-shell">
        <div class="auth-wrap">

            <section class="auth-card">
                <div class="auth-header">
                    <div class="auth-logo-box">
                        <img
                            src="{{ asset('img/logotipo.png') }}"
                            alt="ImpulseGo"
                            class="auth-logo-mark"
                        >
                    </div>

                    <div>
                        <div class="auth-kicker">Panel administrativo</div>
                        <h1 class="auth-title">Iniciar sesión</h1>
                        <p class="auth-subtitle">Ingresa tus credenciales para continuar.</p>
                    </div>
                </div>

                @if($errors->has('login'))
                    <div class="alert-error mb-5">
                        {{ $errors->first('login') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="auth-form">
                    @csrf

                    <div class="auth-field">
                        <label for="usuario" class="form-label">Usuario</label>
                        <input
                            id="usuario"
                            type="text"
                            name="usuario"
                            value="{{ old('usuario') }}"
                            autofocus
                            autocomplete="username"
                            placeholder="Escribe tu usuario"
                            class="form-input @error('usuario') form-input-error @enderror"
                        >
                        @error('usuario')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="auth-field" x-data="{ show: false }">
                        <label for="password" class="form-label">Contraseña</label>

                        <div class="password-wrap">
                            <input
                                id="password"
                                x-bind:type="show ? 'text' : 'password'"
                                name="password"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="form-input pr-11 @error('password') form-input-error @enderror"
                            >

                            <button
                                type="button"
                                class="input-action"
                                @click="show = !show"
                                aria-label="Mostrar contraseña"
                            >
                                <svg x-show="!show" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" stroke="currentColor" stroke-width="2"/>
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                </svg>

                                <svg x-show="show" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                    <path d="M3 3l18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M10.58 10.58A2 2 0 0 0 13.42 13.42" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M9.88 5.09A10.94 10.94 0 0 1 12 5c6.5 0 10 7 10 7a17.56 17.56 0 0 1-4.04 4.87" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M6.61 6.61C3.73 8.55 2 12 2 12s3.5 7 10 7a9.77 9.77 0 0 0 4.24-.93" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>

                        @error('password')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn-primary">
                        Ingresar
                    </button>
                </form>

                <p class="auth-note">
                    Acceso solo para personal autorizado
                </p>
            </section>

            <div class="auth-footer">
                © {{ date('Y') }} ImpulseGo
            </div>
        </div>
    </main>

</body>
</html>