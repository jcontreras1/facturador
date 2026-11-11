<?php

use App\Http\Controllers\AfipWS\Afip;
use App\Models\Arca\Comprobante;
use App\Models\Arca\Iva;
use App\Models\Arca\IvaReceptor;
use App\Models\Arca\TipoComprobante;
use App\Models\User;
use App\Models\VariableGlobal;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->actingAs(User::factory()->create());
    $this->travelTo(now()->setDate(2026, 11, 11)->startOfDay());
    VariableGlobal::where('clave', 'PUNTO_VENTA')->update(['valor' => '1']);

    $this->servicioArca = Mockery::mock();
    $afip = new class($this->servicioArca) extends Afip
    {
        public $FacturaElectronica;

        public function __construct($servicio)
        {
            $this->FacturaElectronica = $servicio;
        }
    };
    $this->app->instance(Afip::class, $afip);

    $this->crearFactura = function (string $letra, int $concepto = 1) {
        $receptor = IvaReceptor::create([
            'descripcion' => 'Receptor con ID distinto al codigo ARCA',
            'codigo_afip' => $letra === 'A' ? 1 : 5,
        ]);
        $factura = Comprobante::create([
            'tipo_comprobante_id' => TipoComprobante::where('codigo', $letra)->firstOrFail()->id,
            'tipo_documento_id' => 80,
            'cuit_dni' => '20123456789',
            'razon_social' => 'Cliente de prueba',
            'punto_venta' => 4,
            'nro_comprobante' => 123,
            'created_by' => auth()->id(),
            'condicion_iva_receptor_id' => $receptor->id,
            'concepto' => $concepto,
            'fecha_emision' => '2026-10-10',
            'fecha_servicio_desde' => $concepto === 1 ? null : '2026-10-01',
            'fecha_servicio_hasta' => $concepto === 1 ? null : '2026-10-10',
            'fecha_vencimiento_pago' => $concepto === 1 ? null : '2026-10-20',
            'importe_neto' => 350,
            'importe_total' => $letra === 'C' ? 350 : 402.5,
            'cae' => '12345678901234',
        ]);

        foreach ([[100, 21, '5'], [100, 10.5, '4'], [50, 0, '3'], [100, 21, '5']] as [$base, $iva, $codigo]) {
            $factura->detalle()->create([
                'descripcion' => 'Producto de prueba',
                'cantidad' => 1,
                'unidad_medida' => 'UN',
                'importe_unitario' => $base,
                'porcentaje_descuento' => 0,
                'importe_descuento' => 0,
                'importe_subtotal' => $base,
                'importe_subtotal_con_iva' => $letra === 'C' ? $base : $base + $iva,
                'importe_iva' => $letra === 'C' ? null : $iva,
                'iva_id' => $letra === 'C' ? null : Iva::where('codigo_afip', $codigo)->firstOrFail()->id,
            ]);
        }

        return $factura;
    };
});

test('cancellation issues the matching credit note and preserves the original VAT breakdown', function (
    string $letra, int $codigoFactura, int $codigoNota, int $concepto
) {
    $factura = ($this->crearFactura)($letra, $concepto);
    $this->servicioArca->shouldReceive('GetLastVoucher')->once()->with('1', (string) $codigoNota)->andReturn(10);
    $this->servicioArca->shouldReceive('CreateVoucher')->once()->withArgs(function (array $data) use ($factura, $letra, $codigoFactura, $codigoNota, $concepto) {
        expect($data['CbteTipo'])->toBe($codigoNota)
            ->and($data['CbteDesde'])->toBe(11)
            ->and($data['CbteHasta'])->toBe(11)
            ->and($data['ImpTotal'])->toEqual($factura->importe_total)
            ->and($data['ImpNeto'])->toEqual(350)
            ->and($data['ImpIVA'])->toEqual($letra === 'C' ? 0 : 52.5)
            ->and($data['CondicionIVAReceptorId'])->toBe($letra === 'A' ? 1 : 5)
            ->and($data['CbtesAsoc'])->toEqual([['Tipo' => $codigoFactura, 'PtoVta' => 4, 'Nro' => 123]])
            ->and($data['FchServDesde'])->toBe($concepto === 1 ? null : 20261001)
            ->and($data['FchServHasta'])->toBe($concepto === 1 ? null : 20261010)
            ->and($data['FchVtoPago'])->toBe($concepto === 1 ? null : 20261111);

        if ($letra === 'C') {
            expect($data)->not->toHaveKey('Iva');
        } else {
            expect($data['Iva'])->toEqual([
                ['Id' => 5, 'BaseImp' => 200, 'Importe' => 42],
                ['Id' => 4, 'BaseImp' => 100, 'Importe' => 10.5],
                ['Id' => 3, 'BaseImp' => 50, 'Importe' => 0],
            ]);
        }

        return true;
    })->andReturn(['CAE' => '98765432109876', 'CAEFchVto' => '2026-11-21']);

    $this->from('/comprobantes')->post(route('comprobante.anular', $factura))->assertRedirect('/comprobantes');

    $nota = $factura->fresh()->comprobanteAnulado;
    expect($nota)->not->toBeNull()
        ->and((int) $nota->tipoComprobante->codigo_afip)->toBe($codigoNota)
        ->and($nota->cae)->toBe('98765432109876')
        ->and($nota->importe_total)->toEqual($factura->importe_total)
        ->and($nota->importe_iva)->toEqual($letra === 'C' ? 0 : 52.5)
        ->and($nota->detalle)->toHaveCount($letra === 'C' ? 1 : 4);

    if ($letra !== 'C') {
        $campos = ['descripcion', 'cantidad', 'importe_subtotal', 'importe_subtotal_con_iva', 'importe_iva', 'iva_id'];
        expect($nota->detalle->map->only($campos)->all())->toEqual($factura->detalle->map->only($campos)->all());
    }

    foreach (['comprobantes.pdf', 'comprobantes.termica'] as $vista) {
        $html = view($vista, ['comprobante' => $nota])->render();
        if ($letra === 'C') {
            expect($html)->not->toContain('Importe Neto Gravado:');
        } else {
            expect($html)->toContain('Importe Neto Gravado:', 'IVA 21%', 'IVA 10.5%', 'IVA 0%');
        }
    }

    $this->assertDatabaseCount('comprobante', 2);
})->with([['A', 1, 3], ['B', 6, 8], ['C', 11, 13]])->with([1, 2, 3]);

