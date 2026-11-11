<?php

use App\Http\Controllers\AfipWS\Afip;
use App\Models\Arca\Comprobante;
use App\Models\Arca\Iva;
use App\Models\Arca\IvaReceptor;
use App\Models\Cliente;
use App\Models\User;
use App\Models\VariableGlobal;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->actingAs(User::factory()->create());
    VariableGlobal::where('clave', 'PUNTO_VENTA')->update(['valor' => '1']);

    $this->servicioArca = Mockery::mock();
    $this->servicioArca->shouldReceive('GetLastVoucher')->andReturn(10)->byDefault();
    $this->servicioArca->shouldReceive('CreateVoucher')
        ->andReturn(['CAE' => '12345678901234', 'CAEFchVto' => '2026-11-21'])->byDefault();
    $afip = new class($this->servicioArca) extends Afip
    {
        public $FacturaElectronica;

        public function __construct($servicio)
        {
            $this->FacturaElectronica = $servicio;
        }
    };
    $this->app->instance(Afip::class, $afip);

    $this->datosFactura = function (string $tipo, int $documento = 80): array {
        VariableGlobal::where('clave', 'CONDICION_IVA')->update([
            'valor' => $tipo === 'C' ? 'Responsable Monotributo' : 'IVA Responsable Inscripto',
        ]);

        return [
            'tipoDocumentoId' => $documento,
            'documento' => $documento === 96 ? '12345678' : '20123456789',
            'razonSocial' => 'Nuevo receptor',
            'domicilio' => 'Domicilio de prueba',
            'condicionIva' => IvaReceptor::where('codigo_afip', $tipo === 'A' ? 1 : 5)->firstOrFail()->id,
            'concepto' => 1,
            'fecha' => '2026-11-11',
            'importeNeto' => 100,
            'importeTotal' => $tipo === 'C' ? 100 : 121,
            'agregarComoCliente' => true,
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
        ];
    };
});

test('opting in creates the receiver and links the issued invoice', function (string $tipo, int $tipoDocumento) {
    $datos = ($this->datosFactura)($tipo, $tipoDocumento);
    $this->postJson(route('comprobante.'.strtolower($tipo).'.store'), $datos)->assertCreated();

    $this->assertDatabaseCount('clientes', 1);
    $cliente = Cliente::firstOrFail();
    expect($cliente->nombre)->toBe($datos['razonSocial'])
        ->and($cliente->cuit)->toBe($datos['documento'])
        ->and($cliente->direccion)->toBe($datos['domicilio'])
        ->and((int) $cliente->tipo_documento_afip)->toBe($tipoDocumento)
        ->and((int) $cliente->condicion_iva_receptor_id)->toBe($datos['condicionIva'])
        ->and(Comprobante::firstOrFail()->cliente_id)->toBe($cliente->id);
})->with([['A', 80], ['A', 86], ['B', 80], ['B', 86], ['B', 96], ['C', 80], ['C', 86], ['C', 96]]);

test('a new receiver is not saved without opting in', function (string $tipo) {
    $datos = ($this->datosFactura)($tipo);
    $datos['agregarComoCliente'] = false;
    $this->postJson(route('comprobante.'.strtolower($tipo).'.store'), $datos)->assertCreated();

    $this->assertDatabaseCount('clientes', 0);
    expect(Comprobante::firstOrFail()->cliente_id)->toBeNull();
})->with(['A', 'B', 'C']);

test('submission rechecks existing documents without duplicating or overwriting the client', function (int $tipoDocumento, bool $agregar) {
    $datos = ($this->datosFactura)('C', $tipoDocumento);
    $datos['agregarComoCliente'] = $agregar;
    $cliente = Cliente::create([
        'nombre' => 'Nombre guardado',
        'cuit' => $tipoDocumento === 96 ? '12.345.678' : '20-12345678-9',
        'tipo_documento_afip' => $tipoDocumento === 86 ? 80 : $tipoDocumento,
        'condicion_iva_receptor_id' => $datos['condicionIva'],
    ]);

    $this->postJson(route('comprobante.c.store'), $datos)->assertCreated();

    $this->assertDatabaseCount('clientes', 1);
    expect($cliente->fresh()->nombre)->toBe('Nombre guardado')
        ->and(Comprobante::firstOrFail()->cliente_id)->toBe($cliente->id);
})->with([80, 86, 96])->with([true, false]);

test('an ARCA rejection rolls back the new client and invoice together', function (string $tipo) {
    $datos = ($this->datosFactura)($tipo);
    $this->servicioArca->shouldReceive('CreateVoucher')->once()->andThrow(new Exception('Rechazo de ARCA'));

    $this->postJson(route('comprobante.'.strtolower($tipo).'.store'), $datos)
        ->assertStatus(500)->assertJsonFragment(['message' => 'Error al guardar el comprobante: Rechazo de ARCA']);

    $this->assertDatabaseCount('clientes', 0);
    $this->assertDatabaseCount('comprobante', 0);
    $this->assertDatabaseCount('detalle_comprobante', 0);
})->with(['A', 'B', 'C']);

test('saving a client requires a valid identified receiver', function (array $cambios, string $campo) {
    $datos = array_replace(($this->datosFactura)('C'), $cambios);
    $this->servicioArca->shouldNotReceive('CreateVoucher');

    $this->postJson(route('comprobante.c.store'), $datos)->assertUnprocessable()->assertJsonValidationErrors($campo);
    $this->assertDatabaseCount('clientes', 0);
})->with([
    [['tipoDocumentoId' => 99, 'documento' => ''], 'tipoDocumentoId'],
    [['documento' => '123'], 'documento'],
    [['razonSocial' => ''], 'razonSocial'],
    [['agregarComoCliente' => 'incorrecto'], 'agregarComoCliente'],
]);
