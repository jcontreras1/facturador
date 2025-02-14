<?php

namespace App\Http\Controllers\AfipWS;

// use App\Http\Controllers\Afip\AfipWebService;

class FacturaElectronica extends AfipWebService
{
    
	var $soap_version 	= SOAP_1_2;
	var $WSDL 			= 'wsfe-production.wsdl';
	var $URL 			= 'https://servicios1.afip.gov.ar/wsfev1/service.asmx';
	var $WSDL_TEST 		= 'wsfe.wsdl';
	var $URL_TEST 		= 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx';

	public function GetLastVoucher($puntoVenta, $type)
	{
		$req = [
			'PtoVta' 	=> $puntoVenta,
			'CbteTipo' 	=> $type
		];

		return $this->ExecuteRequest('FECompUltimoAutorizado', $req)->CbteNro;
	}

	public function CreateVoucher($data, $return_response = FALSE)
	{
		$req = [
			'FeCAEReq' => [
				'FeCabReq' => [
					'CantReg' 	=> $data['CbteHasta'] - $data['CbteDesde'] + 1,
					'PtoVta' 	=> $data['PtoVta'],
					'CbteTipo' 	=> $data['CbteTipo']
				],
				'FeDetReq' => [
					'FECAEDetRequest' => &$data
				]
			]
		];

		unset($data['CantReg']);
		unset($data['PtoVta']);
		unset($data['CbteTipo']);

		if (isset($data['Tributos'])) 
			$data['Tributos'] = ['Tributo' => $data['Tributos']];

		if (isset($data['Iva'])) 
			$data['Iva'] = ['AlicIva' => $data['Iva']];

		if (isset($data['Opcionales'])) 
			$data['Opcionales'] = ['Opcional' => $data['Opcionales']];

		$results = $this->ExecuteRequest('FECAESolicitar', $req);

		if ($return_response === TRUE) {
			return $results;
		} else {
			return [
				'CAE' 		=> $results->FeDetResp->FECAEDetResponse->CAE,
				'CAEFchVto' => $this->FormatDate($results->FeDetResp->FECAEDetResponse->CAEFchVto),
			];
		}
	}
	public function GetVoucherInfo($numero, $puntoVenta, $tipo)
	{
		$req = [
			'FeCompConsReq' => [
				'CbteNro' 	=> $numero,
				'PtoVta' 	=> $puntoVenta,
				'CbteTipo' 	=> $tipo
			]
		];

		try {
			$result = $this->ExecuteRequest('FECompConsultar', $req);
		} catch (Exception $e) {
			if ($e->getCode() == 602) 
				return NULL;
			else
				throw $e;
		}

		return $result->ResultGet;
	}

	public function GetSalesPoints()
	{
		return $this->ExecuteRequest('FEParamGetPtosVenta')->ResultGet->PtoVenta;
	}

	public function GetVoucherTypes()
	{
		return $this->ExecuteRequest('FEParamGetTiposCbte')->ResultGet->CbteTipo;
	}

	public function GetConceptTypes()
	{
		return $this->ExecuteRequest('FEParamGetTiposConcepto')->ResultGet->ConceptoTipo;
	}

	public function GetDocumentTypes()
	{
		return $this->ExecuteRequest('FEParamGetTiposDoc')->ResultGet->DocTipo;
	}

	public function GetAliquotTypes()
	{
		return $this->ExecuteRequest('FEParamGetTiposIva')->ResultGet->IvaTipo;
	}

	public function GetCurrenciesTypes()
	{
		return $this->ExecuteRequest('FEParamGetTiposMonedas')->ResultGet->Moneda;
	}

	public function GetOptionsTypes()
	{
		return $this->ExecuteRequest('FEParamGetTiposOpcional')->ResultGet->OpcionalTipo;
	}

	public function GetTaxTypes()
	{
		return $this->ExecuteRequest('FEParamGetTiposTributos')->ResultGet->TributoTipo;
	}

	public function GetServerStatus()
	{
		return $this->ExecuteRequest('FEDummy');
	}

	public function FormatDate($date)
	{
		return date_format(\DateTime::CreateFromFormat('Ymd', $date.''), 'Y-m-d');
	}

	public function ExecuteRequest($operation, $params = [])
	{
		$params = array_replace($this->GetWSInitialRequest($operation), $params); 

		$results = parent::ExecuteRequest($operation, $params);

		$this->_CheckErrors($operation, $results);

		return $results->{$operation.'Result'};
	}

	private function GetWSInitialRequest($operation)
	{
		$ta = $this->afip->GetServiceTA('wsfe');

		return [
			'Auth' => [ 
				'Token' => $ta->token,
				'Sign' 	=> $ta->sign,
				'Cuit' 	=> $this->afip->CUIT
			]
		];
	}

	private function _CheckErrors($operation, $results)
	{
		$res = $results->{$operation.'Result'};

		if ($operation == 'FECAESolicitar') {
			if (isset($res->FeDetResp->FECAEDetResponse->Observaciones) && $res->FeDetResp->FECAEDetResponse->Resultado != 'A') {
				$res->Errors = new \StdClass();
				$res->Errors->Err = $res->FeDetResp->FECAEDetResponse->Observaciones->Obs;
			}
		}

		if (isset($res->Errors)) {
			$err = is_array($res->Errors->Err) ? $res->Errors->Err[0] : $res->Errors->Err;
			throw new \Exception('('.$err->Code.') '.$err->Msg, $err->Code);
		}
	}
}
