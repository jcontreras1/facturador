<?php

namespace App\Http\Controllers\AfipWS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;

/**
 * Este WS se puede utilizar para acceder a datos de un contribuyente relacionados con su
 * constancia de inscripción. 
 */
class PadronAlcance5 extends AfipWebService {

	var $soap_version 	= SOAP_1_1;
	var $WSDL 			= 'ws_sr_padron_a5-production.wsdl';
	var $URL 			= 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA5';
	var $WSDL_TEST 		= 'ws_sr_padron_a5.wsdl';
	var $URL_TEST 		= 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA5';

	public function GetServerStatus()
	{
		return $this->ExecuteRequest('dummy');
	}

	public function GetTaxpayerDetails($identifier)
	{
		$ta = $this->afip->GetServiceTA('ws_sr_padron_a5');
		
		$params = array(
			'token' 			=> $ta->token,
			'sign' 				=> $ta->sign,
			'cuitRepresentada' 	=> $this->afip->CUIT,
			'idPersona' 		=> $identifier
		);

		try {
			return $this->ExecuteRequest('getPersona_v2', $params);
		} catch (Exception $e) {
			if (strpos($e->getMessage(), 'No existe') !== FALSE)
				return NULL;
			else
				throw $e;
		}
	}

	public function GetTaxpayersDetails($identifiers)
	{
		$ta = $this->afip->GetServiceTA('ws_sr_padron_a5');
		
		$params = array(
			'token' 			=> $ta->token,
			'sign' 				=> $ta->sign,
			'cuitRepresentada' 	=> $this->afip->CUIT,
			'idPersona' 		=> $identifiers
		);

		return $this->ExecuteRequest('getPersonaList_v2', $params)->persona;
	}

	public function ExecuteRequest($operation, $params = array())
	{
		$results = parent::ExecuteRequest($operation, $params);

		return $results->{
			$operation === 'getPersona_v2' ? 'personaReturn' :
				($operation === 'getPersonaList_v2' ? 'personaListReturn': 'return')
			};
	}
}
