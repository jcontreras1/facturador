<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Models\Cliente;
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
        return view('clientes.dashboard')->with(compact(['cliente', 'comprobantes']));

    }
}
