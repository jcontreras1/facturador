<?php

use App\Models\Arca\Comprobante;
use App\Models\Factura;
use App\Models\VariableGlobal;
use Illuminate\Support\Facades\Request;
use Carbon\Carbon;

function variable_global($clave): string{
	if(VariableGlobal::select('valor')->where('clave', $clave)->count() > 0 ){
		return VariableGlobal::select('valor')->where('clave', $clave)->first()->valor ?? '';
	}else{
		return '';
	}
}
function obj_variable_global($clave){
	if(VariableGlobal::where('clave', $clave)->first()){
		return VariableGlobal::where('clave', $clave)->first();
	}
	return false;
}

function pesosargentinos($importe){
	if (is_string($importe)) {
		$importe = floatval($importe);
	}
	return number_format($importe, 2, ',', '.');
}

function afipDir():string{
	return base_path() . '/afip_resources/';
}
function afipWsDir():string{
	return base_path() . '/afip_ws/';
}

function puntoVentaValido($puntoVenta){
	if($puntoVenta == ''){
		return false;
	}
	if(!is_numeric($puntoVenta)){
		return false;
	}
	return true;
}

function cuitGenerico(){ return 20111111112; }


function transformarArreglos($request){
	$lineas = [];
	
	// Suponiendo que todos los arreglos tienen la misma cantidad de elementos, podemos usar count() para el ciclo.
	$numLineas = count($request->cantidad);
	// Iteramos sobre los índices de los arreglos
	for ($i = 0; $i < $numLineas; $i++) {
		// Solo agregar la línea si hay valores válidos (en caso de que sean nulos o vacíos, puedes agregar una condición)
		if ($request->cantidad[$i] > 0) {
			$lineas[] = [
				"codigo" => $request->codigo[$i],
				"descripcion" => $request->descripcion[$i],
				"cantidad" => $request->cantidad[$i],
				"unidad" => $request->unidadMedida[$i],
				"precio_unitario" => $request->precioUnitario[$i],
				"porcentaje_bonificacion" => $request->porcentajeBonificacion[$i],
				"importe_bonificado" => $request->importeBonificado[$i],
				"total" => $request->subtotal[$i]
			];
		}
		
	}
	return $lineas;
}
function infoQRFactura(Comprobante $comprobante){
	// Datos de la factura
	$fecha = $comprobante->created_at->format('Y-m-d');
	$cuit = 0;//$comprobante->cliente->cuit;
	$ptoVta = $comprobante->pto_venta;
	$tipoCmp = $comprobante->tipoComprobante->codigo_afip; // Función para determinar el tipo de comprobante
	$nroCmp = $comprobante->nro_factura;
	$importe = $comprobante->total;
	$moneda = 'PES'; // Siempre será "PES" (Pesos)
	$ctz = 1; // Cotización siempre es 1 (para PES)
	$tipoDocRec = 0;//80; // Para tipo de documento del receptor (puede variar según el cliente)
	$nroDocRec = 0;//$comprobante->cliente->nro_documento; // Número de documento del receptor (si corresponde)
	$tipoCodAut = 'A'; // Tipo de código de autorización (autorizado)
	$codAut = $comprobante->cae; // Código de autorización (CAE)
	
	$jsonData = [
		"ver" => 1,
		"fecha" => $fecha,
		"cuit" => (int) $cuit,
		"ptoVta" => (int) $ptoVta,
		"tipoCmp" => (int) $tipoCmp,
		"nroCmp" => (int) $nroCmp,
		"importe" => (float) $importe,
		"moneda" => $moneda,
		"ctz" => (int) $ctz,
		"tipoDocRec" => (int) $tipoDocRec,
		"nroDocRec" => (int) $nroDocRec,
		"tipoCodAut" => $tipoCodAut,
		"codAut" => (int) $codAut
	];
	$jsonString = json_encode($jsonData);
	$base64Json = base64_encode($jsonString);
	return "https://www.afip.gob.ar/fe/qr/?p=" . $base64Json;
}

function imgBase64QRFactura(Comprobante $comprobante){
	$qr = infoQRFactura($comprobante);
	$qrCode = new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer(
		new \BaconQrCode\Renderer\RendererStyle\RendererStyle(150),
		new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
	));
	$svgDataUri = 'data:image/svg+xml;base64,' . base64_encode($qrCode->writeString($qr));
	return $svgDataUri;
	// return $qr;
}

