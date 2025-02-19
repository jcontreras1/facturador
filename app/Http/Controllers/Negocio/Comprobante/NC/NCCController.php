<?php

namespace App\Http\Controllers\Negocio\Comprobante\NC;

use App\Http\Controllers\AfipWS\Afip;
use App\Http\Controllers\Controller;
use App\Models\Arca\Comprobante;
use App\Models\Arca\DetalleComprobante;
use App\Models\Arca\TipoComprobante;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;


class NCCController extends Controller
{
    //HAce una nota de crédito a partir de un comprobante ya emitido
    public function anular(Comprobante $comprobante){
        if($comprobante->anulacion_id){
            alert('Error', 'El comprobante ya ha sido anulado', 'error')->autoClose(0);
            return redirect()->back();
        }
        $afip = new Afip();
        //comprobante N(CC)
        $tipoComprobante = TipoComprobante::where('codigo', 'CC')->first();
        $punto_de_venta = variable_global('PUNTO_VENTA');
        $last_voucher = $afip->FacturaElectronica->GetLastVoucher($punto_de_venta, $tipoComprobante->codigo_afip);
        $numero = $last_voucher + 1;
        
        $fecha_servicio_desde = null;
        $fecha_servicio_hasta = null;
        $fecha_vencimiento_pago = null;
        if ($comprobante->concepto === 2 || $comprobante->concepto === 3) {
            $fecha_servicio_desde = intval(str_replace('-', '', $comprobante->fecha_servicio_desde));
            $fecha_servicio_hasta = intval(str_replace('-', '', $comprobante->fecha_servicio_hasta));
            if(Carbon::parse($fecha_vencimiento_pago)->startOfDay()->isPast()){
                $fecha_vencimiento_pago = intval(date('Ymd'));
            }else{
                $fecha_vencimiento_pago = intval(str_replace('-', '', $comprobante->fecha_vencimiento_pago));
            }
        }

        $data = [
            'CantReg' => 1,
            'PtoVta' => intval($punto_de_venta),
            'CbteTipo' => intval($tipoComprobante->codigo_afip),
            'Concepto' => $comprobante->concepto,
            'DocTipo' => $comprobante->tipo_documento_id,
            'DocNro' => $comprobante->cuit_dni ?? 0,
            'CbteDesde' => $numero,
            'CbteHasta' => $numero,
            'CbteFch' => intval(date('Ymd')),
            'FchServDesde' => $fecha_servicio_desde,
            'FchServHasta' => $fecha_servicio_hasta,
            'FchVtoPago' => $fecha_vencimiento_pago,
            'ImpTotal' => $comprobante->importe_total,
            'ImpTotConc' => 0,
            'ImpNeto' => $comprobante->importe_total,
            'ImpOpEx' => 0,
            'ImpIVA' => 0,
            'ImpTrib' => 0,
            'CondicionIVAReceptorId' => $comprobante->condicion_iva_receptor_id,
            'MonId' => 'PES',
            'MonCotiz' => 1,
            'CbtesAsoc' => [
                [
                    'Tipo' => $comprobante->tipoComprobante->codigo_afip,
                    'PtoVta' => $comprobante->punto_venta,
                    'Nro' => $comprobante->nro_comprobante,
                ]
            ]
        ];

        DB::beginTransaction();
        try {
            $notaDeCredito = Comprobante::create([
                'cuit_dni' => $comprobante->cuit_dni,
                'razon_social' => $comprobante->razon_social,
                'domicilio' => $comprobante->domicilio,
                'tipo_documento_id' => $comprobante->tipo_documento_id,
                'tipo_comprobante_id' => $tipoComprobante->id,
                'punto_venta' => $punto_de_venta,
                'nro_comprobante' => $numero,
                'created_by' => auth()->user()->id,
                'importe_neto' => $comprobante->importe_neto,
                'importe_gravado' => $comprobante->importe_gravado,
                'cliente_id' => $comprobante->cliente_id,
                'importe_no_gravado' => $comprobante->importe_no_gravado,
                'importe_exento_iva' => $comprobante->importe_exento_iva,
                'importe_iva' => $comprobante->importe_iva,
                'importe_total_tributos' => $comprobante->importe_total_tributos,
                'importe_total' => $comprobante->importe_total,
                'observaciones' => $comprobante->observaciones,
                'condicion_iva_receptor_id' => $comprobante->condicion_iva_receptor_id,
                'fecha_emision' => now(),
                'fecha_servicio_desde' => $comprobante->fecha_servicio_desde,
                'fecha_servicio_hasta' => $comprobante->fecha_servicio_hasta,
                'fecha_vencimiento_pago' => $comprobante->fecha_vencimiento_pago,
                'concepto' => $comprobante->concepto,
            ]);

            DetalleComprobante::create([
                'comprobante_id' => $notaDeCredito->id,
                'descripcion' => 'Nota de crédito por anulación de comprobante',
                'cantidad' => 1,
                'unidad_medida' => 'UN',
                'importe_unitario' => $comprobante->importe_total,
                'porcentaje_descuento' => 0,
                'importe_descuento' => 0,
                'importe_subtotal' => $comprobante->importe_total,
            ]);

            $comprobante->update([
                'anulacion_id' => $notaDeCredito->id
            ]);

            $res = $afip->FacturaElectronica->CreateVoucher($data);

            

            $notaDeCredito->update([
                'cae' => $res['CAE'],
                'fecha_vencimiento_cae' => $res['CAEFchVto']
            ]);


            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            alert('Error', 'Ha ocurrido un error al anular el comprobante: ' . $e->getMessage(), 'error')->autoClose(0);
            return redirect()->back();
        }
        toast('Nota de crédito generada correctamente', 'success')->autoClose(1500);
        return redirect()->back();

    }
}
