<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportPagosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cartera_id' => ['required', 'integer', 'exists:carteras,id'],
            'archivo' => ['required', 'file', 'mimes:xlsx', 'max:15360'],
        ];
    }

    public function attributes(): array
    {
        return [
            'cartera_id' => 'cartera',
            'archivo' => 'archivo XLSX',
        ];
    }
}
