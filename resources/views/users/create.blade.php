<x-app-layout>
    <div class="container">
        <x-title title="Nuevo usuario" urlBack="{{ route('usuarios.index') }}" />

        <div class="card shadow-sm">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('usuarios.store') }}">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="name" class="form-label">Nombre para mostrar</label>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}"
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
                                value="{{ old('email') }}"
                                required
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3">Definición de acceso</h6>
                    <div class="form-check mb-2">
                        <input
                            class="form-check-input"
                            type="radio"
                            name="password_mode"
                            id="password_mode_manual"
                            value="manual"
                            {{ old('password_mode', 'manual') === 'manual' ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="password_mode_manual">
                            Definir contraseña ahora
                        </label>
                    </div>
                    <div class="form-check mb-3">
                        <input
                            class="form-check-input"
                            type="radio"
                            name="password_mode"
                            id="password_mode_email"
                            value="email"
                            {{ old('password_mode') === 'email' ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="password_mode_email">
                            Enviar email para que complete el registro
                        </label>
                    </div>
                    @error('password_mode')
                        <div class="text-danger small mb-3">{{ $message }}</div>
                    @enderror

                    <div id="password-fields" class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="password" class="form-label">Contraseña</label>
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
                            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                class="form-control"
                            >
                        </div>
                    </div>

                    <div id="email-info" class="alert alert-info d-none mt-3 mb-0">
                        Se enviará un correo con enlace para definir la contraseña.
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-success">Crear usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modeManual = document.getElementById('password_mode_manual');
                const modeEmail = document.getElementById('password_mode_email');
                const passwordFields = document.getElementById('password-fields');
                const emailInfo = document.getElementById('email-info');
                const passwordInput = document.getElementById('password');
                const passwordConfirmInput = document.getElementById('password_confirmation');

                const syncMode = () => {
                    const isManual = modeManual.checked;

                    passwordFields.classList.toggle('d-none', !isManual);
                    emailInfo.classList.toggle('d-none', isManual);
                    passwordInput.required = isManual;
                    passwordConfirmInput.required = isManual;
                };

                modeManual.addEventListener('change', syncMode);
                modeEmail.addEventListener('change', syncMode);
                syncMode();
            });
        </script>
    @endpush
</x-app-layout>
