<?php

namespace App\Http\Controllers\AfipWS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;

class PadronAlcance13 extends AfipWebService
{
    var $soap_version 	= SOAP_1_1;
    var $WSDL 			= 'ws_sr_padron_a13-production.wsdl';
    var $URL 			= 'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA13';
    var $WSDL_TEST 		= 'ws_sr_padron_a13.wsdl';
    var $URL_TEST 		= 'https://awshomo.afip.gov.ar/sr-padron/webservices/personaServiceA13';
    
    public function GetServerStatus()
    {
        return $this->ExecuteRequest('dummy');
    }
    
    public function GetTaxpayerDetails($identifier)
    {
        $ta = $this->afip->GetServiceTA('ws_sr_padron_a13');
        
        $params = [
            'token' 			=> $ta->token,
            'sign' 				=> $ta->sign,
            'cuitRepresentada' 	=> $this->afip->CUIT,
            'idPersona' 		=> $identifier
        ];
        
        try {
            return $this->ExecuteRequest('getPersona', $params)->persona;
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'No existe') !== FALSE)
            return NULL;
            else
            throw $e;
        }
        
    }
    
    public function DniACuit($documentNumber)
    {
        $ta = $this->afip->GetServiceTA('ws_sr_padron_a13');
        
        $params = array(
            'token' 			=> $ta->token,
            'sign' 				=> $ta->sign,
            'cuitRepresentada' 	=> $this->afip->CUIT,
            'documento' 		=> $documentNumber
        );
        
        try {
            return $this->ExecuteRequest('getIdPersonaListByDocumento', $params)->idPersona;
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
        
        return $results->{$operation == 'getPersona' ? 'personaReturn' :
            ($operation == 'getIdPersonaListByDocumento' ? 'idPersonaListReturn': 'return')
        };
    }
    
    
}
