<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\VariableGlobal;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $variables = [
            [
                'clave' => 'AVATAR',
                'descripcion' => 'Nombre del archivo para la imagen de los membretes',
                'valor' => '',
            ],
            [
                'clave' => 'CUIT_EMPRESA',
                'descripcion' => 'Cuit de la empresa',
                'valor' => '',
            ],
            [
                'clave' => 'AFIP_KEY',
                'descripcion' => 'Clave privada para AFIP',
                'valor' => '',
            ],
            [
                'clave' => 'RAZON_SOCIAL',
                'descripcion' => 'Razón social de la empresa',
                'valor' => '',
            ],
            [
                'clave' => 'AFIP_CERTIFICADO',
                'descripcion' => 'Certificado emitido por AFIP para facturación electrónica',
                'valor' => '',
            ],
            [
                'clave' => 'VENCIMIENTO_CERTIFICADO',
                'descripcion' => 'Fecha de vencimiento del certificado emitido por AFIP',
                'valor' => '',
            ],
            [
                'clave' => 'PUNTO_VENTA',
                'descripcion' => 'Punto de venta para facturación electrónica',
                'valor' => '',
            ],
            [
                'clave' => 'CONDICION_IVA',
                'descripcion' => 'Condiciones de IVA para facturación electrónica',
                'valor' => '',
            ],
            [
                'clave' => 'DOMICILIO_FISCAL',
                'descripcion' => 'Domicilio fiscal de la empresa',
                'valor' => '',
            ],
            [
                'clave' => 'FECHA_INICIO_ACTIVIDADES',
                'descripcion' => 'Fecha de inicio de actividades de la empresa',
                'valor' => '',
            ],
            [
                'clave' => 'TOPE_FACTURACION_CONSUMIDOR_FINAL',
                'descripcion' => 'Tope de facturación para responsable inscripto',
                'valor' => '417000',
            ],
            [
                'clave' => 'TIPO_IMPRESORA',
                'descripcion' => 'Estilo de impresora para la factura',
                'valor' => 'AMBAS',
            ],
        ];

        foreach ($variables as $variable) {
            VariableGlobal::firstOrCreate(
                ['clave' => $variable['clave']],
                [
                    'descripcion' => $variable['descripcion'],
                    'valor' => $variable['valor'],
                ]
            );
        }

        $this->call(AfipSeeder::class);
    }
}
