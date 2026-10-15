<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserProvisioningService
{
    public function createWithPassword(string $name, string $email, string $password, ?bool $isAdmin = null): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'is_admin' => $isAdmin ?? $this->isFirstUser(),
        ]);
    }

    public function createWithResetLink(string $name, string $email, ?bool $isAdmin = null): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(Str::random(40)),
            'is_admin' => $isAdmin ?? $this->isFirstUser(),
        ]);

        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            $user->delete();

            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        return $user;
    }

    public function isFirstUser(): bool
    {
        return User::query()->count() === 0;
    }
}
