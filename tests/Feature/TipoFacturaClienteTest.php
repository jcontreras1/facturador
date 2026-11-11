<?php

use App\Http\Controllers\AfipWS\Afip;
use App\Http\Controllers\Negocio\Comprobante\Factura\CController;
use App\Models\Arca\Iva;
use App\Models\Arca\IvaReceptor;
use App\Models\Arca\TipoComprobante;
use App\Models\Cliente;
use App\Models\User;
use App\Models\VariableGlobal;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->withoutVite();
    $this->actingAs(User::factory()->create());
    VariableGlobal::where('clave', 'CONDICION_IVA')->update(['valor' => 'IVA Responsable Inscripto']);

    $this->mock(Afip::class, function ($mock) {
        $mock->shouldNotReceive('GetServiceTA');
    });
});

test('client screens and routes only allow the corresponding invoice', function (string $emisor, int $codigo, string $tipo) {
    VariableGlobal::where('clave', 'CONDICION_IVA')->update(['valor' => $emisor]);
    $cliente = Cliente::create([
        'nombre' => 'Cliente de prueba',
        'cuit' => '20123456789',
        'tipo_documento_afip' => 80,
        'condicion_iva_receptor_id' => IvaReceptor::where('codigo_afip', $codigo)->firstOrFail()->id,
    ]);

    foreach (['clientes.index', 'clientes.dashboard'] as $pantalla) {
        $response = $this->get(route($pantalla, $pantalla === 'clientes.index' ? [] : $cliente))->assertOk();
        foreach (['A', 'B', 'C'] as $letra) {
            $url = route('cliente.comprobante.create.'.strtolower($letra), $cliente);
            if ($letra === $tipo) {
                $response->assertSee($url, false);
            } else {
                $response->assertDontSee($url, false);
            }
        }
    }

    foreach (['A', 'B', 'C'] as $letra) {
        $response = $this->get(route('cliente.comprobante.create.'.strtolower($letra), $cliente));
        if ($letra === $tipo) {
            $response->assertOk()->assertViewHas('cliente', fn ($value) => $value->is($cliente));
        } else {
            $response->assertForbidden();
        }
    }
})->with([
    ['IVA Responsable Inscripto', 1, 'A'],
    ['IVA Responsable Inscripto', 6, 'A'],
    ['IVA Responsable Inscripto', 13, 'A'],
    ['IVA Responsable Inscripto', 16, 'A'],
    ['IVA Responsable Inscripto', 4, 'B'],
    ['IVA Responsable Inscripto', 5, 'B'],
    ['Responsable Monotributo', 1, 'C'],
    ['Responsable Monotributo', 6, 'C'],
    ['Responsable Monotributo', 4, 'C'],
    ['Responsable Monotributo', 5, 'C'],
]);

test('generic forms filter receiver conditions by invoice type', function (string $tipo, array $codigos) {
    $this->get(route('comprobante.create.'.strtolower($tipo)))
        ->assertOk()
        ->assertViewHas('cliente', null)
        ->assertViewHas('condicionesIva', fn ($condiciones) => $condiciones->pluck('codigo_afip')
            ->map(fn ($codigo) => (int) $codigo)->sort()->values()->all() === $codigos)
        ->assertSee(':cliente="null"', false);
})->with([
    ['A', [1, 6, 13, 16]],
    ['B', [4, 5, 7, 8, 9, 10, 15]],
]);

test('API rejects incompatible receiver conditions before contacting ARCA', function (string $tipo, int $codigo) {
    $receptor = IvaReceptor::where('codigo_afip', $codigo)->firstOrFail();

    $this->postJson(route('comprobante.'.strtolower($tipo).'.store'), ['condicionIva' => $receptor->id])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('condicionIva');

    $this->assertDatabaseCount('comprobante', 0);
})->with([
    ['A', 4], ['A', 5], ['B', 1], ['B', 6], ['B', 13], ['B', 16],
]);

test('API rejects invoice types not allowed for the issuer', function (string $emisor, string $tipo) {
    VariableGlobal::where('clave', 'CONDICION_IVA')->update(['valor' => $emisor]);

    $this->postJson(route('comprobante.'.strtolower($tipo).'.store'), [
        'condicionIva' => IvaReceptor::where('codigo_afip', 5)->firstOrFail()->id,
    ])->assertForbidden();

    $this->get(route('comprobante.create.'.strtolower($tipo)))->assertForbidden();
    $this->assertDatabaseCount('comprobante', 0);
})->with([
    ['Responsable Monotributo', 'A'],
    ['Responsable Monotributo', 'B'],
    ['IVA Responsable Inscripto', 'C'],
    ['', 'A'],
    ['', 'B'],
    ['', 'C'],
]);

