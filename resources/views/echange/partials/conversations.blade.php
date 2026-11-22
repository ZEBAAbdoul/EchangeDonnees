{{-- Module : Messages (conversations chiffrées de bout en bout) --}}
<section id="vue-conversations" class="echange-vue d-none">

    <div id="bandeau-verrou" class="alert alert-warning d-none" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-lock"></i>
            <div>
                Trousseau verrouillé :
                <a href="#/trousseau" class="alert-link">déverrouillez-le</a>
                pour lire et envoyer des messages.
            </div>
        </div>
    </div>

    <div class="row g-3">

        {{-- ===== Liste des conversations ===== --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-comments me-1"></i> Conversations</span>
                    <div class="d-flex gap-1">
                        <button type="button" id="btn-actualiser-conv" class="btn btn-sm btn-outline-info"
                            title="Actualiser">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button type="button" id="btn-nouvelle-conv" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i> Nouvelle
                        </button>
                    </div>
                </div>

                {{-- Panneau de création --}}
                <div id="panneau-nouvelle-conv" class="card-body border-bottom d-none">
                    <label for="nc-titre" class="form-label fw-bold mb-1">Titre (facultatif)</label>
                    <input type="text" id="nc-titre" class="form-control form-control-sm mb-2" maxlength="150"
                        placeholder="Ex. : Coordination frontière">

                    <label for="nc-recherche" class="form-label fw-bold mb-1">Participants</label>
                    <input type="search" id="nc-recherche" class="form-control form-control-sm mb-2"
                        placeholder="Rechercher un agent, un organisme, un pays…">

                    <div id="nc-agents" class="sed-nc-agents mb-2"></div>

                    <button type="button" id="nc-creer" class="btn btn-sm btn-success w-100">
                        <i class="fas fa-check me-1"></i> Créer la conversation
                    </button>
                </div>

                <div id="liste-conversations" class="sed-conv-liste"></div>
            </div>
        </div>

        {{-- ===== Fenêtre de discussion ===== --}}
        <div class="col-lg-8">
            <div class="card h-100">

                <div id="conv-vide" class="card-body text-center text-muted py-5">
                    <i class="fas fa-lock fa-2x mb-3 d-block"></i>
                    Sélectionnez une conversation ou créez-en une.<br>
                    <small>Les messages sont chiffrés dans votre navigateur : le serveur ne peut pas les lire.</small>
                </div>

                <div id="conv-fenetre" class="d-none">
                    <div class="sed-fenetre">
                        <div class="sed-fenetre-entete">
                            <div class="fw-bold" id="conv-titre"></div>
                            <small class="text-muted" id="conv-membres"></small>
                        </div>

                        <div id="conv-messages" class="sed-messages"></div>

                        <div id="zone-saisie" class="sed-saisie d-none">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label for="msg-niveau" class="form-label small mb-1">Classification</label>
                                    <select id="msg-niveau" class="form-select form-select-sm">
                                        <option value="1">Public</option>
                                        <option value="2" selected>Restreint</option>
                                        <option value="3">Confidentiel</option>
                                        <option value="4">Secret</option>
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <textarea id="msg-texte" class="form-control" rows="2"
                                        placeholder="Votre message… (Ctrl + Entrée pour envoyer)"></textarea>
                                </div>
                                <div class="col-md-2 d-grid">
                                    <button type="button" id="btn-envoyer" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-1"></i> Envoyer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>