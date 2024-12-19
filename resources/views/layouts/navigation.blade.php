<nav class="navbar navbar-expand-lg bg-body-tertiary mb-4 border-bottom">
    <div class="container">
        {{-- <a class="navbar-brand" href="#">Navbar</a> --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 fs-6">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'border-bottom border-3 border-info' : '' }}" href="{{ route('dashboard') }}"><strong>{{ __('Home')}}</strong></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('clientes.index') ? 'border-bottom border-3 border-info' : '' }}" href="{{ route('clientes.index') }}">Clientes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('items.index') ? 'border-bottom border-3 border-info' : '' }}" href="{{ route('items.index') }}">Servicios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('facturacion.index') ? 'border-bottom border-3 border-info' : '' }}" href="{{ route('facturacion.index') }}">Facturación</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('config.index') ? 'border-bottom border-3 border-info' : '' }}" href="{{ route('config.index') }}">Configuraciones</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto fs-6">
                @guest
                @if (Route::has('login'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                </li>
                @endif
                
                @if (Route::has('register'))
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                </li>
                @endif
                @else
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ ucfirst(Auth::user()->name) }}
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Perfil</a></li>
                        <li><a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a></li>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </ul>
                </li>
                @endguest
               
            </ul>
            <ul class="navbar-nav me-0">
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
						<i class="fas fa-adjust"></i>
					</a>
					<style type="text/css">
						.navbar .dropdown-menu.show {
							min-width: inherit;
							display: inline-block;
						}
					</style>
					<ul class="dropdown-menu dropdown-menu-end">
						<li><button class="dropdown-item" data-toggle="tooltip" title="Claro" data-bs-theme-value="light"><i class="fas fa-sun"></i></button></li>
						<li><button class="dropdown-item" data-toggle="tooltip" title="Oscuro" data-bs-theme-value="dark"><i class="far fa-moon"></i></button></li>
						<li><button class="dropdown-item" data-toggle="tooltip" title="Color del Sistema" data-bs-theme-value="auto"><i class="fas fa-magic"></i></button></li>
					</ul>
				</li>
			</ul>
        </div>
    </div>
</nav>
