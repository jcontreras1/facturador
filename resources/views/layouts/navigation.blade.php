<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center w-100">
            <!-- Logo -->
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                {{-- Aquí puedes agregar el logo si lo necesitas --}}
            </a>

            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'border-bottom border-3 border-info' : '' }}" href="{{ route('dashboard') }}">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('clientes.index') ? 'border-bottom border-3 border-info' : '' }}" href="{{ route('clientes.index') }}">Clientes</a>
                    </li>
                </ul>
            </div>

            <!-- Settings Dropdown -->
            <div class="d-none d-lg-flex align-items-center ms-3">
                <div class="dropdown">
                    <button class="btn dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ ucfirst(Auth::user()->name) }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUser">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Perfil</a></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Cerrar sesión</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Hamburger Button for Mobile -->
            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </div>
</nav>
