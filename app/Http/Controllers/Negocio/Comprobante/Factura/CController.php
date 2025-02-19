<?php

namespace App\Http\Controllers\Negocio\Comprobante\Factura;

use App\Http\Controllers\AfipWS\Afip;
use App\Http\Controllers\Controller;
use App\Models\Arca\Comprobante;
use App\Models\Arca\DetalleComprobante;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CController extends Controller
{
    public function store(Request $request){
        $tipoComprobante = Comprobante::where('codigo', 'C')->first();
        $afip = new Afip();
        $cliente = $request->has('cliente') ? $request->cliente : null;
        DB::beginTransaction();
        try {
            $puntoVenta = variable_global('PUNTO_VENTA');

            $comprobante = Comprobante::create([
                'tipo_documento_id' => $request->tipoDocumentoId,
                'cuit_dni' => $request->documento,
                'razon_social' => $request->razonSocial,
                'domicilio' => $request->domicilio,
                'tipo_comprobante_id' => $tipoComprobante->id,
                'punto_venta' => $puntoVenta,
                'created_by' => auth()->user()->id,
                'importe_neto' => $request->importeNeto,
                'importe_total' => $request->importeTotal,
                'condicion_iva_receptor_id' => $request->condicionIva,
                'fecha_emision' => $request->fecha,
                'concepto' => $request->concepto,
                'fecha_servicio_desde' => $request->concepto == '1' ? null : $request->fechaInicioServicios,
                'fecha_servicio_hasta' => $request->concepto == '1' ? null : $request->fechaFinServicios,
                'fecha_vencimiento_pago' => $request->concepto == '1' ? null : $request->fechaVencimientoPago,
                'cliente_id' => $cliente,
            ]);
            
            foreach($request->lineas as $linea){
                DetalleComprobante::create([
                    'comprobante_id' => $comprobante->id,
                    'descripcion' => $linea['descripcion'],
                    'cantidad' => $linea['cantidad'],
                    'unidad_medida' => $linea['unidadDeMedida'],
                    'importe_unitario' => $linea['precioUnitario'],
                    'porcentaje_descuento' => $linea['bonificacion'],
                    'importe_descuento' => $linea['importeBonificado'],
                    'importe_subtotal' => $linea['subtotal'],
                ]);
            }

            //ARCA
            
            $numeroComprobante = $afip->FacturaElectronica->GetLastVoucher($puntoVenta, $tipoComprobante->codigo_afip);
            $numeroComprobante++;

            $data = [
                'CantReg' => 1,
                'PtoVta' => intval($puntoVenta),
                'CbteTipo' => intval($tipoComprobante->codigo_afip),
                'Concepto' => intval($request->concepto),
                'DocTipo' => intval($request->tipoDocumentoId),
                'DocNro' => $request->documento ? intval($request->documento) : 0,
                'CondicionIVAReceptorId' => intval($request->condicionIva),
                'CbteDesde' => $numeroComprobante,
                'CbteHasta' => $numeroComprobante,
                'CbteFch' => intval(str_replace('-', '', $request->fecha)),
                'ImpTotal' => $request->importeTotal,
                'ImpTotConc' => 0,
                'ImpNeto' => $request->importeTotal,
                'ImpOpEx' => 0,
                'ImpIVA' => 0,
                'ImpTrib' => 0,
                'MonId' => 'PES',
                'MonCotiz' => 1,
            ];

            if ($request->concepto == 1) {
                $data['FchServDesde'] = null;
                $data['FchServHasta'] = null;
                $data['FchVtoPago'] = null;
            } else {
                $data['FchServDesde'] = intval(date('Ymd', strtotime($request->fechaInicioServicios)));
                $data['FchServHasta'] = intval(date('Ymd', strtotime($request->fechaFinServicios)));
                $data['FchVtoPago'] = intval(date('Ymd', strtotime($request->fechaVencimientoPago)));
            }

            $afipComp = $afip->FacturaElectronica->CreateVoucher($data);

            $comprobante->update([
                'cae' => $afipComp['CAE'],
                'fecha_vencimiento_cae' => $afipComp['CAEFchVto'],
                'nro_comprobante' => $numeroComprobante,
            ]);

            // return response('Parece que sim che', 500);
            DB::commit();

        } catch (Exception $th) {
            DB::rollBack();
            return response()->json(['message' => 'Error al guardar el comprobante: ' . $th->getMessage()], 500);
        }

        return response()->json(['message' => 'Comprobante guardado correctamente'], 201);



    }
}
