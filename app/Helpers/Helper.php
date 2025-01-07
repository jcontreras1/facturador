<?php

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
	return number_format($importe, 2, ',', '.');
}

function afipDir():string{
	return dirname($_SERVER['DOCUMENT_ROOT']) . '/afip_resources/';
}
function afipWsDir():string{
	return dirname($_SERVER['DOCUMENT_ROOT']) . '/afip_ws/';
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
function idTipoFactura($tipoFactura){
	$tipos = [
		'A' => 1,
		'B' => 6,
		'C' => 11,
		'FCEA' => 201,
		'FCEB' => 206,
		'FCEC' => 211,
		'NCA' => 3,
		'NCB' => 8,
		'NCC' => 13,
	];
	if(!array_key_exists($tipoFactura, $tipos)){
		return 0;
	}
	return $tipos[$tipoFactura];
}

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
function infoQRFactura(Factura $factura){
	// Datos de la factura
	$fecha = $factura->created_at->format('Y-m-d');
	$cuit = 0;//$factura->cliente->cuit;
	$ptoVta = $factura->pto_venta;
	$tipoCmp = idTipoFactura($factura->tipo_comprobante); // Función para determinar el tipo de comprobante
	$nroCmp = $factura->nro_factura;
	$importe = $factura->total;
	$moneda = 'PES'; // Siempre será "PES" (Pesos)
	$ctz = 1; // Cotización siempre es 1 (para PES)
	$tipoDocRec = 0;//80; // Para tipo de documento del receptor (puede variar según el cliente)
	$nroDocRec = 0;//$factura->cliente->nro_documento; // Número de documento del receptor (si corresponde)
	$tipoCodAut = 'A'; // Tipo de código de autorización (autorizado)
	$codAut = $factura->cae; // Código de autorización (CAE)
	
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

function imgBase64QRFactura(Factura $factura){
	$qr = infoQRFactura($factura);
	$qrCode = new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer(
		new \BaconQrCode\Renderer\RendererStyle\RendererStyle(150),
		new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
	));
	$svgDataUri = 'data:image/svg+xml;base64,' . base64_encode($qrCode->writeString($qr));
	return $svgDataUri;
	// return $qr;
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