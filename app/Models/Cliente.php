<?php

namespace App\Models;

use App\Models\Arca\Comprobante;
use App\Models\Arca\IvaReceptor;
use App\Models\Arca\TipoDocumento;
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
        'condicion_iva_receptor_id',
        'tipo_documento_afip',
    ];

    protected $appends = ['tipo_documento'];

    public function getTipoDocumentoAttribute(){
        $tipoDocumentos = TipoDocumento::getAll();
        return isset($tipoDocumentos[$this->tipo_documento_afip]) ? $tipoDocumentos[$this->tipo_documento_afip] : null;
        // return TipoDocumento::getAll();
    }
    public function comprobantes()
    {
        return $this->hasMany(Comprobante::class, 'cliente_id');
    }

    public function servicios(){
        return $this->hasMany(ServicioCliente::class, 'cliente_id');
    }

    public function condicionIva(){
        return $this->belongsTo(IvaReceptor::class, 'condicion_iva_receptor_id');
    }
}
