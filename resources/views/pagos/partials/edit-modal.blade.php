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
                <p class="mt-1 text-xs text-slate-500">DNI y operación no se modifican.</p>
            </div>

            <button type="button" class="icon-btn" @click="open = false">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('pagos.update', [$cartera, $p->id]) }}">
            @csrf
            @method('PUT')

            <div class="modal-body">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">DNI</label>
                        <input type="text" disabled value="{{ $p->DNI }}" class="readonly-input">
                    </div>

                    <div>
                        <label class="form-label">Operación</label>
                        <input type="text" disabled value="{{ $p->OPERACION }}" class="readonly-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Fecha</label>
                        <input
                            type="date"
                            name="fecha"
                            value="{{ \Carbon\Carbon::parse($p->FECHA)->format('Y-m-d') }}"
                            required
                            class="form-input"
                        >
                    </div>

                    <div>
                        <label class="form-label">Moneda</label>
                        <input type="text" name="moneda" value="{{ $p->MONEDA }}" class="form-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="form-label">Monto</label>
                        <input
                            type="number"
                            step="0.01"
                            name="monto"
                            value="{{ $p->MONTO }}"
                            required
                            class="form-input text-right"
                        >
                    </div>

                    <div>
                        <label class="form-label">Gestor</label>
                        <input type="text" name="gestor" value="{{ $p->GESTOR }}" class="form-input">
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