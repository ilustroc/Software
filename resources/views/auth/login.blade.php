{{-- resources/views/auth/login.blade.php --}}
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - ImpulseGo</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Tailwind (CDN) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="min-h-screen bg-slate-50">
    <div class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            {{-- Card --}}
            <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                {{-- Header --}}
                <div class="px-6 pt-6">
                    <div class="flex items-center gap-3">
                        <div class="h-11 w-11 rounded-2xl bg-slate-900/5 flex items-center justify-center">
                            <svg class="h-5 w-5 text-slate-700" viewBox="0 0 24 24" fill="none">
                                <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Z" stroke="currentColor" stroke-width="2"/>
                                <path d="M20 21a8 8 0 1 0-16 0" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-slate-900">Ingreso a Reportes</div>
                            <div class="text-xs text-slate-500">ImpulseGo</div>
                        </div>
                    </div>
                </div>

                {{-- Body --}}
                <div class="px-6 pb-6 pt-5">
                    @if($errors->has('login'))
                        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                            {{ $errors->first('login') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                        @csrf

                        {{-- Usuario --}}
                        <div>
                            <label class="text-xs font-semibold text-slate-700">Usuario</label>
                            <input
                                type="text"
                                name="usuario"
                                value="{{ old('usuario') }}"
                                autofocus
                                class="mt-1 w-full rounded-xl border px-3 py-2 text-sm outline-none transition
                                    {{ $errors->has('usuario')
                                        ? 'border-red-400 focus:ring-4 focus:ring-red-100'
                                        : 'border-slate-200 focus:border-slate-300 focus:ring-4 focus:ring-slate-100'
                                    }}"
                                placeholder="Escribe tu usuario"
                            >
                            @error('usuario')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Contraseña --}}
                        <div>
                            <label class="text-xs font-semibold text-slate-700">Contraseña</label>
                            <input
                                type="password"
                                name="password"
                                class="mt-1 w-full rounded-xl border px-3 py-2 text-sm outline-none transition
                                    {{ $errors->has('password')
                                        ? 'border-red-400 focus:ring-4 focus:ring-red-100'
                                        : 'border-slate-200 focus:border-slate-300 focus:ring-4 focus:ring-slate-100'
                                    }}"
                                placeholder="••••••••"
                            >
                            @error('password')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Botón --}}
                        <button
                            type="submit"
                            class="w-full rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white
                                   hover:bg-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-200"
                        >
                            Ingresar
                        </button>

                        {{-- Footer mini --}}
                        <div class="pt-1 text-center text-[11px] text-slate-500">
                            Acceso solo para personal autorizado
                        </div>
                    </form>
                </div>
            </div>

            {{-- Nota inferior --}}
            <div class="mt-4 text-center text-xs text-slate-500">
                © {{ date('Y') }} ImpulseGo
            </div>
        </div>
    </div>
</body>
</html>
