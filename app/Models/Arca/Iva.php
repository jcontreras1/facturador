<?php

namespace App\Models\Arca;

use Illuminate\Database\Eloquent\Model;

class Iva extends Model
{
    protected $table = 'iva';

    protected $fillable = [
        'iva',
        'codigo_afip',
        'descripcion',
    ];
}
