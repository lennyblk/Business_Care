<header>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <img src="{{ asset('images/logo.svg') }}" alt="Business Care" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('services') ? 'active' : '' }}" href="{{ route('services') }}">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">À propos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Contact</a>
                    </li>
                </ul>

                <div class="navbar-nav">
                    @auth
                        <!-- Menu déroulant pour l'utilisateur connecté -->
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ Auth::user()->name }} <!-- Affiche le nom de l'utilisateur -->
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <!-- Lien vers le tableau de bord (adapté au type d'utilisateur) -->
                                @if(Auth::user()->user_type === 'admin')
                                    <li><a class="dropdown-item" href="{{ route('dashboard.admin') }}">Tableau de bord</a></li>
                                @elseif(Auth::user()->user_type === 'client')
                                    <li><a class="dropdown-item" href="{{ route('dashboard.client') }}">Tableau de bord</a></li>
                                @elseif(Auth::user()->user_type === 'employe')
                                    <li><a class="dropdown-item" href="{{ route('dashboard.employee') }}">Tableau de bord</a></li>
                                @elseif(Auth::user()->user_type === 'prestataire')
                                    <li><a class="dropdown-item" href="{{ route('dashboard.provider') }}">Tableau de bord</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <!-- Lien de déconnexion (GET) -->
                                <li>
                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')">Déconnexion</a>
                                </li>
                            </ul>
                        </div>
                    @else
                        <!-- Liens pour les utilisateurs non connectés -->
                        <a href="{{ route('login') }}" class="nav-link">Connexion</a>
                        <a href="{{ route('register') }}" class="nav-link">Inscription</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</header>
