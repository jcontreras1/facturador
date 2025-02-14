<?php

namespace Database\Seeders;

use App\Models\Arca\Iva;
use App\Models\Arca\IvaReceptor;
use App\Models\Arca\TipoComprobante;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AfipSeeder extends Seeder
{
    /**
    * Run the database seeds.
    */
    public function run(): void
    {
        //IVAS
        
        Iva::firstOrCreate(['descripcion' => 'No gravado', 'codigo_afip' => 'N', 'iva' => 0]);
        Iva::firstOrCreate(['descripcion' => 'Exento',     'codigo_afip' => 'E', 'iva' => 0]);
        Iva::firstOrCreate(['descripcion' => '0%',         'codigo_afip' => '3', 'iva' => 0]);
        Iva::firstOrCreate(['descripcion' => '2.5%',       'codigo_afip' => '9', 'iva' => 2.5]);
        Iva::firstOrCreate(['descripcion' => '5%',         'codigo_afip' => '8', 'iva' => 5]);
        Iva::firstOrCreate(['descripcion' => '10.5%',      'codigo_afip' => '4', 'iva' => 10.5]);
        Iva::firstOrCreate(['descripcion' => '21%',        'codigo_afip' => '5', 'iva' => 21]);
        Iva::firstOrCreate(['descripcion' => '27%',        'codigo_afip' => '6', 'iva' => 27]);
        
        //Condicion Iva Receptor
        
        $condiciones = [
            ["codigo_afip" => 1, "descripcion" => "IVA Responsable Inscripto", "Cmp_Clase" => "A/M/C"],
            ["codigo_afip" => 6, "descripcion" => "Responsable Monotributo", "Cmp_Clase" => "A/M/C"],
            ["codigo_afip" => 13, "descripcion" => "Monotributista Social", "Cmp_Clase" => "A/M/C"],
            ["codigo_afip" => 16, "descripcion" => "Monotributo Trabajador Independiente Promovido", "Cmp_Clase" => "A/M/C"],
            ["codigo_afip" => 4, "descripcion" => "IVA Sujeto Exento", "Cmp_Clase" => "B/C"],
            ["codigo_afip" => 5, "descripcion" => "Consumidor Final", "Cmp_Clase" => "B/C"],
            ["codigo_afip" => 7, "descripcion" => "Sujeto No Categorizado", "Cmp_Clase" => "B/C"],
            ["codigo_afip" => 8, "descripcion" => "Proveedor del Exterior", "Cmp_Clase" => "B/C"],
            ["codigo_afip" => 9, "descripcion" => "Cliente del Exterior", "Cmp_Clase" => "B/C"],
            ["codigo_afip" => 10, "descripcion" => "IVA Liberado – Ley N° 19.640", "Cmp_Clase" => "B/C"],
            ["codigo_afip" => 15, "descripcion" => "IVA No Alcanzado", "Cmp_Clase" => "B/C"],
        ];
        
        foreach ($condiciones as $condicion) {
            IvaReceptor::firstOrCreate([
                'codigo_afip' => $condicion['codigo_afip'],
                'descripcion' => $condicion['descripcion'],
                'cmp_clase' => $condicion['Cmp_Clase'],
            ]);
        }

        //Tipos de Comprobantes

        TipoComprobante::firstOrCreate(['codigo_afip' => '1', 'codigo' => 'A','descripcion' => 'Factura A']);
        TipoComprobante::firstOrCreate(['codigo_afip' => '3', 'codigo' => 'CA','descripcion' => 'Nota de Crédito A']);
        TipoComprobante::firstOrCreate(['codigo_afip' => '6', 'codigo' => 'B','descripcion' => 'Factura B']);
        TipoComprobante::firstOrCreate(['codigo_afip' => '8', 'codigo' => 'CB','descripcion' => 'Nota de Crédito B']);
        TipoComprobante::firstOrCreate(['codigo_afip' => '11', 'codigo' => 'C','descripcion' => 'Factura C']);
        TipoComprobante::firstOrCreate(['codigo_afip' => '13', 'codigo' => 'CC','descripcion' => 'Nota de Crédito C']);
        
    }
    
}
