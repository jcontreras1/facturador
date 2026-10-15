<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('users.index')->with(compact(['users', 'search']));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request, UserProvisioningService $userProvisioningService): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'password_mode' => ['required', Rule::in(['manual', 'email'])],
            'password' => [
                Rule::requiredIf($request->input('password_mode') === 'manual'),
                'nullable',
                'confirmed',
                Password::defaults(),
            ],
        ]);

        $user = $validated['password_mode'] === 'manual'
            ? $userProvisioningService->createWithPassword(
                name: $validated['name'],
                email: $validated['email'],
                password: $validated['password'],
                isAdmin: false,
            )
            : $userProvisioningService->createWithResetLink(
                name: $validated['name'],
                email: $validated['email'],
                isAdmin: false,
            );

        toast('Usuario creado correctamente', 'success')->autoClose(2000);

        return redirect()->route('usuarios.edit', $user);
    }

    public function edit(User $usuario): View
    {
        return view('users.edit')->with(compact(['usuario']));
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')->ignore($usuario->id)],
            'is_admin' => ['nullable', 'boolean'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $becomesAdmin = $request->boolean('is_admin');
        if ($usuario->is_admin && ! $becomesAdmin && $this->adminCount() <= 1) {
            return back()
                ->withErrors(['is_admin' => 'Debe existir al menos un administrador activo.'])
                ->withInput();
        }

        $usuario->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_admin' => $becomesAdmin,
        ]);

        if ($usuario->isDirty('email')) {
            $usuario->email_verified_at = null;
        }

        if (! empty($validated['password'])) {
            $usuario->password = $validated['password'];
        }

        $usuario->save();

        toast('Usuario actualizado correctamente', 'success')->autoClose(2000);

        return redirect()->route('usuarios.edit', $usuario);
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        if ($request->user()->id === $usuario->id) {
            return back()->withErrors(['user' => 'No podés eliminar tu propio usuario desde esta pantalla.']);
        }

        if ($usuario->is_admin && $this->adminCount() <= 1) {
            return back()->withErrors(['user' => 'No podés eliminar el último administrador del sistema.']);
        }

        $usuario->delete();

        toast('Usuario eliminado correctamente', 'success')->autoClose(2000);

        return redirect()->route('usuarios.index');
    }

    protected function adminCount(): int
    {
        return User::query()->where('is_admin', true)->count();
    }
}
