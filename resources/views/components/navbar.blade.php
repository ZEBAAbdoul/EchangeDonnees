@auth
@php
    $user = Auth::user()->loadMissing(['roles', 'personne']);
    $fullName = $user->personne?->nom_complet ?: $user->email;
    $displayName = $user->personne?->prenom ?: $user->email;
    $roles = $user->roles->pluck('name')->map(fn ($r) => ucfirst($r))->join(', ');
    $avatar = $user->avatar ?: asset('admin/dist/img/icon-user.png');

    $afficherCloche = $afficherCloche ?? false;
    $inscriptionsEnCoursCount = $inscriptionsEnCoursCount ?? 0;
    $inscriptionsEnCours = $inscriptionsEnCours ?? collect();
@endphp

{{-- Styles de la barre du haut (thème sombre SED), chargés une seule fois --}}
@once
    <style>
        .main-header.navbar {
            background: rgba(10, 25, 47, .92) !important;
            border-bottom: 1px solid rgba(0, 212, 255, .25) !important;
            backdrop-filter: blur(10px);
        }

        .main-header .navbar-nav .nav-link {
            color: rgba(230, 241, 255, .85);
            transition: color .2s;
        }

        .main-header .navbar-nav .nav-link:hover,
        .main-header .navbar-nav .show > .nav-link { color: #00d4ff; }

        .main-header .navbar-badge {
            font-size: .62rem;
            font-weight: 700;
            box-shadow: 0 0 0 2px #0a192f;
        }

        /* Avatar avec liseré cyan */
        .main-header .user-menu .img-circle {
            border: 2px solid rgba(0, 212, 255, .55);
            box-shadow: 0 0 10px rgba(0, 212, 255, .3);
            transition: box-shadow .25s, border-color .25s;
        }

        .main-header .user-menu .nav-link:hover .img-circle {
            border-color: #00d4ff;
            box-shadow: 0 0 16px rgba(0, 212, 255, .55);
        }

        /* Menus déroulants en verre sombre */
        .main-header .dropdown-menu {
            background: rgba(10, 25, 47, .97);
            border: 1px solid rgba(0, 212, 255, .3);
            border-radius: 12px;
            backdrop-filter: blur(12px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, .5), 0 0 30px rgba(0, 212, 255, .08);
            padding: 6px;
        }

        .main-header .dropdown-menu .dropdown-item {
            color: #cfe3ff;
            border-radius: 8px;
            transition: background .2s, color .2s;
        }

        .main-header .dropdown-menu .dropdown-item:hover,
        .main-header .dropdown-menu .dropdown-item:focus {
            background: rgba(0, 212, 255, .12);
            color: #fff;
        }

        .main-header .dropdown-menu .dropdown-item.active {
            background: linear-gradient(135deg, #00d4ff, #007bff);
            color: #fff;
        }

        .main-header .dropdown-menu .dropdown-item.text-danger { color: #f87171 !important; }
        .main-header .dropdown-menu .dropdown-item.text-danger:hover { background: rgba(220, 38, 38, .15); color: #fecaca !important; }

        .main-header .dropdown-menu .dropdown-header { color: #7a9cc0; font-size: .8rem; letter-spacing: .04em; }
        .main-header .dropdown-menu .dropdown-footer { color: #00d4ff; text-align: center; }
        .main-header .dropdown-menu .dropdown-divider { border-top-color: rgba(0, 212, 255, .15); }
        .main-header .dropdown-menu .text-muted { color: #7a9cc0 !important; }

        @media (prefers-reduced-motion: reduce) {
            .main-header .navbar-nav .nav-link,
            .main-header .user-menu .img-circle,
            .main-header .dropdown-menu .dropdown-item { transition: none; }
        }
    </style>
@endonce

<nav class="main-header navbar navbar-expand navbar-dark">
    <!-- Liens de gauche -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Afficher/masquer le menu">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </a>
        </li>
    </ul>

    <!-- Liens de droite -->
    <ul class="navbar-nav ml-auto">

        <!-- Cloche : inscriptions en cours -->
        @if ($afficherCloche)
            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#" role="button"
                   aria-label="Inscriptions en cours">
                    <i class="far fa-bell" aria-hidden="true"></i>
                    @if ($inscriptionsEnCoursCount > 0)
                        <span class="badge badge-warning navbar-badge">
                            {{ $inscriptionsEnCoursCount > 99 ? '99+' : $inscriptionsEnCoursCount }}
                        </span>
                    @endif
                </a>

                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">
                        {{ $inscriptionsEnCoursCount }}
                        inscription{{ $inscriptionsEnCoursCount > 1 ? 's' : '' }} en cours
                    </span>

                    @forelse ($inscriptionsEnCours as $inscription)
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('admin.inscriptions.show', $inscription) }}" class="dropdown-item">
                            <i class="fas fa-file-alt mr-2 text-warning" aria-hidden="true"></i>
                            <span class="font-weight-bold">
                                {{ $inscription->candidat->name ?? '' }} {{ $inscription->candidat->forname ?? '' }}
                            </span>
                            <span class="d-block small text-muted text-truncate">
                                {{ $inscription->numero_dossier }} · {{ $inscription->formation->intitule ?? '—' }}
                            </span>
                            <span class="d-block text-muted text-sm">
                                {{ $inscription->date_soumission?->diffForHumans() }}
                            </span>
                        </a>
                    @empty
                        <div class="dropdown-divider"></div>
                        <span class="dropdown-item text-muted text-center">Aucune inscription en cours</span>
                    @endforelse

                    <div class="dropdown-divider"></div>
                    <a href="{{ route('admin.inscriptions.index', ['statut' => 'en_cours']) }}"
                       class="dropdown-item dropdown-footer">Voir toutes les inscriptions</a>
                </div>
            </li>
        @endif

        <!-- Menu utilisateur -->
        <li class="nav-item dropdown user-menu">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown"
               role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <img src="{{ $avatar }}" alt="Avatar de {{ $user->username }}"
                     class="img-circle elevation-1 mr-lg-2" width="30" height="30" style="object-fit: cover;">
                <span class="d-none d-lg-inline">{{ $user->username }}</span>
            </a>

            <div class="dropdown-menu dropdown-menu-right shadow" aria-labelledby="userDropdown">
                <!-- En-tête utilisateur -->
                {{-- <div class="dropdown-header text-center py-3">
                    <img class="img-circle elevation-2 mb-2" src="{{ $avatar }}"
                         alt="" width="60" height="60" style="object-fit: cover;">
                    <p class="mb-0 font-weight-bold text-body">{{ $fullName }}</p>
                    <small class="text-muted d-block">{{ $user->username }}</small>
                    @if ($roles)
                        <span class="badge badge-primary mt-1">{{ $roles }}</span>
                    @endif
                </div> --}}

                {{-- <div class="dropdown-divider"></div> --}}

                <a class="dropdown-item {{ request()->routeIs('admin.profile.edit') ? 'active' : '' }}"
                   href="{{ route('admin.profile.edit') }}">
                    <i class="fas fa-user fa-fw mr-2 text-muted" aria-hidden="true"></i> Mon profil
                </a>

                <a class="dropdown-item {{ request()->routeIs('admin.profile.password.edit') ? 'active' : '' }}"
                   href="{{ route('admin.profile.password.edit') }}">
                    <i class="fas fa-key fa-fw mr-2 text-muted" aria-hidden="true"></i> Modifier le mot de passe
                </a>

                <div class="dropdown-divider"></div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt fa-fw mr-2" aria-hidden="true"></i> Déconnexion
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>
@endauth