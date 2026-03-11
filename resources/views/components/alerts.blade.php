@php
    $alertLogo = asset('img/logotipo.png'); // cambia esta ruta por la real de tu logotipo
@endphp

<div class="toast-stack pointer-events-none">

    @if(session('msg'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 5000)"
            x-show="show"
            x-transition.opacity.scale.95
            x-cloak
            class="pointer-events-auto toast-item toast-success"
        >
            <div class="toast-logo-wrap">
                <img src="{{ $alertLogo }}" alt="Logo" class="toast-logo">
            </div>

            <div class="min-w-0 flex-1">
                <div class="toast-title">Operación exitosa</div>
                <div class="toast-text">{{ session('msg') }}</div>
            </div>

            <button type="button" class="toast-close" @click="show = false" aria-label="Cerrar">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 6500)"
            x-show="show"
            x-transition.opacity.scale.95
            x-cloak
            class="pointer-events-auto toast-item toast-error"
        >
            <div class="toast-logo-wrap">
                <img src="{{ $alertLogo }}" alt="Logo" class="toast-logo">
            </div>

            <div class="min-w-0 flex-1">
                <div class="toast-title">Atención</div>
                <div class="toast-text">{{ session('error') }}</div>
            </div>

            <button type="button" class="toast-close" @click="show = false" aria-label="Cerrar">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 8000)"
            x-show="show"
            x-transition.opacity.scale.95
            x-cloak
            class="pointer-events-auto toast-item toast-error"
        >
            <div class="toast-logo-wrap">
                <img src="{{ $alertLogo }}" alt="Logo" class="toast-logo">
            </div>

            <div class="min-w-0 flex-1">
                <div class="toast-title">Atención: revisa los datos</div>
                <ul class="toast-list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>

            <button type="button" class="toast-close" @click="show = false" aria-label="Cerrar">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>
    @endif

</div>