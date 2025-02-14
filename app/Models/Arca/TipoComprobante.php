<?php

namespace App\Models\Arca;

use Illuminate\Database\Eloquent\Model;

class TipoComprobante extends Model
{
    protected $table = 'tipo_comprobante';

    protected $fillable = ['codigo_afip', 'codigo', 'descripcion'];
}
