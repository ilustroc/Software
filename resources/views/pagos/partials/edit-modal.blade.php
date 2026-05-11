@php
    $dni = data_get($p, 'dni', data_get($p, 'DNI'));
    $operacion = data_get($p, 'operacion', data_get($p, 'OPERACION'));
    $fecha = data_get($p, 'fecha', data_get($p, 'FECHA'));
    $moneda = data_get($p, 'moneda', data_get($p, 'MONEDA'));
    $monto = data_get($p, 'monto', data_get($p, 'MONTO'));
    $gestor = data_get($p, 'gestor', data_get($p, 'GESTOR'));
@endphp

<div x-show="open"
     x-transition.opacity
     x-cloak
     class="modal-shell"
     role="dialog"
     aria-modal="true">
    <div class="modal-backdrop" @click="open = false"></div>

    <div class="modal-card">
        <div class="modal-head">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Editar pago</h3>
                <p class="mt-1 text-xs text-slate-500">DNI y operacion no se modifican.</p>
            </div>

            <button type="button" class="icon-btn" @click="open = false" title="Cerrar">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('pagos.update', $p) }}">
            @csrf
            @method('PUT')

            <div class="modal-body">
                <div>
                    <label class="form-label">Cartera</label>
                    <select name="cartera_id" required class="form-input">
                        @foreach($carteras as $item)
                            <option value="{{ $item->id }}" @selected((int) $p->cartera_id === $item->id)>
                                {{ $item->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">DNI</label>
                        <input type="text" disabled value="{{ $dni }}" class="readonly-input">
                    </div>

                    <div>
                        <label class="form-label">Operacion</label>
                        <input type="text" disabled value="{{ $operacion }}" class="readonly-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Fecha</label>
                        <input
                            type="date"
                            name="fecha"
                            value="{{ $fecha ? \Carbon\Carbon::parse($fecha)->format('Y-m-d') : now()->toDateString() }}"
                            required
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label class="form-label">Moneda</label>
                        <select name="moneda" required class="form-input">
                            <option value="SOLES" @selected($moneda === 'SOLES')>SOLES</option>
                            <option value="DOLARES" @selected($moneda === 'DOLARES')>DOLARES</option>
                            @if($moneda && !in_array($moneda, ['SOLES', 'DOLARES'], true))
                                <option value="{{ $moneda }}" selected>{{ $moneda }}</option>
                            @endif
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Monto</label>
                        <input
                            type="number"
                            step="0.01"
                            name="monto"
                            value="{{ $monto }}"
                            required
                            class="form-input text-right"
                        >
                    </div>

                    <div>
                        <label class="form-label">Gestor</label>
                        <input type="text" name="gestor" value="{{ $gestor }}" class="form-input">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-muted" @click="open = false">
                    Cancelar
                </button>

                <button type="submit" class="btn-primary w-auto px-4">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
