<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Models\Arca\Iva;
use App\Models\Cliente;
use App\Models\Item;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(){

        $clientes = Cliente::orderBy('nombre', 'asc')->get();
        return view('clientes.index')->with(compact(['clientes']));
    }

    public function create(){
        return view('clientes.create');
    }

    public function store(StoreClienteRequest $request){
        Cliente::updateOrCreate([
            'cuit' => preg_replace('/\D/', '', $request->cuit),
        ],[
            'nombre' => $request->nombre,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
        ]);

        toast('Cliente creado correctamente', 'success');
        return redirect()->route('clientes.index');
    }

    public function dashboard(Cliente $cliente){
        $comprobantes = $cliente->comprobantes()->orderBy('fecha', 'desc')->get();
        $ivas = Iva::all();
        $items = Item::all();
        $servicios = $cliente->servicios()->orderBy('id', 'desc')->get();
        return view('clientes.dashboard')->with(compact([
            'cliente', 
            'comprobantes', 
            'ivas', 
            'items',
            'servicios',
        ]));
    }

    public function toggleRequiereFacturacion(Cliente $cliente){
        $cliente->requiere_facturacion_mensual = !$cliente->requiere_facturacion_mensual;
        $cliente->save();
        toast('Cliente actualizado', 'success')->autoClose(1500);
        return redirect()->back();
    }
}
