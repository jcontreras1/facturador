<?php

namespace App\Http\Requests;

use App\Models\Arca\IvaReceptor;
use App\Models\Cliente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreFacturaRequest extends FormRequest
{
    public function tipoFactura(): string
    {
        return match ($this->route()->getName()) {
            'comprobante.a.store' => 'A',
            'comprobante.b.store' => 'B',
            'comprobante.c.store' => 'C',
        };
    }

    public function authorize(): bool
    {
        return emisor_puede_emitir_factura($this->tipoFactura());
    }

    public function rules(): array
    {
        $rules = [
            'condicionIva' => ['required', 'integer', 'exists:condicion_iva_receptor,id'],
            'cliente' => ['nullable', 'integer', 'exists:clientes,id'],
        ];

        if (in_array($this->tipoFactura(), ['A', 'B'], true)) {
            $rules['lineas'] = ['required', 'array', 'min:1'];
            $rules['lineas.*.iva'] = ['required', 'integer', 'exists:iva,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'lineas.*.iva.required' => 'Seleccioná una alícuota de IVA en cada línea.',
            'lineas.*.iva.exists' => 'La alícuota de IVA seleccionada no es válida.',
        ];
    }

    public function clienteParaComprobante(): ?Cliente
    {
        if ($this->filled('cliente')) {
            return Cliente::findOrFail($this->input('cliente'));
        }

        $documento = preg_replace('/\D/', '', (string) $this->input('documento'));
        if ($this->integer('tipoDocumentoId') !== 80 || strlen($documento) !== 11) {
            return null;
        }

        return Cliente::where('cuit', $documento)->first();
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->has('condicionIva') || $validator->errors()->has('cliente')) {
                    return;
                }

                $receptor = IvaReceptor::find($this->input('condicionIva'));
                if (tipo_factura_para_receptor($receptor) !== $this->tipoFactura()) {
                    $validator->errors()->add('condicionIva', 'El tipo de factura no corresponde a la condicion de IVA del receptor.');
                }

                if ($this->filled('cliente')) {
                    $cliente = Cliente::findOrFail($this->input('cliente'));
                    if ((int) $cliente->condicion_iva_receptor_id !== (int) $receptor->id) {
                        $validator->errors()->add('condicionIva', 'La condicion de IVA debe coincidir con la del cliente seleccionado.');
                    }
                }
            },
        ];
    }
}
