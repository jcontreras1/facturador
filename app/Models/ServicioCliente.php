<?php

namespace App\Models;

use App\Models\Arca\Iva;
use Illuminate\Database\Eloquent\Model;

class ServicioCliente extends Model
{
    protected $table = 'servicio_mensual_cliente';

    protected $fillable = [
        'cliente_id',
        'item_id',
        'cantidad',
        'descripcion',
        'iva_id',
        'importe_neto',
        'importe_total',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function servicio()
    {
        return $this->belongsTo(Item::class);
    }

    public function iva()
    {
        return $this->belongsTo(Iva::class);
    }
}
