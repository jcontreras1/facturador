<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Item;
use App\Models\ServicioCliente;
use Illuminate\Http\Request;

class ServicioClienteController extends Controller
{
    public function store(Cliente $cliente, Request $request){
        $validated = $request->validate([
            'item_id' => 'nullable|exists:items,id',
            'descripcion_manual' => 'nullable|string|required_without:item_id',
            'cantidad' => 'required|numeric|min:0.01',
            'iva_id' => 'nullable|exists:iva,id',
            'importe_unitario_neto' => 'nullable|numeric|min:0',
            'importe_unitario_subtotal' => 'required|numeric|min:0',
            'fecha' => 'nullable|date',
        ]);

        $descripcion = '';
        if (!empty($validated['item_id']) && empty($validated['descripcion_manual'])) {
            $descripcion = Item::find($validated['item_id'])->descripcion;
        } else {
            $descripcion = $validated['descripcion_manual'];
        }

        $servicioCliente = ServicioCliente::create([
            'cliente_id' => $cliente->id,
            'item_id' => $validated['item_id'] ?? null,
            'cantidad' => $validated['cantidad'],
            'descripcion' => $descripcion,
            'importe_neto' => $validated['importe_unitario_neto'] ?? $validated['importe_unitario_subtotal'],
            'importe_total' => $validated['importe_unitario_subtotal'],
            'iva_id' => $validated['iva_id'] ?? null,
            'fecha' => $validated['fecha'] ?? null,
        ]);

        toast('Servicio agregado correctamente', 'success')->autoClose(1500);
        return redirect()->back();



    }

    public function update(Cliente $cliente, ServicioCliente $servicioCliente, Request $request){

    }

    public function destroy(Cliente $cliente, ServicioCliente $servicioCliente){
        $servicioCliente->delete();
        toast('Servicio eliminado correctamente', 'success');
        return redirect()->back();
    }
}
