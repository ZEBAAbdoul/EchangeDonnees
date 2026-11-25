{{-- Module : Données (échanges structurés chiffrés de bout en bout) --}}
<section id="vue-echanges" class="echange-vue d-none">

    <div id="bandeau-verrou-don" class="alert alert-warning d-none" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="fas fa-lock"></i>
            <div>
                Trousseau verrouillé :
                <a href="#/trousseau" class="alert-link">déverrouillez-le</a>
                pour lire ou envoyer des données.
            </div>
        </div>
    </div>

    <div id="zone-donnees" class="d-none">
        <div class="row g-3">

            {{-- ===== Liste ===== --}}
            <div class="col-xl-7">
                <div class="card h-100">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <span><i class="fas fa-database me-1"></i> Échanges de données</span>
                        <div class="d-flex gap-2">
                            <button type="button" id="btn-actualiser-don" class="btn btn-sm btn-outline-info"
                                title="Actualiser">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button type="button" id="btn-nouveau-don" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i> Nouvel échange
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-6">
                                <div class="btn-group w-100" role="group" aria-label="Sens">
                                    <button type="button" id="don-sens-recus" class="btn btn-sm btn-outline-info active">
                                        Reçus <span id="don-nb-recus" class="badge bg-danger ms-1 d-none">0</span>
                                    </button>
                                    <button type="button" id="don-sens-envoyes" class="btn btn-sm btn-outline-info">
                                        Envoyés
                                    </button>
                                    <button type="button" id="don-sens-valider" class="btn btn-sm btn-outline-info">
                                        À valider <span id="don-nb-valider" class="badge bg-danger ms-1 d-none">0</span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <select id="don-filtre-type" class="form-select form-select-sm" aria-label="Type">
                                    <option value="">Tous les types</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <select id="don-filtre-statut" class="form-select form-select-sm" aria-label="Statut">
                                    <option value="">Tous les statuts</option>
                                    <option value="nouveau">Nouveau</option>
                                    <option value="lu">Lu</option>
                                    <option value="traite">Traité</option>
                                    <option value="rejete">Rejeté</option>
                                </select>
                            </div>
                        </div>

                        {{-- Recherche : faite dans le navigateur sur les échanges déchiffrés (rien n'est envoyé au serveur) --}}
                        <div class="row g-2 mb-3">
                            <div class="col-12">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
                                    <input type="search" id="don-q" class="form-control"
                                        placeholder="Rechercher : nom, plaque, lieu, objet, description…"
                                        autocomplete="off" spellcheck="false" aria-label="Rechercher dans les échanges">
                                    <button type="button" id="don-q-raz" class="btn btn-outline-secondary"
                                        title="Effacer la recherche" aria-label="Effacer la recherche">
                                        <i class="fas fa-xmark"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <select id="don-f-priorite" class="form-select form-select-sm" aria-label="Priorité">
                                    <option value="">Toutes priorités</option>
                                    <option value="normale">Normale</option>
                                    <option value="haute">Haute</option>
                                    <option value="urgente">Urgente</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <select id="don-f-niveau" class="form-select form-select-sm" aria-label="Classification">
                                    <option value="">Toutes classifications</option>
                                    <option value="1">Public</option>
                                    <option value="2">Restreint</option>
                                    <option value="3">Confidentiel</option>
                                    <option value="4">Secret</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <input type="date" id="don-f-du" class="form-control form-control-sm" aria-label="Du">
                            </div>
                            <div class="col-6 col-md-3">
                                <input type="date" id="don-f-au" class="form-control form-control-sm" aria-label="Au">
                            </div>
                            <div class="col-12 small text-muted" id="don-q-compteur" aria-live="polite"></div>
                        </div>

                        <div class="table-responsive">
                            <table id="table-donnees" class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Objet</th>
                                        <th>Priorité</th>
                                        <th>Classification</th>
                                        <th id="don-col-partie">De</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== Détail / formulaire ===== --}}
            <div class="col-xl-5">

                {{-- Détail d'un échange --}}
                <div class="card h-100" id="don-detail">
                    <div class="card-header"><i class="fas fa-file-lines me-1"></i> Détail</div>
                    <div class="card-body">
                        <p id="don-detail-vide" class="text-muted text-center my-4">
                            Sélectionnez un échange dans la liste.
                        </p>

                        <div id="don-detail-contenu" class="sed-fiche d-none">
                            {{-- Photo : flottante en haut à droite, le texte l'entoure (aucun espace vide) --}}
                            <div id="don-detail-photo-zone" class="sed-fiche-photo d-none">
                                <div class="sed-fiche-photo-cadre">
                                    <img id="don-detail-photo" alt="Photo jointe" class="sed-photo d-none"
                                        title="Cliquer pour agrandir">
                                    <div id="don-detail-photo-etat" class="small text-muted"></div>
                                </div>
                                <button type="button" id="btn-photo-telecharger"
                                    class="btn btn-sm btn-outline-info w-100 mt-2 d-none">
                                    <i class="fas fa-download me-1"></i> Télécharger
                                </button>
                            </div>

                            <div id="don-detail-badges" class="d-flex flex-wrap gap-2 mb-2"></div>
                            <h5 id="don-detail-objet" class="sed-fiche-titre"></h5>
                            <div id="don-detail-meta" class="small text-muted mb-3"></div>

                            <dl id="don-detail-champs" class="sed-champs"></dl>

                            {{-- Validation interétatique : décisions signées, approbation / refus, relance, annulation --}}
                            <div id="don-detail-validation" class="sed-validation border rounded p-2 mb-3 d-none"></div>

                            <div id="don-detail-actions" class="d-flex flex-wrap gap-2 mb-3"></div>

                            <div id="don-detail-suivi-zone" class="d-none">
                                <div class="fw-bold mb-2">Suivi par les destinataires</div>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0" id="don-detail-suivi">
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Nouvel échange --}}
                <div class="card h-100 d-none" id="don-formulaire">
                    <div class="card-header"><i class="fas fa-pen me-1"></i> Nouvel échange</div>
                    <div class="card-body">
                        <label for="don-type" class="form-label fw-bold mb-1">Type</label>
                        <select id="don-type" class="form-select form-select-sm mb-3"></select>

                        <div id="don-champs"></div>

                        {{-- Photo facultative (types personne et véhicule) --}}
                        <div id="don-zone-photo" class="mb-3 d-none">
                            <label for="don-photo" id="don-photo-libelle" class="form-label fw-bold mb-1">Photo (facultatif)</label>
                            <input type="file" id="don-photo" class="form-control form-control-sm"
                                accept="image/jpeg,image/png,image/webp">
                            <div class="small text-muted mt-1">
                                JPEG, PNG ou WebP. La photo est réduite et ses métadonnées (position GPS, appareil,
                                date de prise de vue) sont supprimées avant le chiffrement.
                            </div>
                            <div id="don-photo-apercu" class="mt-2 d-none">
                                <img id="don-photo-img" alt="Aperçu de la photo" class="sed-photo-apercu">
                                <div>
                                    <button type="button" id="btn-retirer-photo" class="btn btn-sm btn-outline-secondary mt-1">
                                        <i class="fas fa-xmark me-1"></i> Retirer la photo
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="don-niveau" class="form-label fw-bold mb-1">Classification</label>
                                <select id="don-niveau" class="form-select form-select-sm">
                                    <option value="1">Public</option>
                                    <option value="2" selected>Restreint</option>
                                    <option value="3">Confidentiel</option>
                                    <option value="4">Secret</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="don-priorite" class="form-label fw-bold mb-1">Priorité</label>
                                <select id="don-priorite" class="form-select form-select-sm">
                                    <option value="normale" selected>Normale</option>
                                    <option value="haute">Haute</option>
                                    <option value="urgente">Urgente</option>
                                </select>
                            </div>
                        </div>

                        <label for="don-recherche" class="form-label fw-bold mb-1">Destinataires</label>
                        <input type="search" id="don-recherche" class="form-control form-control-sm mb-2"
                            placeholder="Rechercher un agent, un organisme, un pays…">
                        <div id="don-agents" class="sed-nc-agents mb-2"></div>
                        <div class="small text-muted mb-3">
                            Vous gardez toujours une copie. Seuls les agents dont l'habilitation couvre la
                            classification choisie peuvent recevoir l'échange. Un envoi vers un autre État
                            doit d'abord être approuvé par les administrateurs de votre État.
                        </div>

                        {{-- Aperçu : qui reçoit tout de suite, qui doit valider --}}
                        <div id="don-apercu-envoi" class="mb-3" aria-live="polite"></div>

                        <div class="d-flex gap-2">
                            <button type="button" id="btn-envoyer-don" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-paper-plane me-1"></i> Chiffrer et envoyer
                            </button>
                            <button type="button" id="btn-annuler-don" class="btn btn-secondary">Annuler</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>