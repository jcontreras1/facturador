<?php

use App\Http\Controllers\AfipWS\Afip;
use App\Models\Cliente;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('existing CUIT CUIL and DNI receivers are returned without constructing an ARCA service', function (string $documento, ?int $tipoGuardado, string $tipoBusqueda) {
    $cliente = Cliente::create([
        'nombre' => 'Cliente local',
        'cuit' => $documento,
        'direccion' => 'Domicilio local',
        'tipo_documento_afip' => $tipoGuardado,
    ]);
    $this->app->bind(Afip::class, function () {
        throw new RuntimeException('No debe conectarse a ARCA');
    });

    $this->getJson('/api/contribuyente/'.preg_replace('/\D/', '', $documento).'?tipo='.$tipoBusqueda.'&buscarCliente=1')
        ->assertOk()->assertJson([
            'razonSocial' => 'Cliente local',
            'domicilio' => 'Domicilio local',
            'cliente' => ['id' => $cliente->id],
        ]);
})->with([
    ['20-12345678-9', 80, 'cuit'],
    ['20123456789', 86, 'cuit'],
    ['12.345.678', 96, 'dni'],
    ['20123456789', null, 'cuit'],
    ['12345678', null, 'dni'],
]);

test('ARCA is consulted for missing clients and explicit registry refreshes', function (string $documento, string $tipo, bool $buscarCliente = true) {
    if (!$buscarCliente) {
        Cliente::create(['nombre' => 'Datos anteriores', 'cuit' => $documento, 'tipo_documento_afip' => 80]);
    }
    $padron = Mockery::mock();
    if ($tipo === 'dni') {
        $padron->shouldReceive('DniACuit')->once()->with($documento)->andReturn('20123456789');
    } else {
        $padron->shouldNotReceive('DniACuit');
    }
    $padron->shouldReceive('GetTaxpayerDetails')->once()->with('20123456789')->andReturn((object) [
        'tipoPersona' => 'FISICA',
        'apellido' => 'Apellido',
        'nombre' => 'Nombre',
        'domicilio' => [(object) [
            'direccion' => 'Calle 123',
            'localidad' => 'Localidad',
            'descripcionProvincia' => 'Provincia',
        ]],
    ]);
    $afip = new class($padron) extends Afip
    {
        public $PadronAlcance13;

        public function __construct($padron)
        {
            $this->PadronAlcance13 = $padron;
        }
    };
    $this->app->instance(Afip::class, $afip);

    $this->getJson('/api/contribuyente/'.$documento.'?tipo='.$tipo.'&buscarCliente='.(int) $buscarCliente)->assertOk()
        ->assertJson(['razonSocial' => 'Apellido, Nombre', 'cliente' => null]);
    $this->assertDatabaseCount('clientes', $buscarCliente ? 0 : 1);
})->with([['20123456789', 'cuit'], ['12345678', 'dni'], ['20123456789', 'cuit', false]]);
