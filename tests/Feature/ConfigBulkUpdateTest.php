<?php

use App\Models\User;
use App\Models\Arca\Iva;
use App\Models\VariableGlobal;
use Database\Seeders\DatabaseSeeder;

test('admin can update multiple configuration fields in one request', function () {
    $this->seed(\Database\Seeders\DatabaseSeeder::class);

    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->patch(route('config.bulkUpdate'), [
        'config' => [
            'CUIT_EMPRESA' => '20123456789',
            'RAZON_SOCIAL' => 'Empresa de Prueba SRL',
            'DOMICILIO_FISCAL' => 'Av. Siempreviva 742',
            'FECHA_INICIO_ACTIVIDADES' => '2026-10-15',
            'CONDICION_IVA' => 'IVA Responsable Inscripto',
            'PUNTO_VENTA' => '0001',
            'TIPO_IMPRESORA' => 'A4',
            'TOPE_FACTURACION_CONSUMIDOR_FINAL' => '500000',
        ],
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('variable_global', [
        'clave' => 'CUIT_EMPRESA',
        'valor' => '20123456789',
    ]);

    $this->assertDatabaseHas('variable_global', [
        'clave' => 'RAZON_SOCIAL',
        'valor' => 'Empresa de Prueba SRL',
    ]);

    $this->assertDatabaseHas('variable_global', [
        'clave' => 'PUNTO_VENTA',
        'valor' => '0001',
    ]);
});

test('non admin users cannot bulk update configuration fields', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch(route('config.bulkUpdate'), [
        'config' => [
            'CUIT_EMPRESA' => '20999999999',
        ],
    ]);

    $response->assertForbidden();
});

test('admin can save and clear a default VAT rate without a later seed overwriting it', function () {
    $this->seed(DatabaseSeeder::class);
    $this->actingAs(User::factory()->admin()->create());
    $iva = Iva::where('codigo_afip', '5')->firstOrFail();

    $this->patch(route('config.bulkUpdate'), [
        'config' => ['IVA_PREDETERMINADO' => $iva->id],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $this->seed(DatabaseSeeder::class);
    expect(variable_global('IVA_PREDETERMINADO'))->toBe((string) $iva->id);

    $this->patch(route('config.bulkUpdate'), [
        'config' => ['IVA_PREDETERMINADO' => ''],
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(variable_global('IVA_PREDETERMINADO'))->toBe('');
});

test('invalid default VAT rates are rejected through both configuration endpoints', function () {
    $this->seed(DatabaseSeeder::class);
    $this->actingAs(User::factory()->admin()->create());
    $variable = VariableGlobal::where('clave', 'IVA_PREDETERMINADO')->firstOrFail();

    $this->patch(route('config.bulkUpdate'), [
        'config' => ['IVA_PREDETERMINADO' => 99999],
    ])->assertSessionHasErrors('config.IVA_PREDETERMINADO');

    $this->patch(route('config.update', $variable), ['valor' => 99999])
        ->assertSessionHasErrors('valor');

    expect(variable_global('IVA_PREDETERMINADO'))->toBe('');
});

test('default VAT configuration is visible only for registered issuers', function (string $condicion, bool $visible) {
    $this->seed(DatabaseSeeder::class);
    $this->withoutVite();
    $this->actingAs(User::factory()->admin()->create());
    VariableGlobal::where('clave', 'CONDICION_IVA')->update(['valor' => $condicion]);

    $response = $this->get(route('config.index'))->assertOk()
        ->assertSee('Alícuota de IVA predeterminada')
        ->assertViewHas('ivas', fn ($ivas) => $ivas->contains('descripcion', '21%'));

    $hidden = preg_match('/id="ivaPredeterminadoCard"[^>]*\bhidden\b/', $response->getContent()) === 1;
    expect($hidden)->toBe(!$visible);
})->with([
    ['IVA Responsable Inscripto', true],
    ['Responsable Monotributo', false],
]);

test('default VAT can be configured before rerunning the seeder on an existing installation', function () {
    $this->seed(DatabaseSeeder::class);
    $this->actingAs(User::factory()->admin()->create());
    VariableGlobal::where('clave', 'IVA_PREDETERMINADO')->delete();
    $iva = Iva::where('codigo_afip', '3')->firstOrFail();

    $this->patch(route('config.bulkUpdate'), [
        'config' => ['IVA_PREDETERMINADO' => $iva->id],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $this->assertDatabaseHas('variable_global', [
        'clave' => 'IVA_PREDETERMINADO',
        'valor' => (string) $iva->id,
    ]);
});
