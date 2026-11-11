<?php

use App\Models\Cliente;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->withoutVite();
    $this->actingAs(User::factory()->create());
});

test('client search is rendered below the heading and its action buttons', function () {
    $this->get(route('clientes.index'))->assertOk()
        ->assertSeeInOrder([
            'Facturación mensual',
            'Agregar cliente',
            '</h2>',
            '<form method="GET"',
        ], false);
});

test('clients are paginated alphabetically', function () {
    foreach (range(21, 1) as $numero) {
        Cliente::create([
            'nombre' => sprintf('Cliente %02d', $numero),
            'cuit' => (string) (20000000000 + $numero),
        ]);
    }

    $this->get(route('clientes.index'))->assertOk()
        ->assertViewHas('clientes', fn ($clientes) => $clientes->total() === 21
            && $clientes->count() === 20
            && $clientes->first()->nombre === 'Cliente 01'
            && $clientes->last()->nombre === 'Cliente 20')
        ->assertDontSee('Cliente 21');

    $this->get(route('clientes.index', ['page' => 2]))->assertOk()
        ->assertViewHas('clientes', fn ($clientes) => $clientes->currentPage() === 2
            && $clientes->count() === 1
            && $clientes->first()->nombre === 'Cliente 21');
});

test('clients can be searched by name CUIT or email', function (string $search) {
    $cliente = Cliente::create([
        'nombre' => 'Comercial Ejemplo',
        'cuit' => '30712345678',
        'email' => 'ventas@ejemplo.com',
    ]);
    Cliente::create(['nombre' => 'Sin coincidencia', 'cuit' => '20111111112']);

    $this->get(route('clientes.index', ['q' => $search]))->assertOk()
        ->assertViewHas('search', trim($search))
        ->assertViewHas('clientes', fn ($clientes) => $clientes->total() === 1
            && $clientes->first()->is($cliente))
        ->assertSee('Comercial Ejemplo')
        ->assertDontSee('Sin coincidencia');
})->with([' Comercial ', '712345', 'ventas@ejemplo.com']);

test('pagination keeps the search and only includes matching clients', function () {
    foreach (range(1, 21) as $numero) {
        Cliente::create([
            'nombre' => sprintf('Coincidente %02d', $numero),
            'cuit' => (string) (20000000000 + $numero),
        ]);
    }
    Cliente::create(['nombre' => 'Ajeno', 'cuit' => '30999999999']);

    $response = $this->get(route('clientes.index', ['q' => 'Coincidente']))->assertOk()
        ->assertViewHas('clientes', fn ($clientes) => $clientes->total() === 21);
    $nextPageUrl = $response->viewData('clientes')->nextPageUrl();

    expect($nextPageUrl)->toContain('q=Coincidente');

    $this->get($nextPageUrl)->assertOk()
        ->assertSee('Coincidente 21')
        ->assertDontSee('Ajeno')
        ->assertViewHas('clientes', fn ($clientes) => $clientes->count() === 1);
});

test('empty searches show all clients and missing matches show a specific message', function () {
    $this->get(route('clientes.index'))->assertOk()
        ->assertSee('No hay clientes registrados');

    Cliente::create(['nombre' => 'Cliente existente', 'cuit' => '20123456789']);

    $this->get(route('clientes.index', ['q' => '   ']))->assertOk()
        ->assertViewHas('clientes', fn ($clientes) => $clientes->total() === 1);

    $this->get(route('clientes.index', ['q' => 'inexistente']))->assertOk()
        ->assertViewHas('clientes', fn ($clientes) => $clientes->total() === 0)
        ->assertSee('No se encontraron clientes para la búsqueda.')
        ->assertSee('name="q"', false)
        ->assertSee('Limpiar');
});
