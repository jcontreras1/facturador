<x-app-layout>
    <div class="container">
        <x-title title="Editar usuario" urlBack="{{ route('usuarios.index') }}" />

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-3">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="{{ route('usuarios.update', $usuario) }}">
                            @csrf
                            @method('PATCH')

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="name" class="form-label">Nombre para mostrar</label>
                                    <input
                                        id="name"
                                        name="name"
                                        type="text"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $usuario->name) }}"
                                        required
                                    >
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input
                                        id="email"
                                        name="email"
                                        type="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $usuario->email) }}"
                                        required
                                    >
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr>

                            <div class="form-check mb-3">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    value="1"
                                    id="is_admin"
                                    name="is_admin"
                                    {{ old('is_admin', $usuario->is_admin) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="is_admin">
                                    Administrador del sistema
                                </label>
                            </div>
                            @error('is_admin')
                                <div class="text-danger small mb-3">{{ $message }}</div>
                            @enderror

                            <h6 class="mb-3">Restablecer contraseña (opcional)</h6>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label for="password" class="form-label">Nueva contraseña</label>
                                    <input
                                        id="password"
                                        name="password"
                                        type="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                    >
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="password_confirmation" class="form-label">Confirmar nueva contraseña</label>
                                    <input
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        type="password"
                                        class="form-control"
                                    >
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-success">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card border-danger shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-danger">Eliminar usuario</h5>
                        <p class="card-text mb-3">
                            Esta acción elimina definitivamente la cuenta del usuario.
                        </p>

                        <form
                            method="POST"
                            action="{{ route('usuarios.destroy', $usuario) }}"
                            onsubmit="return confirm('¿Seguro que querés eliminar este usuario?');"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
