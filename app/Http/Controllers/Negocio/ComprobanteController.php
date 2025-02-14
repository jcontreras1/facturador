<?php

namespace App\Http\Controllers\Negocio;

use App\Http\Controllers\Controller;
use App\Models\Arca\Comprobante;
use App\Models\Arca\IvaReceptor;
use Illuminate\Http\Request;

class ComprobanteController extends Controller
{
    public function index(){
        $comprobantes = Comprobante::orderBy('id', 'desc')->cursorPaginate(16);
        return view('comprobantes.index')->with(['comprobantes' => $comprobantes
        ]);
    }

    public function createFacturaC(){
        $condicionesIva = IvaReceptor::all();
        return view('comprobantes.createFacturaC', with([
            'condicionesIva' => $condicionesIva,
        ]));
    }
}