test('ARCA rejection rolls back the credit note and leaves the invoice available for retry', function () {
    $factura = ($this->crearFactura)('A');
    $this->servicioArca->shouldReceive('GetLastVoucher')->once()->andReturn(10);
    $this->servicioArca->shouldReceive('CreateVoucher')->once()->andThrow(new Exception('Rechazo de ARCA'));

    $this->from('/comprobantes')->post(route('comprobante.anular', $factura))->assertRedirect('/comprobantes');

    expect($factura->fresh()->anulacion_id)->toBeNull();
    $this->assertDatabaseCount('comprobante', 1);
    $this->assertDatabaseCount('detalle_comprobante', 4);
    expect(session('alert.config'))->toContain('Rechazo de ARCA');
});

test('an already cancelled invoice does not contact ARCA or generate another credit note', function () {
    $factura = ($this->crearFactura)('C');
    $factura->update(['anulacion_id' => 999]);
    $this->servicioArca->shouldNotReceive('GetLastVoucher');
    $this->servicioArca->shouldNotReceive('CreateVoucher');

    $this->post(route('comprobante.anular', $factura))->assertRedirect();

    $this->assertDatabaseCount('comprobante', 1);
});

test('a credit note cannot itself be cancelled as an invoice', function () {
    $comprobante = ($this->crearFactura)('A');
    $comprobante->update(['tipo_comprobante_id' => TipoComprobante::where('codigo', 'CA')->firstOrFail()->id]);
    $this->servicioArca->shouldNotReceive('GetLastVoucher');
    $this->servicioArca->shouldNotReceive('CreateVoucher');

    $this->post(route('comprobante.anular', $comprobante))->assertUnprocessable();

    $this->assertDatabaseCount('comprobante', 1);
});

test('an A or B invoice without VAT details cannot generate an incomplete credit note', function (bool $sinDetalles) {
    $factura = ($this->crearFactura)('A');
    if ($sinDetalles) {
        $factura->detalle()->delete();
    } else {
        $factura->detalle()->update(['iva_id' => null]);
    }
    $this->servicioArca->shouldReceive('GetLastVoucher')->once()->andReturn(10);
    $this->servicioArca->shouldNotReceive('CreateVoucher');

    $this->post(route('comprobante.anular', $factura))->assertUnprocessable();

    expect($factura->fresh()->anulacion_id)->toBeNull();
    $this->assertDatabaseCount('comprobante', 1);
})->with([true, false]);

test('the invoice list only labels cancelled invoices', function () {
    $this->withoutVite();
    $anulada = ($this->crearFactura)('A');
    $anulada->update(['anulacion_id' => 999]);
    ($this->crearFactura)('B');

    $response = $this->get(route('comprobantes.index'))->assertOk()
        ->assertSee('Factura A')
        ->assertSee('Factura B')
        ->assertSee('<em><small>(anulada)</small></em>', false);

    expect(substr_count($response->getContent(), '<em><small>(anulada)</small></em>'))->toBe(1);
});
