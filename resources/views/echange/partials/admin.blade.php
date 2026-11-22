{{-- Module : Administration (agents, organismes, audit). Inclus uniquement pour les administrateurs. --}}
<section id="vue-admin" class="echange-vue d-none">

    <ul class="nav nav-tabs mb-3" id="adm-onglets">
        <li class="nav-item">
            <button type="button" class="nav-link active" data-adm="agents">
                <i class="fas fa-users me-1"></i> Agents
            </button>
        </li>
        <li class="nav-item">
            <button type="button" class="nav-link" data-adm="organismes">
                <i class="fas fa-building me-1"></i> Organismes
            </button>
        </li>
        <li class="nav-item">
            <button type="button" class="nav-link" data-adm="audit">
                <i class="fas fa-clipboard-list me-1"></i> Journal d'audit
            </button>
        </li>
    </ul>

    {{-- ===================== AGENTS ===================== --}}
    <div id="adm-agents">

        <div class="card mb-3 d-none" id="adm-form-agent">
            <div class="card-header" id="adm-form-agent-titre">Nouvel agent</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-4" id="adm-groupe-user">
                        <label for="adm-user" class="form-label fw-bold mb-1">Compte utilisateur</label>
                        <select id="adm-user" class="form-select form-select-sm"></select>
                    </div>
                    <div class="col-md-3">
                        <label for="adm-organisme" class="form-label fw-bold mb-1">Organisme</label>
                        <select id="adm-organisme" class="form-select form-select-sm"></select>
                    </div>
                    <div class="col-md-2">
                        <label for="adm-habilitation" class="form-label fw-bold mb-1">Habilitation</label>
                        <select id="adm-habilitation" class="form-select form-select-sm">
                            <option value="1">1 · Public</option>
                            <option value="2" selected>2 · Restreint</option>
                            <option value="3">3 · Confidentiel</option>
                            <option value="4">4 · Secret</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="adm-fonction" class="form-label fw-bold mb-1">Fonction</label>
                        <input type="text" id="adm-fonction" class="form-control form-control-sm" maxlength="150">
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="adm-est-admin">
                            <label class="form-check-label" for="adm-est-admin">
                                Administrateur du module d'échanges (gère les agents, les organismes et consulte l'audit)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="button" id="btn-adm-enregistrer-agent" class="btn btn-primary btn-sm">
                        <i class="fas fa-check me-1"></i> Enregistrer
                    </button>
                    <button type="button" id="btn-adm-annuler-agent" class="btn btn-secondary btn-sm">Annuler</button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span><i class="fas fa-users me-1"></i> Agents</span>
                <div class="d-flex gap-2">
                    <input type="search" id="adm-recherche-agent" class="form-control form-control-sm"
                        placeholder="Rechercher…" style="min-width: 200px">
                    <button type="button" id="btn-adm-actualiser-agents" class="btn btn-sm btn-outline-info"
                        title="Actualiser"><i class="fas fa-sync-alt"></i></button>
                    <button type="button" id="btn-adm-nouvel-agent" class="btn btn-sm btn-primary">
                        <i class="fas fa-user-plus me-1"></i> Nouvel agent
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-adm-agents" class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Agent</th>
                                <th>Organisme</th>
                                <th>Habilitation</th>
                                <th>Droits</th>
                                <th>Statut</th>
                                <th title="Clé de chiffrement publiée">Clé</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== ORGANISMES ===================== --}}
    <div id="adm-organismes" class="d-none">

        <div class="card mb-3 d-none" id="adm-form-org">
            <div class="card-header" id="adm-form-org-titre">Nouvel organisme</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label for="adm-org-etat" class="form-label fw-bold mb-1">État</label>
                        <select id="adm-org-etat" class="form-select form-select-sm"></select>
                    </div>
                    <div class="col-md-3">
                        <label for="adm-org-sigle" class="form-label fw-bold mb-1">Sigle</label>
                        <input type="text" id="adm-org-sigle" class="form-control form-control-sm" maxlength="30">
                    </div>
                    <div class="col-md-6">
                        <label for="adm-org-nom" class="form-label fw-bold mb-1">Nom complet</label>
                        <input type="text" id="adm-org-nom" class="form-control form-control-sm" maxlength="255">
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="button" id="btn-adm-enregistrer-org" class="btn btn-primary btn-sm">
                        <i class="fas fa-check me-1"></i> Enregistrer
                    </button>
                    <button type="button" id="btn-adm-annuler-org" class="btn btn-secondary btn-sm">Annuler</button>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-building me-1"></i> Organismes</span>
                <div class="d-flex gap-2">
                    <button type="button" id="btn-adm-actualiser-org" class="btn btn-sm btn-outline-info"
                        title="Actualiser"><i class="fas fa-sync-alt"></i></button>
                    <button type="button" id="btn-adm-nouvel-org" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> Nouvel organisme
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-adm-organismes" class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>État</th>
                                <th>Sigle</th>
                                <th>Nom</th>
                                <th>Agents</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== AUDIT ===================== --}}
    <div id="adm-audit" class="d-none">
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span><i class="fas fa-clipboard-list me-1"></i> Journal d'audit (200 dernières actions)</span>
                <div class="d-flex gap-2">
                    <input type="search" id="adm-filtre-audit" class="form-control form-control-sm"
                        placeholder="Filtrer par action (ex. message, admin)" style="min-width: 260px">
                    <button type="button" id="btn-adm-actualiser-audit" class="btn btn-sm btn-outline-info"
                        title="Actualiser"><i class="fas fa-sync-alt"></i></button>
                </div>
            </div>
            <div class="card-body">
                <p class="small text-muted">
                    Le journal enregistre qui a fait quoi et quand. Il ne contient jamais le contenu des messages,
                    des documents ou des échanges.
                </p>
                <div class="table-responsive">
                    <table id="table-adm-audit" class="table table-sm table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Agent</th>
                                <th>Action</th>
                                <th>Cible</th>
                                <th>Adresse IP</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</section>