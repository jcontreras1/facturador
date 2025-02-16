<?php

namespace App\Http\Controllers\Negocio;

use App\Http\Controllers\Controller;
use App\Models\Arca\Comprobante;
use App\Models\Arca\IvaReceptor;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

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

    public function descargarPdf(Comprobante $comprobante){
        return view('comprobantes.pdf', ['comprobante' => $comprobante]);
        $pdf = PDF::loadView('comprobantes.pdf', ['comprobante' => $comprobante]);
        return $pdf->download('Comprobante ' . $comprobante->nro_comprobante . '.pdf');
    }
}
