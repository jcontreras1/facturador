<div class="card border-danger shadow-sm">
    <div class="card-body">
        <h5 class="card-title text-danger">Eliminar cuenta</h5>
        <p class="text-muted">
            Esta acción elimina permanentemente tu cuenta y todos sus datos asociados.
        </p>
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
            Eliminar cuenta
        </button>
    </div>
</div>

<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')

                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="deleteAccountModalLabel">Confirmar eliminación de cuenta</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        Para confirmar esta acción, ingresá tu contraseña actual.
                    </p>
                    <label for="delete_account_password" class="form-label">Contraseña</label>
                    <input
                        id="delete_account_password"
                        name="password"
                        type="password"
                        class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                        placeholder="Contraseña"
                    >
                    @error('password', 'userDeletion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Eliminar definitivamente</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($errors->userDeletion->isNotEmpty())
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelector('[data-bs-target="#deleteAccountModal"]')?.click();
            });
        </script>
    @endpush
@endif
