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
            'clave' => 'PAIS_POR_DEFECTO',
        ],[
            'valor' => '11',
            'descripcion' => 'País por defecto en los pasajeros',
        ]);
        VariableGlobal::updateOrCreate([
            'clave' => 'EMBARCACION_POR_DEFECTO',
        ],[
            'valor' => '1',
            'descripcion' => 'Embarcación por defecto en los pasajeros',
        ]);
        VariableGlobal::updateOrCreate([
            'clave' => 'USAR_COLORES_SALIDA',
        ],[
            'valor' => '1',
            'descripcion' => 'Define el uso (o no) de colores para indentificar salidas',
        ]);        
        VariableGlobal::updateOrCreate([
            'clave' => 'AVATAR',
        ],[
            'valor' => '',
            'descripcion' => 'Nombre del archivo para la imagen de los membretes',
        ]);
        
        VariableGlobal::updateOrCreate([
            'clave' => 'CUIT_EMPRESA',
        ],[
            'valor' => '',
            'descripcion' => 'Cuit de la empresa',
        ]);
        
        VariableGlobal::updateOrCreate([
            'clave' => 'AFIP_KEY',
            'descripcion' => 'Clave privada para AFIP'
        ], [
            'valor' => ''
        ]);
        VariableGlobal::updateOrCreate([
            'clave' => 'RAZON_SOCIAL',
            'descripcion' => 'Razón social de la empresa'
        ], [
            'valor' => ''
        ]);
        
        VariableGlobal::updateOrCreate([
            'clave' => 'AFIP_CERTIFICADO',
            'descripcion' => 'Certificado emitido por AFIP para facturación electrónica'
        ], [
            'valor' => ''
        ]);
        
        VariableGlobal::updateOrCreate([
            'clave' => 'VENCIMIENTO_CERTIFICADO',
            'descripcion' => 'Fecha de vencimiento del certificado emitido por AFIP'
        ], [
            'valor' => ''
        ]);

    }
}
