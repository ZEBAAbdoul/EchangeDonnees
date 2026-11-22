{{--
    Menu latéral de l'administration DGTI (composant <x-sidebar />, ex. resources/views/components/sidebar.blade.php).

    Le menu est décrit dans le tableau $menu : ajouter une entrée = ajouter une ligne.
    Une entrée dont la route n'existe pas (Route::has) est ignorée, donc aucune erreur « Route not defined »
    si un module n'est pas encore créé. Un groupe sans entrée visible disparaît.
    Les entrées marquées 'admin' => true ne sont visibles que pour les rôles super-admin et admin.
    Les entrées marquées 'agent' => true ne sont visibles que pour les comptes rattachés à un agent actif
    (module « Échanges sécurisés »).
    Les entrées marquées 'badge' => 'nonLus' affichent le nombre de messages de contact non lus.

    Format d'une entrée : [libellé, icône Font Awesome, nom de route, motif(s) de route pour l'état actif]
--}}

@php
    $user = auth()->user();
    $isAdmin = $user && $user->hasAnyRole(['super-admin', 'admin']);

    // Valeur par défaut si le composant n'a pas reçu $nonLus (évite « Undefined variable »)
    $nonLus = $nonLus ?? 0;

    // Compte rattaché à un agent actif ? (rescue : pas d'erreur si la table « agents » n'existe pas encore)
    $aUnAgent = $user && rescue(
        fn () => \App\Models\Agent::where('user_id', $user->id)->where('actif', true)->exists(),
        false,
        false
    );

    $menu = [
        ['item', ['Tableau de bord', 'fa-tachometer-alt', 'admin.dashboard', 'admin.dashboard']],

        // Module « Échanges sécurisés » : visible uniquement pour un agent actif
        ['item', ['Échanges sécurisés', 'fa-lock', 'admin.echange', ['admin.echange', 'admin.echange.*'], 'agent' => true]],

        // ['group', 'Page d’accueil', 'fa-home', [
        //     ['Diapositives du hero', 'fa-images', 'admin.slides.index', 'admin.slides.*'],
        //     ['Projets', 'fa-project-diagram', 'admin.projets.index', 'admin.projets.*'],
        //     ['Une minute pour comprendre', 'fa-lightbulb', 'admin.explications.index', 'admin.explications.*'],
        //     ['Chiffres clés', 'fa-chart-line', 'admin.stats-accueil.index', 'admin.stats-accueil.*'],
        // ]],

        // [
        //     'group',
        //     'Informations',
        //     'fa-bullhorn',
        //     [['Liste des informations', 'fa-list', 'admin.informations.index', 'admin.informations.*']],
        // ],

        // [
        //     'group',
        //     'Bibliothèque',
        //     'fa-book',
        //     [
        //         ['Documents', 'fa-file-alt', 'admin.documents.index', 'admin.documents.*'],
        //         [
        //             'Catégories de documents',
        //             'fa-tags',
        //             'admin.categories-documents.index',
        //             'admin.categories-documents.*',
        //         ],
        //     ],
        // ],

        [
            'group',
            'Galeries',
            'fa-images',
            [
                ['Photos', 'fa-camera', 'admin.photos.index', 'admin.photos.*'],
                ['Vidéos', 'fa-video', 'admin.videos.index', 'admin.videos.*'],
            ],
        ],

        // [
        //     'group',
        //     'Messages',
        //     'fa-envelope',
        //     [['Tous les messages', 'fa-inbox', 'admin.contacts.index', 'admin.contacts.*', 'badge' => 'nonLus']],
        // ],

        // [
        //     'group',
        //     'Statistiques',
        //     'fa-chart-bar',
        //     [
        //         [
        //             'Statistiques globales',
        //             'fa-th-large',
        //             'admin.statistiques.fonctionnalites',
        //             'admin.statistiques.fonctionnalites',
        //         ],
        //         ['Visiteurs du site', 'fa-users', 'admin.statistiques.visiteurs', 'admin.statistiques.visiteurs'],
        //     ],
        // ],

        ['header', 'Divers'],

        [
            'group',
            'Paramètres',
            'fa-cogs',
            [
                ['Utilisateurs', 'fa-users', 'admin.user.index', 'admin.user.*', 'admin' => true],
                ['Slides', 'fa-images', 'admin.slides.index', 'admin.slides.*'],
                ['Paramètres du site', 'fa-sliders-h', 'admin.parametres.index', 'admin.parametres.*', 'admin' => true],
                // ['Partenaires', 'fa-handshake', 'admin.partenaires.index', 'admin.partenaires.*'],
            ],
        ],
    ];

    // Une entrée est visible si sa route existe et si l'utilisateur a le droit de la voir
    $visible = fn($e) => \Illuminate\Support\Facades\Route::has($e[2])
        && (empty($e['admin']) || $isAdmin)
        && (empty($e['agent']) || $aUnAgent);
    $actif = fn($e) => \Illuminate\Support\Facades\Route::is(...(array) $e[3]);
@endphp

<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

        @foreach ($menu as $bloc)
            @php $type = $bloc[0]; @endphp

            {{-- Entrée simple --}}
            @if ($type === 'item' && $visible($bloc[1]))
                @php $e = $bloc[1]; @endphp
                <li class="nav-item">
                    <a href="{{ route($e[2]) }}" class="nav-link {{ $actif($e) ? 'active' : '' }}">
                        <i class="nav-icon fas {{ $e[1] }}"></i>
                        <p>{{ $e[0] }}</p>
                    </a>
                </li>

                {{-- Titre de section --}}
            @elseif ($type === 'header')
                <li class="nav-header">{{ $bloc[1] }}</li>

                {{-- Groupe déroulant --}}
            @elseif ($type === 'group')
                @php
                    $items = array_values(array_filter($bloc[3], $visible));
                    $groupeActif = collect($items)->contains(fn($e) => $actif($e));
                    $groupeBadge = collect($items)->contains(fn($e) => ($e['badge'] ?? null) === 'nonLus')
                        ? $nonLus
                        : 0;
                @endphp

                @if (count($items))
                    <li class="nav-item {{ $groupeActif ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ $groupeActif ? 'active' : '' }}">
                            <i class="nav-icon fas {{ $bloc[2] }}"></i>
                            <p>
                                {{ $bloc[1] }}
                                @if ($groupeBadge)
                                    <span class="badge badge-danger ml-1">{{ $groupeBadge }}</span>
                                @endif
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach ($items as $e)
                                <li class="nav-item">
                                    <a href="{{ route($e[2]) }}" class="nav-link {{ $actif($e) ? 'active' : '' }}">
                                        <i class="fas {{ $e[1] }} nav-icon"></i>
                                        <p>
                                            {{ $e[0] }}
                                            @if (($e['badge'] ?? null) === 'nonLus' && $nonLus)
                                                <span class="badge badge-danger right">{{ $nonLus }}</span>
                                            @endif
                                        </p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endif
            @endif
        @endforeach

    </ul>
</nav>