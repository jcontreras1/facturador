<?php

namespace Database\Seeders;

use App\Models\VariableGlobal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChotoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        VariableGlobal::firstOrCreate([
            'clave' => 'TIPO_IMPRESORA',
        ], [
            'descripcion' => 'Estilo de impresora para la factura',
            'valor' => 'CHOTO2',
        ]);
    }
}
