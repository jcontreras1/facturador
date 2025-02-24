<?php

namespace App\Models\Arca;

use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    const CUIT = 80;
    const CUIL = 86;
    const DNI = 96;
    const CONSUMIDOR_FINAL = 99;

    public static function getAll()
    {
        return [
        self::CUIT => 'CUIT',
        self::CUIL => 'CUIL',
        self::DNI => 'DNI',
        self::CONSUMIDOR_FINAL => 'Consumidor Final'
    ];
    }

    public static function getOptions()
    {
        return [
            ['value' => self::CUIT, 'descripcion' => 'CUIT'],
            ['value' => self::CUIL, 'descripcion' => 'CUIL'],
            ['value' => self::DNI, 'descripcion' => 'DNI'],
            ['value' => self::CONSUMIDOR_FINAL, 'descripcion' => 'Consumidor Final']
        ];
    }
}
