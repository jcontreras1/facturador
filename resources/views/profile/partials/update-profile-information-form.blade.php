<div class="card shadow-sm h-100">
    <div class="card-body">
        <h5 class="card-title">Información del perfil</h5>
        <p class="text-muted mb-4">
            Actualizá tu nombre y correo electrónico.
        </p>

        <form id="send-verification" method="POST" action="{{ route('verification.send') }}">
            @csrf
        </form>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="mb-3">
                <label for="name" class="form-label">Nombre</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $user->name) }}"
                    required
                    autofocus
                    autocomplete="name"
                >
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $user->email) }}"
                    required
                    autocomplete="username"
                >
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="alert alert-warning">
                    <div class="mb-2">Tu correo todavía no está verificado.</div>
                    <button form="send-verification" class="btn btn-sm btn-outline-primary" type="submit">
                        Reenviar email de verificación
                    </button>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success">
                        Se envió un nuevo enlace de verificación a tu correo.
                    </div>
                @endif
            @endif

            <div class="d-flex align-items-center gap-3">
                <button type="submit" class="btn btn-primary">Guardar</button>
                @if (session('status') === 'profile-updated')
                    <span class="text-success small">Guardado correctamente.</span>
                @endif
            </div>
        </form>
    </div>
</div>
