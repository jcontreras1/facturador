<?php

namespace App\Models\Arca;

use Illuminate\Database\Eloquent\Model;

class IvaReceptor extends Model
{
    public const CODIGOS_FACTURA_A = [1, 6, 13, 16];
    public const CODIGOS_FACTURA_B = [4, 5, 7, 8, 9, 10, 15];

    protected $table = 'condicion_iva_receptor';
    protected $fillable = ['descripcion', 'codigo_afip', 'cmp_clase'];

}
