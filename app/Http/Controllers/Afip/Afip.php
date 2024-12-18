<?php

namespace App\Http\Controllers\Afip;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;

class Afip {
    public $WSAA_WSDL;
    public $WSAA_URL;
    public $CERT;
    public $PRIVATEKEY;
    public $PASSPHRASE;
    public $RES_FOLDER;
    public $TA_FOLDER;
    public $CUIT;
   
    function __construct()
    {
        
        $this->CUIT = variable_global('CUIT_EMPRESA');
        $this->PASSPHRASE = '';
        $this->RES_FOLDER = afipDir();
        $this->TA_FOLDER = afipDir();
        $this->CERT 		= afipDir() . 'cert';
        $this->PRIVATEKEY 	= afipDir() . 'key';
        $this->WSAA_WSDL 	= afipDir() . 'wsaa.wsdl';
        
        $this->WSAA_URL = config('app.debug') === true ? "https://wsaahomo.afip.gov.ar/ws/services/LoginCms" : "https://wsaa.afip.gov.ar/ws/services/LoginCms";
        
        if (!file_exists($this->CERT)) 
        throw new Exception("No se puedo abrir el archivo: ".$this->CERT."\n");
        if (!file_exists($this->PRIVATEKEY)) 
        throw new Exception("No se puedo abrir el archivo: ".$this->PRIVATEKEY."\n");
        if (!file_exists($this->WSAA_WSDL)) 
        throw new Exception("No se puedo abrir el archivo: ".$this->WSAA_WSDL."\n");
    }
    
    
    public function GetServiceTA($service, $continue = TRUE)
    {
        if (file_exists($this->TA_FOLDER.'TA-'.$this->CUIT.'-'.$service.(config('app.debug') === FALSE ? '-production' : '').'.xml')) {
            $TA = new \SimpleXMLElement(file_get_contents($this->TA_FOLDER.'TA-'.$this->CUIT.'-'.$service.(config('app.debug') === FALSE ? '-production' : '').'.xml'));
            
            $actual_time 		= new \DateTime(date('c',date('U')+600));
            $expiration_time 	= new \DateTime($TA->header->expirationTime);
            
            if ($actual_time < $expiration_time) 
            return new TokenAuthorization($TA->credentials->token, $TA->credentials->sign);
            else if ($continue === FALSE)
            throw new Exception("Error Getting TA", 5);
        }
        
        if ($this->CreateServiceTA($service)) 
        return $this->GetServiceTA($service, FALSE);
    }
    
    
    private function CreateServiceTA($service)
    {
        //Parte 1 Crea el Ticket
        $TRA = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>' .
            '<loginTicketRequest version="1.0">'.
            '</loginTicketRequest>');
            $TRA->addChild('header');
            $TRA->header->addChild('uniqueId',date('U'));
            $TRA->header->addChild('generationTime',date('c',date('U')-600));
            $TRA->header->addChild('expirationTime',date('c',date('U')+600));
            $TRA->addChild('service',$service);
            $TRA->asXML($this->TA_FOLDER.'TRA-'.$this->CUIT.'-'.$service.'.xml');
            
            //Parte 2 Firma el Ticket
            $firma = openssl_pkcs7_sign(
                $this->TA_FOLDER."TRA-".$this->CUIT.'-'.$service.".xml",
                $this->TA_FOLDER."TRA-".$this->CUIT.'-'.$service.".tmp",
                "file://".$this->CERT,
                [
                    "file://".$this->PRIVATEKEY, 
                    $this->PASSPHRASE
                ],
                [],
                !PKCS7_DETACHED
            );
            if (!$firma) {return FALSE;}
            $inf = fopen($this->TA_FOLDER."TRA-".$this->CUIT.'-'.$service.".tmp", "r");
            $i = 0;
            $CMS="";
            while (!feof($inf)) {
                $buffer=fgets($inf);
                if ( $i++ >= 4 ) {$CMS.=$buffer;}
            }
            fclose($inf);
            unlink($this->TA_FOLDER."TRA-".$this->CUIT.'-'.$service.".xml");
            unlink($this->TA_FOLDER."TRA-".$this->CUIT.'-'.$service.".tmp");
            
