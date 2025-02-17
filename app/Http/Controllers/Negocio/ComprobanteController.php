<?php

namespace App\Http\Controllers\Negocio;

use App\Http\Controllers\Controller;
use App\Mail\NuevoComprobante;
use App\Models\Arca\Comprobante;
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

    public function createFacturaC(){
        $condicionesIva = IvaReceptor::all();
        return view('comprobantes.createFacturaC', with([
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
        return $pdf->download('Comprobante ' . $comprobante->nro_comprobante . '.pdf');
    }

    
    public function enviarMail(Comprobante $comprobante, Request $request){
        Mail::to($request->email)->send(new NuevoComprobante($comprobante));
        toast('Mail enviado correctamente', 'success')->autoClose(1500);
        return redirect()->back();
    }
}
