<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\UserProvisioningService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class CreateUserCommand extends Command
{
    protected $signature = 'user:create';

    protected $description = 'Crea un usuario manualmente (con contraseña o por email de activación)';

    public function handle(UserProvisioningService $userProvisioningService): int
    {
        $isFirstUser = $userProvisioningService->isFirstUser();

        if ($isFirstUser) {
            $this->warn('No hay usuarios creados todavía. Este usuario será administrador del sistema.');
        }

        $email = strtolower(trim((string) $this->ask('Email')));
        $name = trim((string) $this->ask('Nombre para mostrar'));

        $validator = Validator::make(
            ['name' => $name, 'email' => $email],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class, 'email')],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $createMode = $this->choice(
            '¿Cómo querés completar el alta?',
            ['Definir contraseña ahora', 'Enviar email para completar registro'],
            0
        );

        try {
            if ($createMode === 'Definir contraseña ahora') {
                $password = (string) $this->secret('Contraseña');
                $passwordConfirmation = (string) $this->secret('Confirmar contraseña');

                Validator::make(
                    [
                        'password' => $password,
                        'password_confirmation' => $passwordConfirmation,
                    ],
                    [
                        'password' => ['required', 'confirmed', Password::defaults()],
                    ]
                )->validate();

                $user = $userProvisioningService->createWithPassword(
                    name: $name,
                    email: $email,
                    password: $password,
                );

                $this->info('Usuario creado correctamente con contraseña definida.');
            } else {
                $user = $userProvisioningService->createWithResetLink(
                    name: $name,
                    email: $email,
                );

                $this->info('Usuario creado correctamente y email enviado para completar el registro.');
            }
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $messages) {
                foreach ($messages as $message) {
                    $this->error($message);
                }
            }

            return self::FAILURE;
        }

        $this->line('Nombre: '.$user->name);
        $this->line('Email: '.$user->email);
        $this->line('Rol: '.($user->is_admin ? 'Administrador' : 'Usuario'));

        return self::SUCCESS;
    }
}
