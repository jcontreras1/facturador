<?php

use App\Models\Arca\Comprobante;
use App\Models\Arca\TipoComprobante;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->withoutVite();
    $this->actingAs(User::factory()->create());

    $this->crearComprobante = fn (string $tipo, array $datos = []) => Comprobante::create(array_merge([
        'tipo_comprobante_id' => TipoComprobante::where('codigo', $tipo)->firstOrFail()->id,
        'nro_comprobante' => 123,
        'fecha_emision' => '2026-10-10',
        'importe_total' => 100,
    ], $datos));
});

test('an empty listing keeps the search available', function () {
    $this->get(route('comprobantes.index'))->assertOk()
        ->assertSee('No hay comprobantes para mostrar')
        ->assertSee('name="numero"', false)
        ->assertSee('Limpiar');

    $this->get(route('comprobantes.index', ['numero' => '0']))->assertOk()
        ->assertSee('No se encontraron comprobantes para la búsqueda.');
});

test('the type selector only offers types present in the complete listing', function () {
    ($this->crearComprobante)('A');
    $this->get(route('comprobantes.index'))->assertOk()->assertDontSee('Todos los tipos');

    ($this->crearComprobante)('CA', ['nro_comprobante' => 124]);
    $response = $this->get(route('comprobantes.index', ['numero' => 999]));

    $response->assertOk()
        ->assertSee('Todos los tipos')
        ->assertSee('No se encontraron comprobantes para la búsqueda.')
        ->assertViewHas('tiposComprobante', fn ($tipos) => $tipos->pluck('codigo')->sort()->values()->all() === ['A', 'CA']);
});

test('type number and inclusive emission dates can be combined', function (string $numero) {
    $primero = ($this->crearComprobante)('A', ['fecha_emision' => '2026-10-10']);
    $ultimo = ($this->crearComprobante)('A', ['fecha_emision' => '2026-10-11']);
    ($this->crearComprobante)('B');
    ($this->crearComprobante)('A', ['nro_comprobante' => 124]);
    ($this->crearComprobante)('A', ['fecha_emision' => '2026-10-09']);
    ($this->crearComprobante)('A', ['fecha_emision' => '2026-10-12']);

    $this->get(route('comprobantes.index', [
        'tipo_comprobante_id' => $primero->tipo_comprobante_id,
        'numero' => $numero,
        'fecha_desde' => '2026-10-10',
        'fecha_hasta' => '2026-10-11',
    ]))->assertOk()
        ->assertSee('10/10/2026')
        ->assertSee('11/10/2026')
        ->assertViewHas('comprobantes', fn ($items) => $items->pluck('id')->all() === [$ultimo->id, $primero->id]);
})->with(['123', '00000123']);

test('date filters work independently and fall back to the registration date for legacy records', function (array $fechas) {
    $comprobante = ($this->crearComprobante)('C', ['fecha_emision' => null]);
    $comprobante->created_at = '2026-10-10 23:59:59';
    $comprobante->save();
    ($this->crearComprobante)('C', ['fecha_emision' => '2026-10-09']);
    ($this->crearComprobante)('C', ['fecha_emision' => '2026-10-11']);

    $response = $this->get(route('comprobantes.index', $fechas))->assertOk();
    $items = $response->viewData('comprobantes');
    expect($items->pluck('id'))->toContain($comprobante->id);
    expect($items)->toHaveCount(count($fechas) === 2 ? 1 : 2);
})->with([
    [['fecha_desde' => '2026-10-10']],
    [['fecha_hasta' => '2026-10-10']],
    [['fecha_desde' => '2026-10-10', 'fecha_hasta' => '2026-10-10']],
]);

test('pagination preserves all filters and changing the search starts from the first page', function () {
    foreach (range(1, 18) as $numero) {
        ($this->crearComprobante)('A', ['razon_social' => 'Comprobante '.$numero]);
    }
    ($this->crearComprobante)('B');
    ($this->crearComprobante)('A', ['fecha_emision' => '2026-09-10']);

    $filtros = [
        'tipo_comprobante_id' => TipoComprobante::where('codigo', 'A')->firstOrFail()->id,
        'numero' => '00000123',
        'fecha_desde' => '2026-10-10',
        'fecha_hasta' => '2026-10-10',
    ];
    $response = $this->get(route('comprobantes.index', $filtros))->assertOk();
    $primeraPagina = $response->viewData('comprobantes');
    expect($primeraPagina)->toHaveCount(16);

    $url = $primeraPagina->nextPageUrl();
    parse_str(parse_url($url, PHP_URL_QUERY), $parametros);
    foreach ($filtros as $campo => $valor) {
        expect((string) $parametros[$campo])->toBe((string) $valor);
    }

    $segundaPagina = $this->get($url)->assertOk()->assertDontSee('name="cursor"', false)->viewData('comprobantes');
    expect($segundaPagina)->toHaveCount(2)
        ->and($segundaPagina->pluck('id')->intersect($primeraPagina->pluck('id')))->toBeEmpty();

    $this->get(route('comprobantes.index', ['numero' => 999]))->assertOk()
        ->assertSee('No se encontraron comprobantes para la búsqueda.')
        ->assertDontSee('cursor=', false);
    $this->get(route('comprobantes.index'))->assertOk()->assertSee('Comprobante 18');
});

test('invalid filters are rejected with visible validation errors', function (array $filtros, string $campo) {
    $this->from(route('comprobantes.index'))
        ->get(route('comprobantes.index', $filtros))
        ->assertRedirect(route('comprobantes.index'))
        ->assertSessionHasErrors($campo);
    $this->get(route('comprobantes.index'))->assertOk()->assertSee('role="alert"', false);
})->with([
    [['tipo_comprobante_id' => 999], 'tipo_comprobante_id'],
    [['numero' => '12%'], 'numero'],
    [['numero' => ['123']], 'numero'],
    [['tipo_comprobante_id' => ['1']], 'tipo_comprobante_id'],
    [['fecha_desde' => ['2026-10-10']], 'fecha_desde'],
    [['fecha_desde' => '2026-02-30'], 'fecha_desde'],
    [['fecha_hasta' => 'ayer'], 'fecha_hasta'],
    [['fecha_desde' => '2026-10-11', 'fecha_hasta' => '2026-10-10'], 'fecha_hasta'],
]);
