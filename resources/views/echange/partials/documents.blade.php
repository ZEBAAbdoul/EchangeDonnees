{{-- Module : Documents sécurisés (fichiers chiffrés de bout en bout) --}}
<section id="vue-documents" class="echange-vue d-none">

    <div id="bandeau-verrou-doc" class="alert alert-warning d-none" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-lock"></i>
            <div>
                Trousseau verrouillé :
                <a href="#/trousseau" class="alert-link">déverrouillez-le</a>
                pour déposer ou lire des documents.
            </div>
        </div>
    </div>

    <div class="row g-3">

        {{-- ===== Dépôt ===== --}}
        <div class="col-lg-4 d-none" id="zone-depot">
            <div class="card h-100">
                <div class="card-header"><i class="fas fa-file-shield me-1"></i> Déposer un document</div>
                <div class="card-body">

                    <label for="doc-fichier" class="form-label fw-bold mb-1">Fichier</label>
                    <input type="file" id="doc-fichier" class="form-control form-control-sm mb-1">
                    <div class="small text-muted mb-3">
                        Taille maximale : <span id="doc-max">20 Mo</span>.
                        Le fichier est chiffré dans votre navigateur avant l'envoi : son nom et son contenu
                        restent illisibles pour le serveur.
                    </div>

                    <label for="doc-niveau" class="form-label fw-bold mb-1">Classification</label>
                    <select id="doc-niveau" class="form-select form-select-sm mb-3">
                        <option value="1">Public</option>
                        <option value="2" selected>Restreint</option>
                        <option value="3">Confidentiel</option>
                        <option value="4">Secret</option>
                    </select>

                    <label for="doc-recherche" class="form-label fw-bold mb-1">Destinataires</label>
                    <input type="search" id="doc-recherche" class="form-control form-control-sm mb-2"
                        placeholder="Rechercher un agent, un organisme, un pays…">
                    <div id="doc-agents" class="sed-nc-agents mb-3"></div>
                    <div class="small text-muted mb-3">
                        Vous recevez toujours une copie. Seuls les agents dont l'habilitation couvre la
                        classification choisie peuvent recevoir le document.
                    </div>

                    <button type="button" id="btn-deposer" class="btn btn-primary w-100">
                        <i class="fas fa-lock me-1"></i> Chiffrer et envoyer
                    </button>
                    <div id="doc-etat" class="small text-info mt-2"></div>
                </div>
            </div>
        </div>

        {{-- ===== Liste ===== --}}
        <div class="col-lg-8 d-none" id="zone-liste-docs">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-folder-open me-1"></i> Mes documents</span>
                    <button type="button" id="btn-actualiser-docs" class="btn btn-sm btn-outline-info"
                        title="Actualiser">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table-documents" class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Document</th>
                                    <th>Taille</th>
                                    <th>Classification</th>
                                    <th>Expéditeur</th>
                                    <th>Date</th>
                                    <th title="Signature de l'expéditeur">Sig.</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>