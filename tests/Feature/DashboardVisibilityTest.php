<?php

use App\Models\User;

test('non admin users only see monthly document counters on dashboard', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this
        ->actingAs($user)
        ->get('/dashboard');

    $response
        ->assertOk()
        ->assertSeeText('Facturas del mes')
        ->assertSeeText('Notas de crédito')
        ->assertDontSeeText('Total facturado')
        ->assertDontSeeText('Base imponible IIBB')
        ->assertDontSeeText('Escenarios de pago sobre el 2,5%');
});

test('admin users see full financial summary on dashboard', function () {
    $user = User::factory()->admin()->create();

    $response = $this
        ->actingAs($user)
        ->get('/dashboard');

    $response
        ->assertOk()
        ->assertSeeText('Facturas del mes')
        ->assertSeeText('Notas de crédito')
        ->assertSeeText('Total facturado')
        ->assertSeeText('Base imponible IIBB')
        ->assertSeeText('Escenarios de pago sobre el 2,5%');
});
