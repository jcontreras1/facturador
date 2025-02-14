<?php

namespace App\Http\Controllers\Negocio;

use App\Http\Controllers\AfipWS\Afip;
use App\Http\Controllers\Controller;
use App\Http\Requests\Facturas\FacturaCGenericaRequest;
use App\Mail\NuevaFactura;
use App\Models\Factura;
use App\Models\ItemFactura;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use SimpleQRCode\QRCode;


class ContribuyenteController extends Controller
{
    public function padronv4(){
        $afip = new Afip();
       // $result = $afip->ConsultaEmbarque->obtenerConsultaEstadosCOEM([]);
         $result = $afip->ConsultaTablasReferencia->listaTablasReferencia([]);
        // $result = $afip->ComunicacionEmbarque->registrarCaratula([]);
        dd($result);

        // $contribuyente = $afip->PadronAlcance4->GetTaxpayerDetails(20924507005);
        // return datosContribuyente(contribuyenteObject: $contribuyente);
        // dd($contribuyente);
        // dd(datosContribuyente(contribuyenteObject: $contribuyente));

    }
    public function infoContribuyente(Request $request, $doc){
        $afip = new Afip();
        $contribuyente = null;

        if($request->tipoDoc == 'dni'){
            $contribuyente = $afip->PadronAlcance10->GetTaxpayerDetails($doc);
        }else{
            $contribuyente = $afip->PadronAlcance13->GetTaxpayerDetails($doc);
        }
        if(!$contribuyente){
            return response(['error' => 'No se encontraron datos para el CUIT ingresado'], 404);
        }
        return 
            response(datosContribuyente(contribuyenteObject: $contribuyente), 200);
    }
}