<?php

namespace App\Http\Controllers\Negocio\Comprobante\Factura;

use App\Http\Controllers\AfipWS\Afip;
use App\Http\Controllers\Controller;
use App\Models\Arca\Comprobante;
use App\Models\Arca\DetalleComprobante;
use App\Models\Arca\IvaReceptor;
use App\Models\Arca\TipoComprobante;
use App\Models\Cliente;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CController extends Controller
{
    public function store(Request $request){
        $tipoComprobante = TipoComprobante::where('codigo', 'C')->first();
        $afip = new Afip();
        $cliente = $request->has('cliente') && $request->cliente ? $request->cliente : null;
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
                    'importe_subtotal_con_iva' => $linea['subtotal'],
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
    
    public static function facturacionMensual(
        Cliente $cliente, 
        $periodoDesde = null, 
        $periodoHasta = null, 
        $periodoVencimiento = null): Comprobante | Exception{

            // throw new Exception('Error en la facturación mensual. Cliente: ' . $cliente->nombre . ', Periodo Desde: ' . $periodoDesde . ', Periodo Hasta: ' . $periodoHasta . ', Periodo Vencimiento: ' . $periodoVencimiento);

        $tipoComprobante = TipoComprobante::where('codigo', 'C')->first(); // Obtener tipo de comprobante 'C'
        $afip = new Afip();
        DB::beginTransaction();
        // if(!$cliente->condicionIva){
        //     throw new Exception('El cliente '.$cliente->nombre.' no tiene condición de IVA asignada');
        // }
        if(!$cliente->tipoDocumento){
            throw new Exception('El cliente '.$cliente->nombre.' no tiene tipo de documento asignado');
        }
        try {            
            // Configuramos el punto de venta
            $puntoVenta = variable_global('PUNTO_VENTA');
            // Crear el comprobante
            $comprobante = Comprobante::create([
                'tipo_documento_id' => $cliente->tipo_documento_afip,
                'cuit_dni' => $cliente->cuit,
                'razon_social' => $cliente->nombre,
                'domicilio' => $cliente->direccion,
                'tipo_comprobante_id' => $tipoComprobante->id,
                'punto_venta' => $puntoVenta,
                'created_by' => auth()->user()->id,
                'importe_neto' => $cliente->servicios()->sum('importe_neto'),
                'importe_total' => $cliente->servicios()->sum('importe_total'),
                'condicion_iva_receptor_id' => $cliente->condicionIva->id,
                'fecha_emision' => today()->format('Y-m-d'),
                'fecha_servicio_desde' => intval($periodoDesde) ? intval(date('Ymd', strtotime($periodoDesde))) : intval(today()->startOfMonth()->format('Ymd')),
                'fecha_servicio_hasta' => intval($periodoHasta) ? intval(date('Ymd', strtotime($periodoHasta))) : intval(today()->endOfMonth()->format('Ymd')),
                'fecha_vencimiento_pago' => intval($periodoVencimiento) ? intval(date('Ymd', strtotime($periodoVencimiento))) : intval(today()->addDays(10)->format('Ymd')),
                'concepto' => 2, // 2: servicios
                'cliente_id' => $cliente->id,
            ]);
            
            // Crear detalles del comprobante
            foreach ($cliente->servicios as $servicio) {
                DetalleComprobante::create([
                    'comprobante_id' => $comprobante->id,
                    'descripcion' => $servicio->descripcion,
                    'cantidad' => $servicio->cantidad,
                    'unidad_medida' => 'unidad', // Se asume que es una unidad por ahora
                    'importe_unitario' => $servicio->importe_neto,
                    'porcentaje_descuento' => 0,
                    'importe_descuento' => 0,
                    'importe_subtotal' => $servicio->importe_total,
                ]);
            }
            
            // Obtener el número de comprobante
            $numeroComprobante = $afip->FacturaElectronica->GetLastVoucher($puntoVenta, $tipoComprobante->codigo_afip);
            $numeroComprobante++;
            
            // Preparar datos para la AFIP
            $data = [
                'CantReg' => 1,
                'PtoVta' => intval($puntoVenta),
                'CbteTipo' => intval($tipoComprobante->codigo_afip),
                'Concepto' => 2, // Servicios
                'DocTipo' => intval($cliente->tipoDocumento), // Tipo de documento 'CUIT'
                'DocNro' => $cliente->cuit,
                'CondicionIVAReceptorId' => intval($cliente->condicionIva->codigo_afip),
                'CbteDesde' => $numeroComprobante,
                'CbteHasta' => $numeroComprobante,
                'CbteFch' => intval(now()->format('Ymd')),
                'FchServDesde' => intval(date('Ymd', strtotime($comprobante->fecha_servicio_desde))),
                'FchServHasta' => intval(date('Ymd', strtotime($comprobante->fecha_servicio_desde))),
                'FchVtoPago' => intval(date('Ymd', strtotime($comprobante->fecha_vencimiento_pago))),
                'ImpTotal' => $cliente->servicios()->sum(column: 'importe_total'),
                'ImpTotConc' => 0,
                'ImpNeto' => $cliente->servicios()->sum('importe_neto'),
                'ImpOpEx' => 0,
                'ImpIVA' => 0,
                'ImpTrib' => 0,
                'MonId' => 'PES',
                'MonCotiz' => 1,
            ];
            
            // Crear el comprobante en la AFIP
            $afipComp = $afip->FacturaElectronica->CreateVoucher($data);
            
            // Actualizar el comprobante con el CAE y la fecha de vencimiento
            $comprobante->update([
                'cae' => $afipComp['CAE'],
                'fecha_vencimiento_cae' => $afipComp['CAEFchVto'],
                'nro_comprobante' => $numeroComprobante,
            ]);
            
            DB::commit();
            
        } catch (Exception $th) {
            DB::rollBack();
            
            throw new Exception($th->getMessage());
        }
        
        return $comprobante;
    }
    public function storeLoteFacturasC(Request $request){
        $tipoComprobante = TipoComprobante::where('codigo', 'C')->first();
        $afip = new Afip();
        DB::beginTransaction();
        try {
            $puntoVenta = variable_global('PUNTO_VENTA');
            $importeNeto = round($request->importe_total / intval($request->cant_comprobantes),2);
            $numeroComprobante = $afip->FacturaElectronica->GetLastVoucher($puntoVenta, $tipoComprobante->codigo_afip);
            for($i = 0; $i < intval($request->cant_comprobantes); $i++){
                
                $numeroComprobante++;

                $comprobante = Comprobante::create([
                    'tipo_comprobante_id' => $tipoComprobante->id,
                    'punto_venta' => $puntoVenta,
                    'created_by' => auth()->user()->id,
                    'condicion_iva_receptor_id' => IvaReceptor::where('codigo_afip', 5)->first()->id,
                    'fecha_emision' => today()->format('Y-m-d'),
                    'concepto' => $request->concepto,
                    'fecha_servicio_desde' => $request->concepto == '1' ? null : $request->fecha_servicio_desde,
                    'fecha_servicio_hasta' => $request->concepto == '1' ? null : $request->fecha_servicio_hasta,
                    'fecha_vencimiento_pago' => $request->concepto == '1' ? null : $request->fecha_vencimiento_pago,
                    'importe_neto' => $importeNeto,
                    'importe_total' => $importeNeto,
                ]);

                
                DetalleComprobante::create([
                    'comprobante_id' => $comprobante->id,
                    'descripcion' => $request->descripcion,
                    'cantidad' => 1,
                    'unidad_medida' => 'unidad',
                    'importe_unitario' => $importeNeto,
                    'importe_subtotal' => $importeNeto,
                ]);
                
                $data = [
                    'CantReg' => 1,
                    'PtoVta' => intval($puntoVenta),
                    'CbteTipo' => intval($tipoComprobante->codigo_afip),
                    'Concepto' => intval($request->concepto),
                    'DocTipo' => 99,
                    'DocNro' => 0,
                    'CondicionIVAReceptorId' => 5, // Consumidor final
                    'CbteDesde' => $numeroComprobante,
                    'CbteHasta' => $numeroComprobante,
                    'CbteFch' => intval(str_replace('-', '', today()->format('Y-m-d'))),
                    'ImpTotal' => $importeNeto,
                    'ImpTotConc' => 0,
                    'ImpNeto' => $importeNeto,
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
                    $data['FchServDesde'] = intval(date('Ymd', strtotime($request->fecha_servicio_desde)));
                    $data['FchServHasta'] = intval(date('Ymd', strtotime($request->fecha_servicio_hasta)));
                    $data['FchVtoPago'] = intval(date('Ymd', strtotime($request->fecha_vencimiento_pago)));
                }

                // return $data;

                $afipComp = $afip->FacturaElectronica->CreateVoucher($data);
                
                $comprobante->update([
                    'cae' => $afipComp['CAE'],
                    'fecha_vencimiento_cae' => $afipComp['CAEFchVto'],
                    'nro_comprobante' => $numeroComprobante,
                ]);   
            }
            
            DB::commit();
            toast('Facturas guardadas correctamente', 'success')->autoClose(1500);
            return redirect()->route('comprobantes.index');
        } catch (Exception $th) {
            DB::rollBack();
            alert('Error', 'Error al guardar las facturas: ' . $th->getMessage(), 'error')->autoClose(0);
            return redirect()->back()->withInput();
        }
    }
}
