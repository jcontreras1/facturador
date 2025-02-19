<?php

namespace App\Models\Arca;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    protected $table = 'comprobante';

    protected $fillable = [
        'cuit_dni',
        'razon_social',
        'domicilio',
        'tipo_documento_id',
        'tipo_comprobante_id',
        'punto_venta',
        'nro_comprobante',
        'created_by',
        'anulacion_id',
        'importe_neto',
        'importe_gravado',
        'cliente_id',
        'importe_no_gravado',
        'importe_exento_iva',
        'importe_iva',
        'importe_total_tributos',
        'importe_total',
        'observaciones',
        'cae',
        'fecha_vencimiento_cae',
        'condicion_iva_receptor_id',
        'fecha_emision',
        'fecha_servicio_desde',
        'fecha_servicio_hasta',
        'fecha_vencimiento_pago',
        'concepto',
    ];

    public function tipoComprobante()
    {
        return $this->belongsTo(TipoComprobante::class, 'tipo_comprobante_id', 'id');
    }

    
    public function detalle(){
        return $this->hasMany(DetalleComprobante::class, 'comprobante_id');
    }
    
    public function comprobanteAnulado()
    {
        return $this->belongsTo(Comprobante::class, 'anulacion_id');
    }

    public function cliente(){
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
    
    public function condicionIvaReceptor()
    {
        return $this->belongsTo(IvaReceptor::class, 'condicion_iva_receptor_id');
    }
}
