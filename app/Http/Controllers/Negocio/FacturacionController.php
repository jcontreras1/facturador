<?php

namespace App\Http\Controllers\Negocio;

use App\Http\Controllers\Afip\Afip;
use App\Http\Controllers\Controller;
use App\Http\Requests\Facturas\FacturaCGenericaRequest;
use App\Models\Factura;
use App\Models\ItemFactura;
use Illuminate\Http\Request;

class FacturacionController extends Controller
{
    public function index(){
        $facturas = Factura::paginate(10);
        return view('facturacion.index')->with(compact('facturas'));
    }
    public function createFacturaCGenerica(){
        return view('facturacion.createFacturaCGenerica');
    }
    /**
    * Crear factura C de monotributista
    * @param \Illuminate\Http\Request $request
    * @return void
    */
    public function facturaCGenerica(FacturaCGenericaRequest $request){
        
        $detalle = transformarArreglos($request);
        
        $factura = Factura::create(array_merge( $request->all(), [
            'total' => $request->importeTotal,
            'total_neto' => $request->importeTotal,
            'created_by' => auth()->user()->id,
            'punto_venta' => variable_global('PUNTO_VENTA'),
            'tipo_comprobante' => 'C'
            ])
        );
        
        foreach ($detalle as $item) {
            ItemFactura::create(array_merge(
                $item,
                ['factura_id' => $factura->id]
            ));
        }
        
        $afip = new Afip();
        $puntoVenta = intval(variable_global('PUNTO_VENTA'));
        if(!puntoVentaValido($puntoVenta))
        toast('El punto de venta no es válido o no está definido. Revise las configuraciones del sistema', 'error')->autoClose(5000);
        
        $ultimaFactura = $afip->FacturaElectronica->GetLastVoucher($puntoVenta, idTipoFactura('C')) + 1;
        $factura->update(['nro_factura' => $ultimaFactura]);
        $fechaServicioDesde = null;
        $fechaServicioHasta = null;
        $fechaVencimientoPago = null;
        $concepto = $request->concepto;
        
        if ($concepto == 2 || $concepto == 3) {
            $fechaServicioDesde = intval(date('Ymd'));
            $fechaServicioHasta = intval(date('Ymd'));
            $fechaVencimientoPago = intval(date('Ymd'));
        }

        $fact = [
            /* Del comprobante */
            'CantReg' 	=> 1,
            'PtoVta' 	=> $puntoVenta,
            'CbteDesde' => $ultimaFactura,
            'CbteHasta' => $ultimaFactura,
            'CbteTipo' 	=> idTipoFactura('C'), 
            'CbteFch' 	=> intval(str_replace('-', '', $request->fecha)),
            
            /* Del contenido */
            'Concepto' 	=> intval($request->concepto),
            'FchServDesde'  => intval($fechaServicioDesde),
            'FchServHasta'  => intval($fechaServicioHasta),
            'FchVtoPago'    => intval($fechaVencimientoPago),
            
            /* Del cliente */
            'DocTipo' 	=> intval($request->tipoDocuemnto),
            'DocNro' 	=> $request->documento ? intval($request->documento) : 0,
            
            /* Del importe */
            'ImpTotal' 	=> intval($request->importeTotal),
            'ImpNeto' 	=> intval($request->importeTotal),
            'ImpTotConc'=> 0,
            'ImpOpEx' 	=> 0,
            'ImpIVA' 	=> 0,
            'ImpTrib' 	=> 0,
            'MonId' 	=> 'PES',
            'MonCotiz' 	=> 1,
        ];
        try{ 
            $afipResponse = $afip->FacturaElectronica->CreateVoucher($fact, TRUE);
        }
        catch(\Exception $e)        {
            alert('Error al intentar crear la factura electrónica.', $e->getMessage() , 'error');
            return redirect()->back()->withInput();
        }
        
        if($afipResponse->FeDetResp && $afipResponse->FeDetResp->FECAEDetResponse->Resultado == 'A'){
            $factura->update([
                'cae' => $afipResponse->FeDetResp->FECAEDetResponse->CAE, 
                // 'caeVto' => $afipResponse['vto'],
                'enviada_afip' => true,
                
            ]);
            toast('Factura generada correctamente', 'success')->autoClose(1500);
        }
        return redirect()->route('facturacion.index');
    }
}