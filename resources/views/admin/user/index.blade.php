<x-admin>
    @section('title', 'Gestion des Utilisateurs')

    {{-- Messages flash (retour des pages create / edit) --}}
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
        <p class="text-muted mb-0">Créez, modifiez, désactivez ou supprimez les comptes ayant accès au système.</p>
        <a href="{{ route('admin.user.create') }}" class="btn btn-primary fw-bold shadow">
            <i class="fas fa-user-plus me-1"></i> Ajouter un utilisateur
        </a>
    </div>

    {{-- ===================== FILTRES ===================== --}}
    <div class="card sed-filters mb-3">
        <div class="card-body py-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-4 col-sm-6">
                    <label for="filterRole" class="form-label fw-bold mb-1">
                        <i class="fas fa-user-shield me-1"></i> Rôle
                    </label>
                    <select id="filterRole" class="form-select">
                        <option value="">Tous les rôles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 col-sm-6">
                    <label for="filterStatut" class="form-label fw-bold mb-1">
                        <i class="fas fa-toggle-on me-1"></i> Statut
                    </label>
                    <select id="filterStatut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="actif">Actifs</option>
                        <option value="inactif">Désactivés</option>
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2 justify-content-md-end">
                    <button type="button" class="btn btn-secondary" id="btnResetFiltres">
                        <i class="fas fa-undo me-1"></i> Réinitialiser
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="btnRefresh">
                        <i class="fas fa-sync-alt me-1"></i> Actualiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== TABLE ===================== --}}
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title mb-0">
                <i class="fas fa-users me-1"></i> Liste des utilisateurs
            </h3>
        </div>

        <div class="card-body">
            <table id="usersTable" class="table table-hover align-middle nowrap w-100">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle(s)</th>
                        <th>Statut</th>
                        <th>Date création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    {{-- Conteneur pour les toasts --}}
    <div id="toastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>

    {{-- ===================== MODALS ===================== --}}
    @include('admin.user.partials.delete')

    {{-- Modal : réinitialiser le mot de passe (2 étapes : confirmation, puis affichage du mot de passe) --}}
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordTitle"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">

                <div class="modal-header bg-warning text-dark" id="resetPasswordHeader">
                    <h5 class="modal-title" id="resetPasswordTitle">
                        <i class="fas fa-key me-2"></i>
                        <span id="resetPasswordTitleText">Réinitialiser le mot de passe ?</span>
                    </h5>
                    <button type="button" class="btn-close" id="resetCloseX" data-bs-dismiss="modal"
                        aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    <div id="resetError" class="alert alert-danger d-none" role="alert"></div>

                    {{-- Étape 1 : confirmation --}}
                    <div id="resetStepConfirm">
                        <p class="mb-2">Vous êtes sur le point de réinitialiser le mot de passe du compte suivant :</p>

                        <div class="sed-identity">
                            <div class="fw-bold" id="resetName"></div>
                            <div class="text-muted small" id="resetEmail"></div>
                        </div>

                        <p class="small text-muted mt-3 mb-0">
                            Le mot de passe actuel sera remplacé par le mot de passe par défaut du SED.
                            Les connexions « Se souvenir de moi » de cet utilisateur seront invalidées.
                        </p>
                    </div>

                    {{-- Étape 2 : résultat (le mot de passe n'est jamais présent dans le HTML de la page) --}}
                    <div id="resetStepDone" class="d-none">
                        <div class="alert alert-success mb-3">
                            <i class="fas fa-check-circle me-1"></i> Le mot de passe a été réinitialisé.
                        </div>

                        <label class="form-label fw-bold">Nouveau mot de passe temporaire</label>
                        <div class="d-flex align-items-center gap-2">
                            <code id="resetPwdValue" class="sed-code fs-5 px-3 py-2 flex-grow-1 user-select-all"></code>
                            <button type="button" class="btn btn-outline-primary" id="resetCopyBtn">
                                <i class="fas fa-copy me-1"></i> Copier
                            </button>
                        </div>

                        <p class="small text-muted mt-3 mb-0">
                            Communiquez-le à l'utilisateur de façon sécurisée et invitez-le à le modifier
                            depuis son profil dès sa prochaine connexion.
                        </p>
                    </div>
                </div>

                <div class="modal-footer" id="resetFooterConfirm">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        id="resetCancelBtn">Annuler</button>
                    <button type="button" class="btn btn-warning" id="resetConfirmBtn">
                        <span class="spinner-border spinner-border-sm me-1 d-none" id="resetSpinner" role="status"
                            aria-hidden="true"></span>
                        <span id="resetConfirmLabel">Réinitialiser</span>
                    </button>
                </div>

                <div class="modal-footer d-none" id="resetFooterDone">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Fermer</button>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal de confirmation : activer / désactiver un compte (contenu rempli en JS) --}}
    <div class="modal fade" id="toggleActifModal" tabindex="-1" aria-labelledby="toggleActifTitle"
        aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">

                <div class="modal-header text-white" id="toggleActifHeader">
                    <h5 class="modal-title" id="toggleActifTitle">
                        <i id="toggleActifIcon" class="fas me-2"></i>
                        <span id="toggleActifTitleText"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Fermer"></button>
                </div>

                <div class="modal-body">
                    {{-- Erreur renvoyée par le serveur (le modal reste ouvert) --}}
                    <div id="toggleActifError" class="alert alert-danger d-none" role="alert"></div>

                    <p class="mb-2" id="toggleActifText"></p>

                    <div class="sed-identity">
                        <div class="fw-bold" id="toggleActifName"></div>
                        <div class="text-muted small" id="toggleActifEmail"></div>
                    </div>

                    <p class="small text-muted mt-3 mb-0" id="toggleActifNote"></p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        id="toggleActifCancel">Annuler</button>
                    <button type="button" class="btn" id="toggleActifConfirm">
                        <span class="spinner-border spinner-border-sm me-1 d-none" id="toggleActifSpinner"
                            role="status" aria-hidden="true"></span>
                        <span id="toggleActifConfirmLabel"></span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- ===================== CSS ===================== --}}
    @section('css')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
        <style>
            /* ---------- Tableau (thème sombre SED) ---------- */
             /* Titre de la page en blanc */
        .pagetitle h1,
        .page-title,
        .content-header h1,
        h1.page-title {
            color: #fff !important;
        }

        .sed-dash { font-variant-numeric: tabular-nums; --rouge: #f87171; --ambre: #f2b705; --vert: #4ade80; --bleu: #60a5fa; }
            #usersTable {
                --bs-table-color: #e6f1ff;
                --bs-table-bg: transparent;
                --bs-table-striped-bg: rgba(255, 255, 255, .03);
                --bs-table-hover-bg: rgba(0, 212, 255, .08);
                --bs-table-hover-color: #fff;
                --bs-table-border-color: rgba(0, 212, 255, .12);
            }

            .content-wrapper table.dataTable,
            .content-wrapper table.dataTable tbody tr,
            .content-wrapper table.dataTable tbody td {
                background-color: transparent;
                color: #e6f1ff;
            }

            .content-wrapper table.dataTable thead th {
                background: rgba(0, 212, 255, .10);
                color: #00d4ff;
                text-transform: uppercase;
                font-size: .72rem;
                letter-spacing: .08em;
                border-bottom: 1px solid rgba(0, 212, 255, .35);
            }

            .content-wrapper table.dataTable tbody tr:hover {
                background: rgba(0, 212, 255, .08);
            }

            .content-wrapper table.dataTable.no-footer {
                border-bottom: 1px solid rgba(0, 212, 255, .2);
            }

            /* ---------- Contrôles DataTables ---------- */
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter,
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_processing {
                color: #9bb6d6 !important;
            }

            .dataTables_wrapper .dataTables_processing {
                background: rgba(10, 25, 47, .95);
                border: 1px solid rgba(0, 212, 255, .3);
                border-radius: 10px;
            }

            .dataTables_wrapper .dataTables_filter input,
            .dataTables_wrapper .dataTables_length select {
                background: rgba(15, 31, 61, .8);
                border: 1px solid rgba(0, 212, 255, .25);
                border-radius: 8px;
                color: #e6f1ff;
                padding: 5px 10px;
            }

            .dataTables_wrapper .dataTables_filter input:focus {
                outline: none;
                border-color: #00d4ff;
                box-shadow: 0 0 0 3px rgba(0, 212, 255, .12);
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button {
                color: #cfe3ff !important;
                border: 1px solid transparent;
                border-radius: 8px;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
                background: rgba(0, 212, 255, .15) !important;
                border-color: rgba(0, 212, 255, .4) !important;
                color: #fff !important;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button.current,
            .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
                background: linear-gradient(135deg, #00d4ff, #007bff) !important;
                border-color: transparent !important;
                color: #fff !important;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
            .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
                color: #4a6a99 !important;
                background: transparent !important;
            }

            button.dt-button, div.dt-button, a.dt-button {
                background: rgba(15, 31, 61, .8) !important;
                border: 1px solid rgba(0, 212, 255, .3) !important;
                border-radius: 8px !important;
                color: #cfe3ff !important;
                box-shadow: none !important;
            }

            button.dt-button:hover:not(.disabled) {
                background: rgba(0, 212, 255, .15) !important;
                color: #fff !important;
            }

            div.dt-button-collection {
                background: #0a192f;
                border: 1px solid rgba(0, 212, 255, .3);
                border-radius: 10px;
            }

            /* ---------- Interrupteur de statut ---------- */
            .form-switch .form-check-input {
                background-color: rgba(122, 156, 192, .25);
                border-color: rgba(122, 156, 192, .5);
                cursor: pointer;
            }

            .form-switch .form-check-input:checked {
                background-color: #00d4ff;
                border-color: #00d4ff;
            }

            .form-switch .form-check-input:disabled {
                cursor: not-allowed;
                opacity: .5;
            }

            .form-check-input:focus {
                box-shadow: 0 0 0 3px rgba(0, 212, 255, .2);
            }

            /* ---------- Modales ---------- */
            .modal-content .btn-close:not(.btn-close-white) {
                filter: none;
            }

            #resetPasswordHeader.bg-warning .btn-close {
                filter: none;
            }

            .sed-identity {
                padding: 10px 14px;
                background: rgba(15, 31, 61, .8);
                border: 1px solid rgba(0, 212, 255, .25);
                border-radius: 10px;
            }

            .sed-code {
                background: rgba(15, 31, 61, .9);
                border: 1px solid rgba(0, 212, 255, .3);
                border-radius: 10px;
                color: #00e676;
            }

            .sed-filters .form-select {
                background-color: rgba(15, 31, 61, .8);
                border: 1px solid rgba(0, 212, 255, .2);
                color: #e6f1ff;
            }

            .sed-filters .form-select option {
                background: #0a192f;
                color: #e6f1ff;
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
        </style>
    @endsection

    {{-- ===================== JS ===================== --}}
    @section('js')
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        {{-- JSZip et pdfmake sont nécessaires aux boutons Excel et PDF (sans eux, ces boutons échouent) --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.colVis.min.js"></script>

        <script>
            // Identifiant de l'utilisateur connecté : il ne peut pas désactiver son propre compte
            const currentUserId = @json((string) auth()->id());
            const usersBaseUrl = @json(url('/admin/users'));
            const csrfToken = @json(csrf_token());

            /* ===================== TOAST ===================== */
            function showToast(message, type = 'success') {
                const icones = {
                    success: 'fa-check-circle',
                    error: 'fa-exclamation-circle',
                    info: 'fa-info-circle'
                };

                // .text() : le message n'est jamais interprété comme du HTML
                const $toast = $('<div class="sed-toast" role="alert"></div>')
                    .addClass(type)
                    .append($('<i class="fas me-2"></i>').addClass(icones[type] || icones.info))
                    .append($('<span></span>').text(message));

                $('#toastContainer').append($toast);
                $toast.fadeIn(200);

                setTimeout(() => $toast.fadeOut(300, function() {
                    $(this).remove();
                }), 4000);
            }

            $(function() {

                /* ===================== DATATABLE ===================== */
                // Les erreurs de chargement sont signalées par un toast plutôt que par une alerte du navigateur
                $.fn.dataTable.ext.errMode = 'none';

                const exportOptions = {
                    columns: ':visible:not(:last-child)'
                };

                let table = $('#usersTable').DataTable({
                    processing: true,
                    serverSide: true,
                    searchDelay: 400, // évite une requête à chaque frappe
                    scrollX: true,
                    pageLength: 10,
                    order: [
                        [5, 'desc']
                    ],
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
                    },
                    ajax: {
                        url: @json(route('admin.user.index')),
                        data: function(d) {
                            d.role = $('#filterRole').val();
                            d.statut = $('#filterStatut').val();
                        }
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'name'
                        },
                        {
                            data: 'email'
                        },
                        {
                            data: 'roles',
                            orderable: false,
                            searchable: false
                        },
                        {
                            // Statut : interrupteur + badge (nécessite `est_actif` dans le JSON du contrôleur)
                            data: 'est_actif',
                            name: 'est_actif',
                            searchable: false,
                            render: function(data, type, row) {
                                if (type !== 'display') return data;

                                const actif = (data === true || data === 1 || data === '1');
                                const estMoi = String(row.id) === String(currentUserId);

                                return `
                                    <div class="d-flex align-items-center">
                                        <div class="form-check form-switch mb-0 me-2">
                                            <input class="form-check-input toggleActifBtn" type="checkbox"
                                                role="switch" data-id="${row.id}"
                                                aria-label="${actif ? 'Désactiver' : 'Activer'} ce compte"
                                                ${actif ? 'checked' : ''}
                                                ${estMoi ? 'disabled title="Vous ne pouvez pas désactiver votre propre compte"' : ''}>
                                        </div>
                                        <span class="badge ${actif ? 'bg-success' : 'bg-danger'}">
                                            ${actif ? 'Actif' : 'Désactivé'}
                                        </span>
                                    </div>`;
                            }
                        },
                        {
                            data: 'created_at'
                        },
                        {
                            data: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    dom: '<"d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"Bf>rt<"d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3"lip>',
                    buttons: [{
                            extend: 'colvis',
                            text: '<i class="fas fa-columns me-1"></i> Colonnes',
                            columns: ':not(:last-child)'
                        },
                        {
                            extend: 'csv',
                            text: 'CSV',
                            exportOptions: exportOptions
                        },
                        {
                            extend: 'excel',
                            text: 'Excel',
                            exportOptions: exportOptions
                        },
                        {
                            extend: 'pdf',
                            text: 'PDF',
                            exportOptions: exportOptions
                        },
                        {
                            extend: 'print',
                            text: '<i class="fas fa-print me-1"></i> Imprimer',
                            exportOptions: exportOptions
                        }
                    ]
                });

                $('#usersTable').on('error.dt', function(e, settings, techNote, message) {
                    showToast('Impossible de charger la liste des utilisateurs. Réessayez ou actualisez la page.',
                        'error');
                    console.log('DataTables :', message);
                });

                /* ===================== FILTRES ===================== */
                $('#filterRole, #filterStatut').on('change', function() {
                    table.ajax.reload();
                });

                $('#btnResetFiltres').on('click', function() {
                    $('#filterRole, #filterStatut').val('');
                    table.search('').ajax.reload();
                });

                $('#btnRefresh').on('click', function() {
                    table.ajax.reload(null, false); // garde la page courante
                });

                /* ===================== MESSAGES D'ERREUR AJAX ===================== */
                // Message clair selon le code HTTP (aide au diagnostic)
                function describeError(xhr, route) {
                    route = route || 'toggle-actif';
                    const serveur = xhr.responseJSON && xhr.responseJSON.message;
                    switch (xhr.status) {
                        case 0:
                            return 'Impossible de joindre le serveur. Vérifiez votre connexion.';
                        case 403:
                            return serveur || 'Action non autorisée (403) : votre rôle ne permet pas cette opération.';
                        case 404:
                            return 'Route ou utilisateur introuvable (404) : vérifiez que la route « ' + route +
                                ' » est bien déclarée.';
                        case 405:
                            return 'Méthode non autorisée (405) : la route ' + route + ' doit accepter PATCH.';
                        case 419:
                            return 'Session expirée (419) : rechargez la page puis réessayez.';
                        case 422:
                            return serveur || 'Opération refusée (422).';
                        case 500:
                            return serveur ?
                                'Erreur serveur (500) : ' + serveur :
                                'Erreur serveur (500) : consultez storage/logs/laravel.log.';
                        default:
                            return serveur || ('Erreur inattendue (' + xhr.status + ').');
                    }
                }

                /* ===================== ACTIVER / DÉSACTIVER (avec modal de confirmation) ===================== */
                const toggleModalEl = document.getElementById('toggleActifModal');
                const $toggleModal = $(toggleModalEl);
                let pendingToggle = null; // { id, activer }

                function setToggleLoading(loading) {
                    $('#toggleActifConfirm, #toggleActifCancel').prop('disabled', loading);
                    $('#toggleActifSpinner').toggleClass('d-none', !loading);
                    if (loading) {
                        $('#toggleActifConfirmLabel').text('Traitement…');
                    } else if (pendingToggle) {
                        $('#toggleActifConfirmLabel').text(pendingToggle.activer ? 'Activer le compte' :
                            'Désactiver le compte');
                    }
                }

                // 1) Clic sur l'interrupteur : on annule le changement visuel et on ouvre le modal.
                //    L'état ne change réellement qu'après confirmation (rechargement du tableau).
                $(document).on('change', '.toggleActifBtn', function() {
                    const activer = this.checked;
                    this.checked = !activer;

                    const row = table.row($(this).closest('tr')).data() || {};
                    pendingToggle = {
                        id: $(this).data('id'),
                        activer: activer
                    };

                    $('#toggleActifName').text(row.name || '—');
                    $('#toggleActifEmail').text(row.email || '');
                    $('#toggleActifError').addClass('d-none').text('');

                    $('#toggleActifHeader')
                        .removeClass('bg-danger bg-success')
                        .addClass(activer ? 'bg-success' : 'bg-danger');
                    $('#toggleActifIcon')
                        .removeClass('fa-user-slash fa-user-check')
                        .addClass(activer ? 'fa-user-check' : 'fa-user-slash');
                    $('#toggleActifConfirm')
                        .removeClass('btn-danger btn-success')
                        .addClass(activer ? 'btn-success' : 'btn-danger');

                    $('#toggleActifTitleText').text(activer ? 'Activer ce compte ?' : 'Désactiver ce compte ?');
                    $('#toggleActifText').text(activer ?
                        'Vous êtes sur le point de réactiver le compte suivant :' :
                        'Vous êtes sur le point de désactiver le compte suivant :');
                    $('#toggleActifNote').text(activer ?
                        "L'utilisateur pourra de nouveau se connecter." :
                        "L'utilisateur ne pourra plus se connecter et sera déconnecté dès sa prochaine action. Vous pourrez le réactiver à tout moment."
                    );

                    setToggleLoading(false);
                    $toggleModal.modal('show');
                });

                // 2) Confirmation : appel AJAX
                $('#toggleActifConfirm').on('click', function() {
                    if (!pendingToggle) return;

                    setToggleLoading(true);
                    $('#toggleActifError').addClass('d-none').text('');

                    $.ajax({
                        url: usersBaseUrl + '/' + pendingToggle.id + '/toggle-actif',
                        type: 'PATCH',
                        dataType: 'json',
                        data: {
                            _token: csrfToken
                        },
                        success: function(res) {
                            $toggleModal.modal('hide');
                            showToast(res.message || 'Statut mis à jour !', 'success');
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            // Le modal reste ouvert et affiche la cause de l'échec
                            $('#toggleActifError').text(describeError(xhr)).removeClass('d-none');
                            console.log(xhr);
                        },
                        complete: function() {
                            setToggleLoading(false);
                        }
                    });
                });

                // 3) Fermeture du modal : on oublie l'action en attente
                toggleModalEl.addEventListener('hidden.bs.modal', function() {
                    pendingToggle = null;
                });

                /* ===================== RÉINITIALISER LE MOT DE PASSE ===================== */
                const resetModalEl = document.getElementById('resetPasswordModal');
                const $resetModal = $(resetModalEl);
                let pendingReset = null; // { id }

                function showResetStep(step) {
                    const fait = (step === 'done');
                    $('#resetStepConfirm').toggleClass('d-none', fait);
                    $('#resetStepDone').toggleClass('d-none', !fait);
                    $('#resetFooterConfirm').toggleClass('d-none', fait);
                    $('#resetFooterDone').toggleClass('d-none', !fait);

                    $('#resetPasswordHeader')
                        .toggleClass('bg-warning text-dark', !fait)
                        .toggleClass('bg-success text-white', fait);
                    $('#resetCloseX').toggleClass('btn-close-white', fait);
                    $('#resetPasswordTitleText').text(fait ? 'Mot de passe réinitialisé' :
                        'Réinitialiser le mot de passe ?');
                }

                function setResetLoading(loading) {
                    $('#resetConfirmBtn, #resetCancelBtn').prop('disabled', loading);
                    $('#resetSpinner').toggleClass('d-none', !loading);
                    $('#resetConfirmLabel').text(loading ? 'Traitement…' : 'Réinitialiser');
                }

                // 1) Clic sur le bouton "clé" de la ligne : ouverture du modal de confirmation
                $(document).on('click', '.resetPasswordBtn', function() {
                    const row = table.row($(this).closest('tr')).data() || {};
                    pendingReset = {
                        id: $(this).data('id')
                    };

                    $('#resetName').text(row.name || '—');
                    $('#resetEmail').text(row.email || '');
                    $('#resetError').addClass('d-none').text('');

                    setResetLoading(false);
                    showResetStep('confirm');
                    $resetModal.modal('show');
                });

                // 2) Confirmation : appel AJAX
                $('#resetConfirmBtn').on('click', function() {
                    if (!pendingReset) return;

                    setResetLoading(true);
                    $('#resetError').addClass('d-none').text('');

                    $.ajax({
                        url: usersBaseUrl + '/' + pendingReset.id + '/reset-password',
                        type: 'PATCH',
                        dataType: 'json',
                        data: {
                            _token: csrfToken
                        },
                        success: function(res) {
                            $('#resetPwdValue').text(res.password || '');
                            showResetStep('done');
                        },
                        error: function(xhr) {
                            $('#resetError').text(describeError(xhr, 'reset-password')).removeClass('d-none');
                            console.log(xhr);
                        },
                        complete: function() {
                            setResetLoading(false);
                        }
                    });
                });

                // 3) Copier le mot de passe
                $('#resetCopyBtn').on('click', function() {
                    const texte = $('#resetPwdValue').text();
                    const $btn = $(this);
                    const ok = function() {
                        $btn.html('<i class="fas fa-check me-1"></i> Copié');
                        setTimeout(function() {
                            $btn.html('<i class="fas fa-copy me-1"></i> Copier');
                        }, 2000);
                    };

                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(texte).then(ok);
                    } else {
                        // Repli (page en http)
                        const ta = document.createElement('textarea');
                        ta.value = texte;
                        document.body.appendChild(ta);
                        ta.select();
                        try {
                            document.execCommand('copy');
                            ok();
                        } catch (e) {}
                        document.body.removeChild(ta);
                    }
                });

                // 4) Fermeture : on ne garde ni l'action en attente ni le mot de passe affiché
                resetModalEl.addEventListener('hidden.bs.modal', function() {
                    pendingReset = null;
                    $('#resetPwdValue').text('');
                });

                /* ===================== SUPPRIMER UN UTILISATEUR ===================== */
                $(document).on('click', '.deleteUserBtn', function() {
                    $('#deleteUserId').val($(this).data('id'));
                    $('#deleteUserModal').modal('show');
                });

                $('#deleteUserForm').on('submit', function(e) {
                    e.preventDefault();
                    const id = $('#deleteUserId').val();

                    $.ajax({
                        url: usersBaseUrl + '/' + id,
                        type: 'DELETE',
                        data: {
                            _token: csrfToken
                        },
                        success: function() {
                            $('#deleteUserModal').modal('hide');
                            table.ajax.reload(null, false);
                            showToast('Utilisateur supprimé avec succès !', 'success');
                        },
                        error: function(err) {
                            showToast(
                                (err.responseJSON && err.responseJSON.message) ||
                                'Erreur lors de la suppression !',
                                'error'
                            );
                            console.log(err);
                        }
                    });
                });

            });
        </script>
    @endsection
</x-admin>