            //TA a WSAA
            $client = new \SoapClient($this->WSAA_WSDL, [
                'soap_version'   => SOAP_1_2,
                'location'       => $this->WSAA_URL,
                'trace'          => 1,
                'exceptions'     => FALSE,
                'stream_context' => stream_context_create(['ssl'=> ['ciphers'=> 'AES256-SHA','verify_peer'=> false,'verify_peer_name'=> false]])
            ]); 
            $results=$client->loginCms(['in0'=>$CMS]);
            if (is_soap_fault($results)) 
            throw new Exception("SOAP Fault: ".$results->faultcode."\n".$results->faultstring."\n", 4);
            
            $TA = $results->loginCmsReturn;
            
            if (file_put_contents($this->TA_FOLDER.'TA-'.$this->CUIT.'-'.$service.(config('app.debug') === FALSE ? '-production' : '').'.xml', $TA)) 
            return TRUE;
            else
            throw new Exception('Error writing "TA-'.$this->CUIT.'-'.$service.'.xml"', 5);
        }
        
        
        public function WebService($service, $options)
        {
            $options['service'] = $service;
            $options['generic'] = TRUE;
            
            return new AfipWebService($this, $options);
        }
        
        public function __get($property)
        {
            include_once "FacturaElectronica.php";
            $ret = null;
            switch ($property) {
                case 'FacturaElectronica':
                $ret = new FacturaElectronica($this);
                break;
                
                default:
                throw new Exception("Propiedad no definida", 1);
            }
            return $this->{$property} = $ret;
        }
    }
    
    class TokenAuthorization {
        
        var $token;
        var $sign;
        
        function __construct($token, $sign)
        {
            $this->token 	= $token;
            $this->sign 	= $sign;
        }
    }
    
    class AfipWebService
    {
        public $soap_version;
        public $WSDL;
        public $URL;
        public $WSDL_TEST;
        public $URL_TEST;
        public $afip;
        public $options;
        
        function __construct($afip, $options = [])
        {
            $this->afip = $afip;
            $this->options = $options;
            
            if (isset($options['generic']) && $options['generic'] === TRUE) {
                if (!isset($options['WSDL'])) {
                    throw new Exception("El atributo WSDL es requerido.");
                }
                
                if (!isset($options['URL'])) {
                    throw new Exception("El atributo URL es requerido.");
                }
                
                if (!isset($options['WSDL_TEST'])) {
                    throw new Exception("El atributo WSDL_TEST es requerido.");
                }
                
                if (!isset($options['URL_TEST'])) {
                    throw new Exception("El atributo URL_TEST es requerido.");
                }
                
                if (!isset($options['service'])) {
                    throw new Exception("El atributo service es requerido.");
                }
                
                if (config('app.debug') === FALSE) {
                    $this->WSDL = $options['WSDL'];
                    $this->URL 	= $options['URL'];
                } else {
                    $this->WSDL = $options['WSDL_TEST'];
                    $this->URL 	= $options['URL_TEST'];
                }
                
                if (!isset($options['soap_version'])) {
                    $options['soap_version'] = SOAP_1_2;
                }
                
                $this->soap_version = $options['soap_version'];
            }
            else {
                if (config('app.debug') === FALSE) {
                    $this->WSDL = afipDir() . $this->WSDL;
                } else {
                    $this->WSDL = afipDir() . $this->WSDL_TEST;
                    $this->URL 	= $this->URL_TEST;
                }
            }
            
            if (!file_exists($this->WSDL)) 
            throw new Exception("No se pudo abrir el archivo: ".$this->WSDL."\n", 3);
        }
        
        public function GetTokenAuthorization()
        {
            return $this->afip->GetServiceTA($this->options['service']);
        }
        
        public function ExecuteRequest($operation, $params = [])
        {
            if (!isset($this->soap_client)) {
                $this->soap_client = new \SoapClient($this->WSDL, [
                    'soap_version'   => $this->soap_version,
                    'location'       => $this->URL,
                    'trace'          => 1,
                    'exceptions'     => FALSE,
                    'stream_context' => stream_context_create(['ssl'=> ['ciphers'=> 'AES256-SHA','verify_peer'=> false,'verify_peer_name'=> false]])
                ]);
            }
            
            $results = $this->soap_client->{$operation}($params);
            
            $this->_CheckErrors($operation, $results);
            
            return $results;
        }
        
        
        private function _CheckErrors($operation, $results)
        {
            if (is_soap_fault($results)) 
            throw new Exception("SOAP Fault: ".$results->faultcode."\n".$results->faultstring."\n", 4);
        }
    }
    