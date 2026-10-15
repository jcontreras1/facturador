<div class="card shadow-sm h-100">
    <div class="card-body">
        <h5 class="card-title">Contraseña</h5>
        <p class="text-muted mb-4">
            Usá una contraseña robusta para proteger tu cuenta.
        </p>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="update_password_current_password" class="form-label">Contraseña actual</label>
                <input
                    id="update_password_current_password"
                    name="current_password"
                    type="password"
                    class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                    autocomplete="current-password"
                >
                @error('current_password', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="update_password_password" class="form-label">Nueva contraseña</label>
                <input
                    id="update_password_password"
                    name="password"
                    type="password"
                    class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                    autocomplete="new-password"
                >
                @error('password', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="update_password_password_confirmation" class="form-label">Confirmar nueva contraseña</label>
                <input
                    id="update_password_password_confirmation"
                    name="password_confirmation"
                    type="password"
                    class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                    autocomplete="new-password"
                >
                @error('password_confirmation', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex align-items-center gap-3">
                <button type="submit" class="btn btn-primary">Guardar contraseña</button>
                @if (session('status') === 'password-updated')
                    <span class="text-success small">Guardado correctamente.</span>
                @endif
            </div>
        </form>
    </div>
</div>
