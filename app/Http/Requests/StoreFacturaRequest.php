<?php

namespace App\Http\Requests;

use App\Models\Arca\IvaReceptor;
use App\Models\Cliente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
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
            'agregarComoCliente' => ['sometimes', 'boolean'],
        ];

        if ($this->boolean('agregarComoCliente')) {
            $rules['tipoDocumentoId'] = ['required', 'integer', Rule::in([80, 86, 96])];
            $rules['documento'] = [
                'required',
                'string',
                $this->integer('tipoDocumentoId') === 96 ? 'regex:/^[0-9]{7,8}$/' : 'regex:/^[0-9]{11}$/',
            ];
            $rules['razonSocial'] = ['required', 'string', 'max:255'];
            $rules['domicilio'] = ['nullable', 'string', 'max:255'];
        }

        if (in_array($this->tipoFactura(), ['A', 'B'], true)) {
            $rules['lineas'] = ['required', 'array', 'min:1'];
            $rules['lineas.*.iva'] = ['required', 'integer', 'exists:iva,id'];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if (is_scalar($this->input('documento'))) {
            $this->merge(['documento' => preg_replace('/[\s.\-]/', '', (string) $this->input('documento'))]);
        }
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
        $tipoDocumento = $this->integer('tipoDocumentoId');
        if (!in_array($tipoDocumento, [80, 86, 96], true) || $documento === '') {
            return null;
        }

        $cliente = Cliente::porDocumento($documento, $tipoDocumento)->first();
        if ($cliente || !$this->boolean('agregarComoCliente')) {
            return $cliente;
        }

        return Cliente::firstOrCreate(['cuit' => $documento], [
            'nombre' => $this->input('razonSocial'),
            'direccion' => $this->input('domicilio'),
            'tipo_documento_afip' => $tipoDocumento,
            'condicion_iva_receptor_id' => $this->input('condicionIva'),
        ]);
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
