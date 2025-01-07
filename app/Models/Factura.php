<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $table = 'factura';
    protected $fillable = [
        'codigo_afip',
        'nro_factura',
        'tipo_comprobante',
        'punto_venta',
        'cliente_id',
        'fecha_pago',
        'created_by',
        'pagada',
        'anulada',
        'total_neto',
        'total_iva',
        'total',
        'observaciones',
        'enviada_afip',
        'cae',
        'fecha_vencimiento_cae',
        'razon_social',
        'domicilio',
        'cuit',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function items()
    {
        return $this->hasMany(ItemFactura::class);
    }
}
