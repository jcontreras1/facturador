<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('admin can create user by email mode even when password fields differ', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('usuarios.store'), [
        'name' => 'Nuevo Usuario',
        'email' => 'nuevo.usuario@example.com',
        'password_mode' => 'email',
        'password' => 'temporal1',
        'password_confirmation' => 'otra-clave',
    ]);

    $user = User::query()->where('email', 'nuevo.usuario@example.com')->first();

    $response->assertSessionHasNoErrors();
    expect($user)->not->toBeNull()
        ->and($user->is_admin)->toBeFalse();

    Notification::assertSentTo($user, ResetPassword::class);
});

test('admin creating user in manual mode still requires password confirmation', function () {
    $admin = User::factory()->admin()->create();

    $response = $this
        ->actingAs($admin)
        ->from(route('usuarios.create'))
        ->post(route('usuarios.store'), [
            'name' => 'Otro Usuario',
            'email' => 'otro.usuario@example.com',
            'password_mode' => 'manual',
            'password' => 'password123',
            'password_confirmation' => 'distinta123',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('usuarios.create'));
});
