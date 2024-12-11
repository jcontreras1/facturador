<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemFactura extends Model
{
    protected $table = 'detalle_factura';
    protected $fillable = [
        'codigo',
        'descripcion',
        'cantidad',
        'unidad',
        'precio_unitario',
        'alicuota_iva',
        'total',
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }
}
