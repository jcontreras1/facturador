<?php

namespace App\Models\Arca;

use Illuminate\Database\Eloquent\Model;

class IvaReceptor extends Model
{
    protected $table = 'condicion_iva_receptor';
    protected $fillable = ['descripcion', 'codigo_afip', 'cmp_clase'];

}
