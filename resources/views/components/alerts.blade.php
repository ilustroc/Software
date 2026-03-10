{{-- Éxito --}}
@if(session('msg'))
    <div class="alert-container alert-success mb-5">
        <div class="flex items-start gap-3">
            <span class="alert-icon bg-emerald-600">✓</span>
            <div>
                <div class="font-bold">Operación exitosa</div>
                <div class="text-emerald-700/90">{{ session('msg') }}</div>
            </div>
        </div>
    </div>
@endif

{{-- Errores de validación --}}
@if($errors->any())
    <div class="alert-container alert-error mb-5">
        <div class="flex items-start gap-3">
            <span class="alert-icon bg-red-600 text-xs">!</span>
            <div>
                <div class="font-bold">Atención: Revisa los datos</div>
                <ul class="mt-1 list-inside list-disc opacity-90">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif