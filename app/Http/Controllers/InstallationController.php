<?php

namespace App\Http\Controllers;

use App\Models\VariableGlobal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class InstallationController extends Controller
{
    public function makeKey(){
        // Verifica que OpenSSL esté habilitado
        if (!extension_loaded('openssl')) {
            toast('El servidor no tiene la extensión OpenSSL activada', 'error');
            return redirect()->back();
        }
        
        // Configuración para la clave RSA de 2048 bits
        $config = [
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];
        
        // Crear un nuevo par de claves RSA
        $res = openssl_pkey_new($config);
        
        if ($res === false) {
            toast('Error al generar una nueva clave: ' . openssl_error_string(), 'error');
            return redirect()->back();
        }
        
        // Exportar la clave privada
        if (openssl_pkey_export($res, $privateKey) === false) {
            toast('Error al exportar la clave privada', 'error');
            return redirect()->back();
        }
        
        if (!is_dir(afipDir())) {
            if (!mkdir(afipDir(), 0755, true)) {
                toast('Error al crear la carpeta para la clave privada', 'error');
                return redirect()->back();
            }
        }

        $filePath = afipDir() . 'key';  // Archivo donde se guardará la clave privada
        
        // Escribir la clave privada en el archivo
        if (file_put_contents($filePath, $privateKey) === false) {
            toast('Error al guardar la clave privada en el archivo', 'error');
            return redirect()->back();
        }
        
        toast('Clave privada generada con éxito', 'success');
        return redirect()->back();
    }
    
    public function makeCSR(){
        // Verifica que OpenSSL esté habilitado
        if (!extension_loaded('openssl')) {
            toast('El servidor no tiene la extensión OpenSSL activada', 'error');
            return redirect()->back();
        }
        // Verifica que la clave privada esté generada
        $keyPath = afipDir() . 'key';
        if (!is_file($keyPath) || !is_readable($keyPath)) {
            toast('Primero debe generar o instalar la clave privada en afip_resources/key', 'error');
            return redirect()->back();
        }
        $privateKey = @openssl_pkey_get_private(file_get_contents($keyPath));
        if ($privateKey === false) {
            toast('El archivo afip_resources/key no contiene una clave privada PEM valida sin contrasena', 'error');
            return redirect()->back();
        }
        // Verifica que exista la cuit
        $cuit = variable_global('CUIT_EMPRESA');
        if (!$cuit || $cuit == "") {
            toast('Debe establecer la cuit de la empresa previamente', 'error');
            return redirect()->back();
        }
        // Verifica que exista la cuit
        $nombreEmpresa = strtoupper(variable_global('RAZON_SOCIAL'));
        if (!$nombreEmpresa || $nombreEmpresa == "") {
            return redirect()->back()->withErrors(['razon_social' => 'Debe establecer la razón social de la empresa previamente. Procurar guardarla tal cual esté registrada en AFIP.']);
        }
        
        
        // Configuración del CSR (Solicitud de firma de certificado)
        $dn = [
            "C" => "AR",
            "O" => variable_global('RAZON_SOCIAL'),
            // "CN" => implode('.', array_slice(explode('.', preg_replace('/^www\./', '', parse_url(config('app.url'), PHP_URL_HOST))), 0, -1)),
            "CN" => variable_global('RAZON_SOCIAL'),
            "serialNumber" => "CUIT " .  preg_replace('/[^0-9]/', '', variable_global('CUIT_EMPRESA'))
        ];
        
        $config = [
            "digest_alg" => "sha256",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
            "private_key" => $privateKey
        ];
        
        //Generar el certificado
        $csr = openssl_csr_new($dn, $privateKey, $config);
        if($csr){
            // Exportar el CSR a una variable (en lugar de un archivo)
            openssl_csr_export($csr, $csrContent);
            
            return Response::make($csrContent, 200, [
                'Content-Type' => 'application/pkcs10',
                'Content-Disposition' => 'attachment; filename="pedido.csr"',
            ]);
        } else {
            toast('No se pudo generar ni exportar el CSR', 'error');
            return redirect()->back();
        }
    }
    
    //Funcion que actualiza el certificado de AFIP que sube el usuario como archivo 
    public function newCert(Request $request){
        
        $request->validate([
            'cert' => 'required',
            'vencimiento' => 'required|date|after:today'
        ]);
        
        if($request->hasFile('cert')){
            
            // Obtener el archivo cargado
            $certFile = $request->file('cert');
            
            // Leer el contenido del archivo como texto
            $certContent = file_get_contents($certFile->getRealPath());
            
            if (!$this->isValidCertificate($certContent)) {
                toast('El certificado otorgado no es válido.', 'error');
                return redirect()->back();
            }

            $keyPath = afipDir() . 'key';
            $privateKey = is_file($keyPath) && is_readable($keyPath)
                ? @openssl_pkey_get_private(file_get_contents($keyPath))
                : false;
            if ($privateKey === false || !openssl_x509_check_private_key($certContent, $privateKey)) {
                toast('El certificado no corresponde a la clave privada de afip_resources/key. Cargue el certificado emitido para esa clave.', 'error');
                return redirect()->back();
            }
            
            if ($certFile->move(afipDir(), 'cert')) {
                toast('Certificado cargado con éxito', 'success');
            } else {
                toast('Hubo un error al cargar el certificado', 'error');
                return redirect()->back();
            }

            VariableGlobal::updateOrCreate([
                'clave' => 'VENCIMIENTO_CERTIFICADO',
            ], [
                'valor' => $request->vencimiento,
            ]);
        }else{
            toast('No se encontró el certificado', 'error');
            return redirect()->back();
        }
        return redirect()->back()->withInput();
        
    }
    
    /**
    * Verificar si el contenido del archivo es un certificado válido.
    *
    * @param string $certContent
    * @return bool
    */
    private function isValidCertificate($certContent)
    {
        // Intentamos leer el certificado
        $cert = openssl_x509_read($certContent);
        
        // Si no se pudo leer el certificado, es inválido
        if (!$cert) {
            return false;
        }
        
        // Puedes añadir más validaciones si lo necesitas. Por ejemplo, comprobar si el certificado está caducado:
            $certData = openssl_x509_parse($cert);
            
            // Comprobar si la fecha de expiración del certificado es posterior a la fecha actual
            $currentDate = time(); // Hora actual en formato timestamp
            $expirationDate = $certData['validTo_time_t']; // Fecha de expiración del certificado en timestamp
            
            if ($expirationDate < $currentDate) {
                return false; // El certificado ha expirado
            }
            
            return true; // El certificado es válido
        }
        
    }
    