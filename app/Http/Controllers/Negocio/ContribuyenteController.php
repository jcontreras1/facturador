<?php

namespace App\Http\Controllers\Negocio;

use App\Http\Controllers\AfipWS\Afip;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Arca\Comprobante;
use App\Models\Arca\TipoComprobante;
use Illuminate\Http\Request;


class ContribuyenteController extends Controller
{
    public function padronv4(){
        $tipoComprobante = TipoComprobante::where('codigo', 'C')->first();
        return response($tipoComprobante, 201);
        $afip = new Afip();
        $contribuyente = $afip->PadronAlcance13->GetTaxpayerDetails(30670501813);
        dd($contribuyente);
        //$contribuyente = $afip->PadronAlcance13->DniACuit(92450700);
        //$contribuyente = $afip->PadronAlcance13->GetTaxpayerDetails($contribuyente);
        //dd($contribuyente);
        return response(datosContribuyente($contribuyente), 200);
        
        
    }
    public function infoContribuyente(Request $request, $doc){
        if(trim($doc) == ''){
            return response(['error' => 'Debe ingresar un número de documento'], 400);
        }
        $afip = new Afip();
        $contribuyente = null;
        if($request->tipo == 'dni'){
            try {
                $contribuyente = $afip->PadronAlcance13->DniACuit($doc);
                $contribuyente = $afip->PadronAlcance13->GetTaxpayerDetails($contribuyente);
            } catch (\Throwable $th) {
                return response(['error' => $th->getMessage()], 500);
            }        
            
            
        }elseif($request->tipo == 'cuit'){
            try {
                $contribuyente = $afip->PadronAlcance13->GetTaxpayerDetails($doc);
            } catch (\Throwable $th) {
                return response(['error' => $th->getMessage()], 500);
            }
        }elseif(!$request->has('tipo')){
            if(strlen($doc) == 11){
                try {
                    $contribuyente = $afip->PadronAlcance13->GetTaxpayerDetails($doc);
                } catch (\Throwable $th) {
                    return response(['error' => $th->getMessage()], 500);
                }
            }else{
                try {
                    $contribuyente = $afip->PadronAlcance13->DniACuit($doc);
                    $contribuyente = $afip->PadronAlcance13->GetTaxpayerDetails($contribuyente);
                } catch (\Throwable $th) {
                    return response(['error' => $th->getMessage()], 500);
                }
            }
        }
        
        
        if(!$contribuyente){
            $tipoDoc = $request->tipo == 'dni' ? 'DNI' : 'CUIT/CUIL';
            return response(['error' => 'No se encontraron datos para el ' . $tipoDoc . ' ingresado'], 404);
        }
        return 
        response(datosContribuyente(contribuyenteObject: $contribuyente), 200);
    }
}