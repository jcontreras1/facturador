<?php

namespace App\Http\Controllers\Negocio;

use App\Http\Controllers\AfipWS\Afip;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class ContribuyenteController extends Controller
{
    public function padronv4(){
        $afip = new Afip();
        $contribuyente = $afip->PadronAlcance13->GetTaxpayerDetails(30670501813);
        dd($contribuyente);
        //$contribuyente = $afip->PadronAlcance13->DniACuit(92450700);
        //$contribuyente = $afip->PadronAlcance13->GetTaxpayerDetails($contribuyente);
        //dd($contribuyente);
        return response(datosContribuyente($contribuyente), 200);
        
        
    }
    public function infoContribuyente(Request $request, $doc){
        info('Consultando datos del contribuyente');
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
            
            
        }else{
            info('Consultando por CUIT/CUIL');
            try {
                $contribuyente = $afip->PadronAlcance13->GetTaxpayerDetails($doc);
            } catch (\Throwable $th) {
                return response(['error' => $th->getMessage()], 500);
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