// Functión para determinar si el usuario logeado es monotributista
function esMonotributista(){
	return str_contains(strtolower(variable_global('CONDICION_IVA')), 'monotrib');
}

function transformarFechaAfip($fecha_afip){
	// Crear una instancia de Carbon a partir del formato Ymd
	$fecha = Carbon::createFromFormat('Ymd', $fecha_afip);
	
	// Convertirla al formato Y-m-d
	return $fecha->format('Y-m-d'); 
}

function datosContribuyente($contribuyenteObject){
	$razonSocial = "";
	if($contribuyenteObject->tipoPersona == 'FISICA'){
		$razonSocial = $contribuyenteObject->apellido . ', ' . $contribuyenteObject->nombre;
	}else{
		$razonSocial = $contribuyenteObject->razonSocial;
	}
	return [
		'razonSocial' => $razonSocial,
		'domicilio' => 
			$contribuyenteObject->domicilio[count($contribuyenteObject->domicilio) - 1]->direccion . ', ' . 
			$contribuyenteObject->domicilio[count($contribuyenteObject->domicilio) - 1]->localidad . ', ' . 
			$contribuyenteObject->domicilio[count($contribuyenteObject->domicilio) - 1]->descripcionProvincia,
	];
}

//nombre del comprobante
function titulo_comprobante(Comprobante $comprobante){
	return 
		strtoupper($comprobante->tipoComprobante->letra) . 
        '-' . str_pad($comprobante->punto_venta, 5, '0', STR_PAD_LEFT) . '-' . 
        str_pad($comprobante->nro_comprobante, 8, '0', STR_PAD_LEFT);
}

//Titulo en mayúsuclas para los comprobantes en pdf, basados en el código de AFIP (que se supone que no cambia)
function tipo_comprobante(Comprobante $comprobante){
	if(in_array($comprobante->tipoComprobante->codigo_afip, [1, 6, 11, 51, 52])){
		return 'FACTURA';
	}
	if(in_array($comprobante->tipoComprobante->codigo_afip, [2, 7, 12, 53, 54])){
		return 'NOTA DE DÉBITO';
	}
	if(in_array($comprobante->tipoComprobante->codigo_afip, [3, 8, 13, 55, 56])){
		return 'NOTA DE CRÉDITO';
	}
	if(in_array($comprobante->tipoComprobante->codigo_afip, [4, 9, 15, 58])){
		return 'RECIBO';
	}
	if(in_array($comprobante->tipoComprobante->codigo_afip, [5, 10, 16, 59])){
		return 'NOTA DE VENTA AL CONTADO';
	}
	if(in_array($comprobante->tipoComprobante->codigo_afip, [19])){
		return 'FACTURA DE CRÉDITO ELECTRÓNICA';
	}
	if(in_array($comprobante->tipoComprobante->codigo_afip, [20])){
		return 'FACTURA DE VENTA DE BIENES USADOS';
	}
	if(in_array($comprobante->tipoComprobante->codigo_afip, [21])){
		return 'FACTURA DE VENTA DE COSAS MUEBLES';
	}
	if(in_array($comprobante->tipoComprobante->codigo_afip, [22])){
		return 'LIQUIDACIÓN DE COMPRA';
	}
	if(in_array($comprobante->tipoComprobante->codigo_afip, [23])){
		return 'LIQUIDACIÓN DE COMPRA DE BIENES USADOS';
	}
	if(in_array($comprobante->tipoComprobante->codigo_afip, [24])){
		return 'LIQUIDACIÓN DE COMPRA DE COSAS MUEBLES';
	}
	if(in_array($comprobante->tipoComprobante->codigo_afip, [25])){
		return 'FACTURA DE EXPORTACIÓN';
	}
	if(in_array($comprobante->tipoComprobante->codigo_afip, [26])){
		return 'NOTA DE DÉBITO DE EXPORTACIÓN';
	}
	if(in_array($comprobante->tipoComprobante->codigo_afip, [27])){
		return 'NOTA DE CRÉDITO DE EXPORTACIÓN';
	}
	if(in_array($comprobante->tipoComprobante->codigo_afip, [28])){
		return 'RECIBO DE EXPORTACIÓN';
	}
	if(in_array($comprobante->tipoComprobante->codigo_afip, [29])){
		return 'NOTA DE VENTA DE BIENES USADOS DE EXPORTACIÓN';
	}
	return 'COMPROBANTE';
}