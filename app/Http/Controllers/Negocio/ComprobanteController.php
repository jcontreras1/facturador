<?php

namespace App\Http\Controllers\Negocio;

use App\Http\Controllers\Controller;
use App\Mail\NuevoComprobante;
use App\Models\Arca\Comprobante;
use App\Models\Arca\Iva;
use App\Models\Arca\IvaReceptor;
use App\Models\Arca\TipoComprobante;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;


class ComprobanteController extends Controller
{
    public function index(Request $request){
        $filtros = $request->validate([
            'tipo_comprobante_id' => ['nullable', 'integer', 'exists:tipo_comprobante,id'],
            'numero' => ['nullable', 'regex:/^[0-9]{1,8}$/'],
            'fecha_desde' => ['nullable', 'date_format:Y-m-d'],
            'fecha_hasta' => [
                'nullable',
                'date_format:Y-m-d',
                $request->filled('fecha_desde') ? 'after_or_equal:fecha_desde' : 'nullable',
            ],
        ]);

        $tiposComprobante = TipoComprobante::whereIn(
            'id', Comprobante::select('tipo_comprobante_id')->distinct()
        )->orderBy('descripcion')->get();

        $query = Comprobante::with(['tipoComprobante', 'cliente']);

        if (!empty($filtros['tipo_comprobante_id'])) {
            $query->where('tipo_comprobante_id', $filtros['tipo_comprobante_id']);
        }
        if (isset($filtros['numero'])) {
            $query->where('nro_comprobante', ltrim($filtros['numero'], '0') ?: '0');
        }
        if (!empty($filtros['fecha_desde'])) {
            $query->whereRaw('COALESCE(fecha_emision, DATE(created_at)) >= ?', [$filtros['fecha_desde']]);
        }
        if (!empty($filtros['fecha_hasta'])) {
            $query->whereRaw('COALESCE(fecha_emision, DATE(created_at)) <= ?', [$filtros['fecha_hasta']]);
        }

        $comprobantes = $query->orderBy('id', 'desc')->cursorPaginate(16)->withQueryString();
        $valoresFiltros = array_filter($request->old() ?: $filtros, 'is_scalar');

        return view('comprobantes.index', compact('comprobantes', 'tiposComprobante', 'filtros', 'valoresFiltros'));
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
