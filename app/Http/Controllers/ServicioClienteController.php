<?php

namespace App\Http\Controllers;

use App\Models\Arca\Iva;
use App\Models\Cliente;
use App\Models\Item;
use App\Models\ServicioCliente;
use Illuminate\Http\Request;

class ServicioClienteController extends Controller
{
    public function store(Cliente $cliente, Request $request){
        $descripcion = "";
        if($request->item_id !== null && $request->descripcion === null){
            $descripcion = Item::find($request->item_id)->descripcion;
        }else{
            //Si ya había una descripción cargada
            $descripcion = $request->descripcion_manual;
        }

        $servicioCliente = ServicioCliente::create([
            'cliente_id' => $cliente->id,
            'item_id' => $request->item_id,
            'cantidad' => $request->cantidad ?? '1',
            'descripcion' => $descripcion,
            'importe_neto' => $request->importe_unitario_neto ?? $request->importe_unitario_subtotal,
            'importe_total' => $request->has('iva_id') ? 
            round((Iva::find($request->iva_id)?->iva / 100) * $request->importe_unitario_neto, 2) : 
            $request->importe_unitario_subtotal,
            'iva_id' => $request->has('iva_id') ? $request->iva_id : null,
            'fecha' => $request->fecha,
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
