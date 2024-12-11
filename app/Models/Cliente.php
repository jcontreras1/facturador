<?php

namespace App\Models;

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

    public function facturas()
    {
        return $this->hasMany(Factura::class);
    }
}
