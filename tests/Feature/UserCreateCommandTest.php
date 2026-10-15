<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;

test('first user created via command is admin', function () {
    $this->artisan('user:create')
        ->expectsQuestion('Email', 'admin@example.com')
        ->expectsQuestion('Nombre para mostrar', 'Admin General')
        ->expectsChoice(
            '¿Cómo querés completar el alta?',
            'Definir contraseña ahora',
            ['Definir contraseña ahora', 'Enviar email para completar registro']
        )
        ->expectsQuestion('Contraseña', 'password123')
        ->expectsQuestion('Confirmar contraseña', 'password123')
        ->assertExitCode(0);

    $user = User::where('email', 'admin@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->is_admin)->toBeTrue();
});

test('users created after the first one are not admins by default', function () {
    User::factory()->admin()->create();

    $this->artisan('user:create')
        ->expectsQuestion('Email', 'usuario@example.com')
        ->expectsQuestion('Nombre para mostrar', 'Usuario Secundario')
        ->expectsChoice(
            '¿Cómo querés completar el alta?',
            'Definir contraseña ahora',
            ['Definir contraseña ahora', 'Enviar email para completar registro']
        )
        ->expectsQuestion('Contraseña', 'password123')
        ->expectsQuestion('Confirmar contraseña', 'password123')
        ->assertExitCode(0);

    $user = User::where('email', 'usuario@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->is_admin)->toBeFalse();
});

test('command can create a user and send reset link email', function () {
    Notification::fake();
    User::factory()->admin()->create();

    $this->artisan('user:create')
        ->expectsQuestion('Email', 'invitado@example.com')
        ->expectsQuestion('Nombre para mostrar', 'Usuario Invitado')
        ->expectsChoice(
            '¿Cómo querés completar el alta?',
            'Enviar email para completar registro',
            ['Definir contraseña ahora', 'Enviar email para completar registro']
        )
        ->assertExitCode(0);

    $user = User::where('email', 'invitado@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->is_admin)->toBeFalse();

    Notification::assertSentTo($user, ResetPassword::class);
});
