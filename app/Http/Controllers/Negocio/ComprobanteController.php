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
        $comprobantes = Comprobante::with(['tipoComprobante', 'cliente'])->orderBy('id', 'desc')->cursorPaginate(16);
        return view('comprobantes.index')->with(['comprobantes' => $comprobantes
    ]);
}

public function createFacturaA(?Cliente $cliente = null){
    return $this->createFactura('A', $cliente);
}

public function createFacturaB(?Cliente $cliente = null){
    return $this->createFactura('B', $cliente);
}
public function createFacturaC(){
    return $this->createFactura('C');
}

private function createFactura(string $tipo, ?Cliente $cliente = null){
    abort_unless(emisor_puede_emitir_factura($tipo), 403, 'La condicion de IVA del emisor no permite este tipo de factura.');
    if ($cliente) {
        abort_unless(tipo_factura_para_receptor($cliente->condicionIva) === $tipo, 403, 'El tipo de factura no corresponde al cliente.');
    }

    $condicionesIva = match ($tipo) {
        'A' => IvaReceptor::whereIn('codigo_afip', IvaReceptor::CODIGOS_FACTURA_A)->get(),
        'B' => IvaReceptor::whereIn('codigo_afip', IvaReceptor::CODIGOS_FACTURA_B)->get(),
        'C' => IvaReceptor::all(),
    };

    return view('comprobantes.createFactura'.$tipo, [
        'condicionesIva' => $condicionesIva,
        'ivas' => Iva::all(),
        'cliente' => $cliente,
        'ivaPredeterminado' => $tipo === 'C' ? null : Iva::find(variable_global('IVA_PREDETERMINADO'))?->id,
    ]);
}

public function createLoteFacturasC(){
    abort_unless(emisor_puede_emitir_factura('C'), 403);
    $condicionesIva = IvaReceptor::all();
    return view('comprobantes.createLoteFacturasC', with([
        'condicionesIva' => $condicionesIva,
    ]));
}

public function createFacturaCCliente(Cliente $cliente){
    return $this->createFactura('C', $cliente);
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
