<?php

namespace App\Http\Controllers\Negocio;

use App\Http\Controllers\Controller;
use App\Mail\NuevoComprobante;
use App\Models\Arca\Comprobante;
use App\Models\Arca\Iva;
use App\Models\Arca\IvaReceptor;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;


class ComprobanteController extends Controller
{
    public function index(){
        $comprobantes = Comprobante::with('tipoComprobante')->orderBy('id', 'desc')->cursorPaginate(16);
        return view('comprobantes.index')->with(['comprobantes' => $comprobantes
    ]);
}

public function createFacturaA(){
    //Solo autorizados por Factura A
    $condicionesIva = IvaReceptor::whereIn('codigo_afip', ['1','6','13','16'])->get();
    return view('comprobantes.createFacturaA', with([
        'condicionesIva' => $condicionesIva,
        'ivas' => Iva::all(),
    ]));
}

public function createFacturaB(){
    $condicionesIva = IvaReceptor::all();
    return view('comprobantes.createFacturaB', with([
        'condicionesIva' => $condicionesIva,
    ]));
}
public function createFacturaC(){
    $condicionesIva = IvaReceptor::all();
    return view('comprobantes.createFacturaC', with([
        'condicionesIva' => $condicionesIva,
    ]));
}

public function createLoteFacturasC(){
    $condicionesIva = IvaReceptor::all();
    return view('comprobantes.createLoteFacturasC', with([
        'condicionesIva' => $condicionesIva,
    ]));
}

public function createFacturaCCliente(Cliente $cliente){
    $condicionesIva = IvaReceptor::all();
    return view('comprobantes.createFacturaC', with([
        'condicionesIva' => $condicionesIva,
        'cliente' => $cliente,
    ]));
}

public function descargarPdf(Comprobante $comprobante){
    // return view('comprobantes.pdf', ['comprobante' => $comprobante]);
    $pdf = PDF::loadView('comprobantes.pdf', ['comprobante' => $comprobante]);
    return $pdf->download(
        strtoupper(variable_global('RAZON_SOCIAL')) . ' - ' . titulo_comprobante($comprobante) . '.pdf');
    }
    
    public function descargarTermica(Comprobante $comprobante){
        return view('comprobantes.termica', ['comprobante' => $comprobante]);
        // $pdf = PDF::loadView('comprobantes.termica', ['comprobante' => $comprobante]);
        // return $pdf->download(
        // strtoupper(variable_global('RAZON_SOCIAL')) . ' - ' . titulo_comprobante($comprobante) . '.pdf');
    }
    
    public function enviarMail(Comprobante $comprobante, Request $request){
        Mail::to($request->email)->send(new NuevoComprobante($comprobante));
        toast('Mail enviado correctamente', 'success')->autoClose(1500);
        return redirect()->back();
    }
    
}
