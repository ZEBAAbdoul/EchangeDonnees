{{--
    Page : Échanges sécurisés (SED)
    Layout : <x-admin> (même structure que « Gestion des Utilisateurs »)
    Variables : $agent (agent connecté), $admin (bool), fournies par EchangeController.
    Compatible avec les identifiants UUID.
--}}
<x-admin>
@section('title')
    <span style="color: #fff;">Échanges sécurisés</span>
@endsection
    {{-- Messages flash --}}
    @foreach (['success' => ['check-circle', 'success'], 'warning' => ['exclamation-triangle', 'warning'], 'error' => ['exclamation-circle', 'danger']] as $clef => [$icone, $classe])
        @if (session($clef))
            <div class="alert alert-{{ $classe }} alert-dismissible fade show" role="alert">
                <i class="fas fa-{{ $icone }} me-1"></i> {{ session($clef) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif
    @endforeach

    {{-- ===================== EN-TÊTE ===================== --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <p class="text-muted mb-0">
            Échangez des messages chiffrés de bout en bout. Le trousseau reste dans votre navigateur.
        </p>

        <div class="d-flex align-items-center gap-2">
            <span id="etat-trousseau" class="sed-etat ferme">
                <i class="fas fa-lock me-1"></i> Trousseau verrouillé
            </span>
            <button type="button" id="btn-verrouiller-haut" class="btn btn-secondary btn-sm"
                title="Verrouiller le trousseau">
                <i class="fas fa-lock me-1"></i> Verrouiller
            </button>
        </div>
    </div>

    {{-- Bandeau d'erreur technique (affiché par le JS si un fichier ne se charge pas) --}}
    <div id="sed-erreur-js" class="alert alert-danger d-none" role="alert"></div>

    {{-- ===================== NAVIGATION ===================== --}}
    <div class="card sed-filters mb-3">
        <div class="card-body py-2">
            <ul class="nav nav-pills gap-2" id="echange-nav">
                <li class="nav-item">
                    <a class="nav-link" href="#/conversations">
                        <i class="fas fa-comments me-1"></i> Messages
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#/documents">
                        <i class="fas fa-file-shield me-1"></i> Documents
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#/echanges">
                        <i class="fas fa-database me-1"></i> Données
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#/trousseau">
                        <i class="fas fa-key me-1"></i> Trousseau
                    </a>
                </li>
                @if ($admin)
                    <li class="nav-item">
                        <a class="nav-link" href="#/admin">
                            <i class="fas fa-user-shield me-1"></i> Administration
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>

    {{-- ===================== MODULES ===================== --}}
    <div class="sed-echange">
        @include('echange.partials.conversations')
        @include('echange.partials.documents')
        @include('echange.partials.echanges')
        @include('echange.partials.trousseau')

        @if ($admin)
            @include('echange.partials.admin')
        @endif
    </div>

    {{-- Conteneur pour les toasts --}}
    <div id="toastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

    {{-- ===================== CSS ===================== --}}
    @section('css')
        <link rel="stylesheet" href="{{ asset('css/echange.css') }}">
        <style>
            /* ---------- Navigation des modules ---------- */
            #echange-nav .nav-link {
                color: #9bb6d6;
                border: 1px solid rgba(0, 212, 255, .15);
                border-radius: 10px;
                background: rgba(15, 31, 61, .6);
                font-weight: 600;
                transition: all .2s;
            }

            #echange-nav .nav-link:hover {
                color: #fff;
                background: rgba(0, 212, 255, .12);
                border-color: rgba(0, 212, 255, .4);
            }

            #echange-nav .nav-link.active {
                color: #fff;
                background: linear-gradient(135deg, #00d4ff, #007bff);
                border-color: transparent;
            }

            .sed-filters {
                background: rgba(10, 25, 47, .7);
                border: 1px solid rgba(0, 212, 255, .2);
            }

            /* ---------- État du trousseau ---------- */
            .sed-etat {
                display: inline-flex;
                align-items: center;
                padding: 5px 12px;
                border-radius: 20px;
                font-size: .82rem;
                font-weight: 700;
                border: 1px solid transparent;
            }

            .sed-etat.ferme {
                color: #ffb300;
                background: rgba(255, 179, 0, .12);
                border-color: rgba(255, 179, 0, .4);
            }

            .sed-etat.ouvert {
                color: #00e676;
                background: rgba(0, 230, 118, .12);
                border-color: rgba(0, 230, 118, .4);
            }

            /* ---------- Cartes et formulaires des modules ---------- */
            .sed-echange .card {
                background: rgba(10, 25, 47, .7);
                border: 1px solid rgba(0, 212, 255, .2);
                color: #e6f1ff;
            }

            .sed-echange .card-header {
                background: rgba(0, 212, 255, .10);
                color: #00d4ff;
                border-bottom: 1px solid rgba(0, 212, 255, .35);
            }

            .sed-echange .form-control,
            .sed-echange .form-select {
                background-color: rgba(15, 31, 61, .8);
                border: 1px solid rgba(0, 212, 255, .25);
                color: #e6f1ff;
            }

            .sed-echange .form-control:focus,
            .sed-echange .form-select:focus {
                border-color: #00d4ff;
                box-shadow: 0 0 0 3px rgba(0, 212, 255, .12);
            }

            .sed-echange .form-select option {
                background: #0a192f;
                color: #e6f1ff;
            }

            .sed-echange .table {
                --bs-table-color: #e6f1ff;
                --bs-table-bg: transparent;
                --bs-table-hover-bg: rgba(0, 212, 255, .08);
                --bs-table-hover-color: #fff;
                --bs-table-border-color: rgba(0, 212, 255, .12);
            }

            .sed-code {
                background: rgba(15, 31, 61, .9);
                border: 1px solid rgba(0, 212, 255, .3);
                border-radius: 10px;
                color: #00e676;
            }

            /* ---------- Toast ---------- */
            .sed-toast {
                min-width: 280px;
                max-width: 420px;
                padding: 12px 16px;
                margin-bottom: 10px;
                border-radius: 12px;
                color: #fff;
                box-shadow: 0 10px 30px rgba(0, 0, 0, .45);
                border-left: 4px solid rgba(255, 255, 255, .6);
                display: none;
            }

            .sed-toast.success { background: #0f9d58; }
            .sed-toast.error { background: #d93025; }
            .sed-toast.info { background: #1e88e5; }

            .sed-statut.valideur, .sed-statut.attente {
    background: rgba(255, 179, 0, .15); color: #ffb300; border: 1px solid rgba(255, 179, 0, .4);
}
        </style>
    @endsection

    {{-- ===================== JS ===================== --}}
    @section('js')
        <script>
            /**
             * Configuration globale de l'application.
             * Les UUID sont transmis comme chaînes de caractères.
             */
            window.APP = @js([
                'csrf' => csrf_token(),
                'api' => url('/echange/api'),
                'admin' => (bool) $admin,
                'maxDocMo' => 20,   // doit rester <= upload_max_filesize (php.ini) et DocumentController::MAX_KO
                'agent' => [
                    'id' => (string) $agent->id,
                    'nom' => $agent->nom,
                ],
            ]);

            /* ===================== TOAST (utilisé par app.js) ===================== */
            function showToast(message, type = 'success') {
                const icones = {
                    success: 'fa-check-circle',
                    error: 'fa-exclamation-circle',
                    info: 'fa-info-circle'
                };

                const toast = document.createElement('div');
                toast.className = 'sed-toast ' + type;
                toast.setAttribute('role', 'alert');

                const icone = document.createElement('i');
                icone.className = 'fas me-2 ' + (icones[type] || icones.info);

                const texte = document.createElement('span');
                texte.textContent = message; // jamais interprété comme du HTML

                toast.append(icone, texte);
                document.getElementById('toastContainer').appendChild(toast);
                toast.style.display = 'block';

                setTimeout(() => toast.remove(), 4000);
            }
            window.showToast = showToast;

            /* ===================== DIAGNOSTIC : affiche les erreurs de chargement dans la page ===================== */
            window.sedErreur = function(message) {
                const zone = document.getElementById('sed-erreur-js');
                if (!zone) return;
                const p = document.createElement('div');
                p.textContent = message;
                zone.appendChild(p);
                zone.classList.remove('d-none');
            };
        </script>

        {{-- Bibliothèques locales : aucun script tiers pour le chiffrement --}}
                <script src="{{ asset('js/echange/sodium.js') }}"
            onerror="sedErreur('Fichier introuvable : ' + this.getAttribute('src'))"></script>
        <script src="{{ asset('js/echange/echange.js') }}"
            onerror="sedErreur('Fichier introuvable : ' + this.getAttribute('src'))"></script>
            <script src="{{ asset('js/echange/echange-documents.js') }}"></script>

        <script src="{{ asset('js/echange/app.js') }}"
            onerror="sedErreur('Fichier introuvable : ' + this.getAttribute('src'))"></script>
            {{-- // Si, une fois la page chargée, l'interface n'a pas démarré, on le dit clairement --}}
            window.addEventListener('load', function() {
                setTimeout(function() {
                    if (!window.__sedAppPret && !document.querySelector('#sed-erreur-js:not(.d-none)')) {
                        sedErreur("L'interface n'a pas démarré. Ouvrez la console du navigateur (F12) pour voir l'erreur.");
                    }
                }, 1500);
            });
        </script>
    @endsection
</x-admin>