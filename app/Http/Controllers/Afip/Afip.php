<?php

namespace App\Http\Controllers\Afip;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;

class Afip extends Controller
{
    // Definición de las propiedades de clase
    public $CERT;
    public $WSDL;
    public $PRIVATEKEY;
    public $PASSPHRASE;
    public $URL;
    
    // Constructor para inicializar las propiedades
    public function __construct()
    {
        if(!extension_loaded('openssl')) {
            throw new Exception('La extensión de PHP OpenSSL no está habilitada');
        }
        
        if(variable_global('AFIP_CERTIFICADO') == ''){
            throw new Exception('No se ha configurado el certificado de AFIP');
        }

        if(variable_global('AFIP_KEY') == ''){
            throw new Exception('No se ha configurado la clave privada del certificado de AFIP');
        }

        // Inicializar las propiedades con las variables globales
        $this->CERT = variable_global('AFIP_CERTIFICADO'); // Define el certificado usado para firmar
        $this->WSDL = afipDir() . "wsaa.wsdl";  // Define el WSDL correspondiente al WSAA
        $this->PRIVATEKEY = variable_global('AFIP_KEY'); // Define la clave privada del certificado
        $this->PASSPHRASE = ""; // Define el passphrase para firmar
        $this->URL = config('app.debug') === true ? "https://wsaahomo.afip.gov.ar/ws/services/LoginCms" : "https://wsaa.afip.gov.ar/ws/services/LoginCms"; // URL de WSAA
    }
    public function getTA(string $service)
    {
        // Verifica que el servicio esté presente en la solicitud
        if (empty($service)) {
            return response()->json(['error' => 'El servicio es obligatorio'], 400);
        }
        
        try {
            // Crear el archivo TRA (Ticket Request)
            $this->createTRA($service);
            echo "pasa por aca<br>";
            // Firmar el archivo TRA
            $cms = $this->signTRA();
            echo $cms;
            
            echo "pasa por aca luego firma";
            // Llamar al WSAA para obtener el ticket de acceso
            $ta = $this->callWSAA($cms);
            
            // Devolver el ticket de acceso como respuesta
            return response()->json(['TA' => $ta]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    private function createTRA($service)
    {
        $TRA = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>' . 
        '<loginTicketRequest version="1.0">' . 
        '</loginTicketRequest>');
        $TRA->addChild('header');
        $TRA->header->addChild('uniqueId', date('U'));
        $TRA->header->addChild('generationTime', date('c', date('U') - 60));
        $TRA->header->addChild('expirationTime', date('c', date('U') + 60));
        $TRA->addChild('service', $service);
        
        $TRA->asXML(afipDir() . 'TRA-' . variable_global('CUIT_EMPRESA') . '.xml');
    }
    
    private function signTRA()
    {

        echo "hola";
        $status = openssl_pkcs7_sign(
            afipDir() . "TRA-" . variable_global('CUIT_EMPRESA') . ".xml",             // El archivo TRA a firmar
            afipDir() . "TRA-" . variable_global('CUIT_EMPRESA') .".tmp",             // El archivo de salida donde se almacenará la firma
            "file://" . afipDir() . 'cert' , // El certificado utilizado para firmar (usamos la ruta del archivo)
            [
                variable_global('AFIP_KEY'),  // Aquí pasamos el contenido de la clave privada directamente
                $this->PASSPHRASE        // La passphrase para la clave privada (si existe)
            ],
            [],               // Opciones adicionales (vacío en este caso)
            !PKCS7_DETACHED         // Si queremos un archivo firmado adjunto o no (PKCS7_DETACHED)
        );
        echo "no llega aca";
        if (!$status) {
            throw new Exception("ERROR generating PKCS#7 signature");
        }
        
        $inf = fopen(afipDir() . "TRA-" . variable_global('CUIT_EMPRESA') .".tmp", "r");
        $i = 0;
        $cms = "";
        while (!feof($inf)) {
            $buffer = fgets($inf);
            if ($i++ >= 4) {
                $cms .= $buffer;
            }
        }
        fclose($inf);
        unlink(afipDir() . "TRA-" . variable_global('CUIT_EMPRESA') .".tmp");
        
        return $cms;
    }
    
    private function callWSAA($cms)
    {
        $client = new \SoapClient($this->WSDL, array(
            'soap_version' => SOAP_1_2,
            'location' => $this->URL,
            'trace' => 1,
            'exceptions' => 0
        ));
        
        $results = $client->loginCms(array('in0' => $cms));
        
        // Guardar los archivos de solicitud y respuesta para depuración
        file_put_contents(afipDir() . "requestloginCms.xml", $client->getLastRequest());
        file_put_contents(afipDir() . "responseloginCms.xml", $client->getLastResponse());
        
        if (is_soap_fault($results)) {
            throw new Exception("SOAP Fault: " . $results->faultcode . "\n" . $results->faultstring);
        }
        
        return $results->loginCmsReturn;
    }
}
