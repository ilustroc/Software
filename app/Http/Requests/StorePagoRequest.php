<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cartera_id' => ['required', 'integer', 'exists:carteras,id'],
            'dni' => ['required', 'string', 'max:20'],
            'operacion' => ['required', 'string', 'max:80'],
            'moneda' => ['required', 'string', 'max:20'],
            'fecha' => ['required', 'date'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'gestor' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function attributes(): array
    {
        return [
            'cartera_id' => 'cartera',
            'dni' => 'DNI',
            'operacion' => 'operacion',
            'fecha' => 'fecha',
            'moneda' => 'moneda',
            'monto' => 'monto',
            'gestor' => 'gestor',
        ];
    }
}
