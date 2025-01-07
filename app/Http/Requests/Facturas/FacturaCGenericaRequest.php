<?php

namespace App\Http\Requests\Facturas;

use Illuminate\Foundation\Http\FormRequest;

class FacturaCGenericaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'importeTotal' => 'required|numeric',
            'fecha' => 'required|date',
            'concepto' => 'required|numeric',
            // 'detalle' => 'required|array',
            'tipoDocuemnto' => 'required|numeric',
            'documento' => 'present|nullable|numeric',
            'razonSocial' => 'string|nullable',
            'domicilio' => 'string|nullable',
        ];
    }
}
