<x-app-layout>
    <div class="container">
        <x-title title="Usuarios">
            <a href="{{ route('usuarios.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Nuevo usuario
            </a>
        </x-title>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="GET" action="{{ route('usuarios.index') }}" class="row g-2 mb-3">
            <div class="col-12 col-md-6">
                <input
                    type="text"
                    name="q"
                    class="form-control"
                    placeholder="Buscar por nombre o email..."
                    value="{{ $search }}"
                >
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Buscar</button>
            </div>
            <div class="col-auto">
                <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">Limpiar</a>
            </div>
        </form>

        @if ($users->count())
            <div class="table-responsive">
                <table class="table table-striped table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Alta</th>
                            <th class="text-end">Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $usuario)
                            <tr>
                                <td>{{ $usuario->name }}</td>
                                <td>{{ $usuario->email }}</td>
                                <td>
                                    @if ($usuario->is_admin)
                                        <span class="badge text-bg-primary">Administrador</span>
                                    @else
                                        <span class="badge text-bg-secondary">Usuario</span>
                                    @endif
                                </td>
                                <td>{{ $usuario->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form
                                        action="{{ route('usuarios.destroy', $usuario) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('¿Eliminar este usuario?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}
        @else
            <div class="alert alert-info mb-0">
                No hay usuarios registrados con los filtros actuales.
            </div>
        @endif
    </div>
</x-app-layout>
