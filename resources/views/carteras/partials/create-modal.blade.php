<div
    x-show="carteraModalOpen"
    x-transition.opacity
    x-cloak
    class="modal-shell"
    role="dialog"
    aria-modal="true"
>
    <div class="modal-backdrop" @click="carteraModalOpen = false"></div>

    <div
        class="modal-card"
        x-data="{
            nombre: @js(old('nombre', '')),
            slug: @js(old('slug', '')),
            touchedSlug: @js((bool) old('slug')),
            slugify(value) {
                return value
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '')
                    .slice(0, 120);
            }
        }"
    >
        <div class="modal-head">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Agregar cartera</h3>
                <p class="mt-1 text-xs text-slate-500">La cartera quedara disponible en pagos, gestiones y dashboard.</p>
            </div>

            <button type="button" class="icon-btn" @click="carteraModalOpen = false" title="Cerrar">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                    <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('configuracion.carteras.store') }}">
            @csrf

            <div class="modal-body">
                <div>
                    <label class="form-label">Nombre de cartera</label>
                    <input
                        type="text"
                        name="nombre"
                        x-model="nombre"
                        @input="if (!touchedSlug) slug = slugify(nombre)"
                        required
                        class="form-input @error('nombre', 'cartera') form-input-error @enderror"
                        placeholder="Ej: Nueva cartera"
                    >
                    @error('nombre', 'cartera') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Slug</label>
                    <input
                        type="text"
                        name="slug"
                        x-model="slug"
                        @input="touchedSlug = true; slug = slugify(slug)"
                        required
                        class="form-input @error('slug', 'cartera') form-input-error @enderror"
                        placeholder="nueva-cartera"
                    >
                    @error('slug', 'cartera') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="form-label">Estado</label>
                    <select name="activa" class="form-input @error('activa', 'cartera') form-input-error @enderror">
                        <option value="1" @selected(old('activa', '1') === '1')>Activa</option>
                        <option value="0" @selected(old('activa') === '0')>Inactiva</option>
                    </select>
                    @error('activa', 'cartera') <p class="mt-2 text-xs text-red-700">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-muted" @click="carteraModalOpen = false">
                    Cancelar
                </button>

                <button type="submit" class="btn-primary w-auto px-4">
                    Crear cartera
                </button>
            </div>
        </form>
    </div>
</div>
