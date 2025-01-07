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
        VariableGlobal::updateOrCreate([
            'clave' => 'AVATAR',
            'descripcion' => 'Nombre del archivo para la imagen de los membretes',
        ],[
            // 'valor' => '',
        ]);
        
        VariableGlobal::updateOrCreate([
            'clave' => 'CUIT_EMPRESA',
            'descripcion' => 'Cuit de la empresa',
        ],[
            // 'valor' => '',
        ]);
        
        VariableGlobal::updateOrCreate([
            'clave' => 'AFIP_KEY',
            'descripcion' => 'Clave privada para AFIP',
        ], [
            // 'valor' => '',
        ]);
        VariableGlobal::updateOrCreate([
            'clave' => 'RAZON_SOCIAL',
            'descripcion' => 'Razón social de la empresa',
        ], [
            // 'valor' => '',
        ]);
        
        VariableGlobal::updateOrCreate([
            'clave' => 'AFIP_CERTIFICADO',
            'descripcion' => 'Certificado emitido por AFIP para facturación electrónica',
        ], [
            // 'valor' => ''
        ]);
        
        VariableGlobal::updateOrCreate([
            'clave' => 'VENCIMIENTO_CERTIFICADO',
            'descripcion' => 'Fecha de vencimiento del certificado emitido por AFIP',
        ], [
            // 'valor' => ''
        ]);
        
        VariableGlobal::updateOrCreate([
            'clave' => 'PUNTO_VENTA',
            'descripcion' => 'Punto de venta para facturación electrónica',
        ], [
            // 'valor' => ''
        ]);

        VariableGlobal::updateOrCreate([
            'clave' => 'CONDICION_IVA',
            'descripcion' => 'Condiciones de IVA para facturación electrónica',
        ], [
            // 'valor' => ''
        ]);
        
        VariableGlobal::updateOrCreate([
            'clave' => 'DOMICILIO_FISCAL',
            'descripcion' => 'Domicilio fiscal de la empresa',
        ], [
            // 'valor' => ''
        ]);

    }
}
