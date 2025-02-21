<?php

namespace App\Models;

use App\Models\Arca\Comprobante;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $fillable = [
        'nombre',
        'cuit',
        'email',
        'telefono',
        'direccion',
    ];

    public function comprobantes()
    {
        return $this->hasMany(Comprobante::class, 'cliente_id');
    }

    public function servicios(){
        return $this->hasMany(ServicioCliente::class, 'cliente_id');
    }
}