test('API cannot change a selected clients receiver condition', function () {
    $cliente = Cliente::create([
        'nombre' => 'Cliente monotributista',
        'cuit' => '20123456789',
        'condicion_iva_receptor_id' => IvaReceptor::where('codigo_afip', 6)->firstOrFail()->id,
    ]);

    $this->postJson(route('comprobante.b.store', ['cliente' => $cliente->id]), [
        'condicionIva' => IvaReceptor::where('codigo_afip', 5)->firstOrFail()->id,
    ])->assertUnprocessable()->assertJsonValidationErrors('condicionIva');
    $this->assertDatabaseCount('comprobante', 0);
});

test('missing or invalid receiver conditions are rejected', function (?int $id) {
    $this->postJson(route('comprobante.a.store'), ['condicionIva' => $id])
        ->assertUnprocessable()->assertJsonValidationErrors('condicionIva');
})->with([null, 99999]);

test('registered issuers cannot invoice a client without a receiver condition', function () {
    $cliente = Cliente::create(['nombre' => 'Sin condicion de IVA', 'cuit' => '20123456789']);

    $this->get(route('clientes.dashboard', $cliente))->assertOk()
        ->assertDontSee('Nueva Factura');
    $this->get(route('cliente.comprobante.create.a', $cliente))->assertForbidden();
    $this->get(route('cliente.comprobante.create.b', $cliente))->assertForbidden();
});

test('registered issuers cannot issue C invoices through monthly or batch billing', function () {
    $cliente = Cliente::create(['nombre' => 'Cliente de prueba', 'cuit' => '20123456789']);

    $this->get(route('lote.create.c'))->assertForbidden();
    $this->post(route('lote.store.c'))->assertForbidden();
    expect(fn () => CController::facturacionMensual($cliente))
        ->toThrow(Exception::class, 'La facturacion mensual C solo esta disponible para emisores monotributistas.');
    $this->assertDatabaseCount('comprobante', 0);
});

test('valid invoices persist the client and send the AFIP receiver code rather than the database id', function (string $emisor, int $codigo, string $tipo) {
    VariableGlobal::where('clave', 'CONDICION_IVA')->update(['valor' => $emisor]);
    VariableGlobal::where('clave', 'PUNTO_VENTA')->update(['valor' => '1']);
    $receptor = IvaReceptor::create([
        'descripcion' => 'Receptor con ID distinto al codigo AFIP',
        'codigo_afip' => $codigo,
    ]);
    $cliente = Cliente::create([
        'nombre' => 'Cliente de prueba',
        'cuit' => '20123456789',
        'tipo_documento_afip' => 80,
        'condicion_iva_receptor_id' => $receptor->id,
    ]);
    $tipoComprobante = TipoComprobante::where('codigo', $tipo)->firstOrFail();
    $servicio = Mockery::mock();
    $servicio->shouldReceive('GetLastVoucher')->once()->with('1', $tipoComprobante->codigo_afip)->andReturn(10);
    $servicio->shouldReceive('CreateVoucher')->once()->withArgs(function (array $data) use ($codigo, $tipoComprobante) {
        return $data['CondicionIVAReceptorId'] === $codigo
            && $data['CbteTipo'] === (int) $tipoComprobante->codigo_afip;
    })->andReturn(['CAE' => '12345678901234', 'CAEFchVto' => '2026-11-21']);
    $afip = new class($servicio) extends Afip
    {
        public $FacturaElectronica;

        public function __construct($servicio)
        {
            $this->FacturaElectronica = $servicio;
        }
    };
    $this->app->instance(Afip::class, $afip);

    $this->postJson(route('comprobante.'.strtolower($tipo).'.store', ['cliente' => $cliente->id]), [
        'tipoDocumentoId' => 80,
        'documento' => $cliente->cuit,
        'razonSocial' => $cliente->nombre,
        'domicilio' => 'Domicilio de prueba',
        'condicionIva' => $receptor->id,
        'concepto' => 1,
        'fecha' => '2026-11-11',
        'importeNeto' => 100,
        'importeTotal' => $tipo === 'C' ? 100 : 121,
        'lineas' => [[
            'descripcion' => 'Servicio',
            'cantidad' => 1,
            'unidadDeMedida' => 'unidad',
            'precioUnitario' => 100,
            'bonificacion' => 0,
            'importeBonificado' => 0,
            'subtotal' => 100,
            'subtotalConIva' => 121,
            'iva' => Iva::where('codigo_afip', '5')->firstOrFail()->id,
        ]],
    ])->assertCreated();

    $this->assertDatabaseHas('comprobante', [
        'cliente_id' => $cliente->id,
        'condicion_iva_receptor_id' => $receptor->id,
        'tipo_comprobante_id' => $tipoComprobante->id,
        'nro_comprobante' => 11,
        'cae' => '12345678901234',
    ]);
})->with([
    ['IVA Responsable Inscripto', 1, 'A'],
    ['IVA Responsable Inscripto', 6, 'A'],
    ['IVA Responsable Inscripto', 4, 'B'],
    ['IVA Responsable Inscripto', 5, 'B'],
    ['Responsable Monotributo', 1, 'C'],
    ['Responsable Monotributo', 6, 'C'],
    ['Responsable Monotributo', 4, 'C'],
    ['Responsable Monotributo', 5, 'C'],
]);
