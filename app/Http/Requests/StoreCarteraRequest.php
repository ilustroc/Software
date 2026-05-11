<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCarteraRequest extends FormRequest
{
    protected $errorBag = 'cartera';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nombre = trim((string) $this->input('nombre'));
        $slug = trim((string) $this->input('slug'));

        $this->merge([
            'nombre' => $nombre,
            'slug' => Str::slug($slug !== '' ? $slug : $nombre),
            'activa' => $this->boolean('activa'),
        ]);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', 'alpha_dash', Rule::unique('carteras', 'slug')],
            'activa' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'nombre de cartera',
            'slug' => 'slug',
            'activa' => 'estado',
        ];
    }
}
