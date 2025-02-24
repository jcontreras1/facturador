<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Negocio\Comprobante\Factura\CController;
use App\Http\Requests\StoreClienteRequest;
use App\Mail\NuevoComprobante;
use App\Models\Arca\Comprobante;
use App\Models\Arca\Iva;
use App\Models\Arca\IvaReceptor;
use App\Models\Arca\TipoDocumento;
use App\Models\Cliente;
use App\Models\Item;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ClienteController extends Controller
{
    public function index(){
        
        $clientes = Cliente::orderBy('nombre', 'asc')->get();
        return view('clientes.index')->with(compact(['clientes']));
    }
    
    public function create(){
        return view('clientes.create');
    }
    public function edit(Cliente $cliente){
        $ivasReceptor = IvaReceptor::all();
        $tiposDocumento = TipoDocumento::getOptions();
        return view('clientes.edit')->with(compact([
            'cliente',
            'ivasReceptor',
            'tiposDocumento',
        ]));
    }
    
    public function update(Cliente $cliente, Request $request){
        $cliente->update($request->all());
        toast('Cliente actualizado correctamente', 'success');
        return redirect()->route('clientes.dashboard', $cliente);
    }
    
    public function store(StoreClienteRequest $request){
        Cliente::updateOrCreate([
            'cuit' => preg_replace('/\D/', '', $request->cuit),
        ],[
            'nombre' => $request->nombre,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion,
            'condicion_iva_receptor_id' => $request->condicion_iva_receptor_id,
            'tipo_documento_afip' => $request->tipo_documento_afip,
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
    
    public function resumen(){
        $clientes = Cliente::where('requiere_facturacion_mensual', true)
        ->orderBy('razon_social')
        ->with('servicios.servicio')
        ->get();
        return view('clientes.resumen')->with(compact(['clientes']));
    }
    
    public function facturacionMensual(Request $request){
        
        $clientes = Cliente::where('requiere_facturacion_mensual', true)->get();
        $notificar = boolval($request->notificar);
        $msg = '';
        foreach($clientes as $cliente){
            try {
                $comprobante = CController::facturacionMensual($cliente);
                if($notificar){
                    Mail::to($cliente->email)->send(new NuevoComprobante($comprobante));
                }
            } catch (\Throwable $th) {
                $msg .= 'Error al generar comprobante. Razon: '.$th->getMessage().'<br>';
            }
        }
        return response(['msg' => $msg], 201);
    }
    
}
