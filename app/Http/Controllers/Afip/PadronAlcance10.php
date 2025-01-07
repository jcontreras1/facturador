<?php

namespace App\Http\Controllers\Afip;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;

/**
 * El servicio de Consulta de Padrón Alcance 10 permite que un organismo externo
 * acceda a los datos de un contribuyente registrado en el Padrón de AFIP, en su versión
 * mínima.
 */

class PadronAlcance10 extends AfipWebService {

var $soap_version 	= SOAP_1_1;
var $WSDL 			= 'ws_sr_padron_a10-production.wsdl';
var $URL 			= 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA10';
var $WSDL_TEST 		= 'ws_sr_padron_a10.wsdl';
var $URL_TEST 		= 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA10';

public function GetServerStatus()
{
    return $this->ExecuteRequest('dummy');
}

public function GetTaxpayerDetails($identifier)
{
    $ta = $this->afip->GetServiceTA('ws_sr_padron_a10');
    
    $params = array(
        'token' 			=> $ta->token,
        'sign' 				=> $ta->sign,
        'cuitRepresentada' 	=> $this->afip->CUIT,
        'idPersona' 		=> $identifier
    );

    try {
        return $this->ExecuteRequest('getPersona', $params)->persona;
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'No existe') !== FALSE)
            return NULL;
        else
            throw $e;
    }
}

public function ExecuteRequest($operation, $params = array())
{
    $results = parent::ExecuteRequest($operation, $params);

    return $results->{$operation == 'getPersona' ? 'personaReturn' : 'return'};
}
}
