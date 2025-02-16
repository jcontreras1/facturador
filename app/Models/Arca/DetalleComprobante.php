<?php

namespace App\Models\Arca;

use Illuminate\Database\Eloquent\Model;

class DetalleComprobante extends Model
{
    protected $table = 'detalle_comprobante';

    protected $fillable = [
        'comprobante_id',
        'codigo',
        'descripcion',
        'cantidad',
        'unidad_medida',
        'importe_unitario',
        'porcentaje_descuento',
        'importe_descuento',
        'iva_id',
        'importe_iva',
        'importe_subtotal',
    ];

    public function comprobante()
    {
        return $this->belongsTo(Comprobante::class, 'comprobante_id');
    }

    public function iva()
    {
        return $this->belongsTo(Iva::class, 'iva_id');
    }   
}
