<?php

use App\Models\User;

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
