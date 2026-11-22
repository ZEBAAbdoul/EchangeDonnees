{{-- Module : Trousseau (clés de chiffrement stockées dans ce navigateur) --}}
<section id="vue-trousseau" class="echange-vue d-none">

    <div class="row g-3">

        <div class="col-lg-5">

            {{-- Création (première utilisation) --}}
            <div class="card mb-3" id="zone-creation">
                <div class="card-header"><i class="fas fa-key me-1"></i> Créer mon trousseau</div>
                <div class="card-body">
                    <p class="small text-muted">
                        Choisissez une phrase secrète d'au moins 12 caractères. Elle protège vos clés sur cet appareil.
                        <strong class="text-warning">Si vous la perdez, vos messages seront définitivement illisibles :
                            personne, pas même l'administrateur, ne peut la récupérer.</strong>
                    </p>
                    <label for="phrase-creation" class="form-label fw-bold mb-1">Phrase secrète</label>
                    <input type="password" id="phrase-creation" class="form-control mb-2" autocomplete="new-password"
                        minlength="12">
                    <label for="phrase-confirmation" class="form-label fw-bold mb-1">Confirmation</label>
                    <input type="password" id="phrase-confirmation" class="form-control mb-3"
                        autocomplete="new-password" minlength="12">
                    <button type="button" id="btn-creer-trousseau" class="btn btn-primary w-100">
                        <i class="fas fa-key me-1"></i> Créer mon trousseau
                    </button>
                </div>
            </div>

            {{-- Déverrouillage --}}
            <div class="card mb-3 d-none" id="zone-deverrouillage">
                <div class="card-header"><i class="fas fa-lock-open me-1"></i> Déverrouiller mon trousseau</div>
                <div class="card-body">
                    <label for="phrase-deverrouiller" class="form-label fw-bold mb-1">Phrase secrète</label>
                    <input type="password" id="phrase-deverrouiller" class="form-control mb-3"
                        autocomplete="current-password">
                    <button type="button" id="btn-deverrouiller" class="btn btn-primary w-100">
                        <i class="fas fa-lock-open me-1"></i> Déverrouiller
                    </button>
                    <div class="text-center mt-3">
                        <button type="button" id="btn-supprimer-trousseau" class="btn btn-link btn-sm text-danger p-0">
                            Phrase secrète perdue ? Supprimer le trousseau de cet appareil
                        </button>
                    </div>
                </div>
            </div>

            {{-- Mes clés / sauvegarde --}}
            <div class="card d-none" id="zone-cles">
                <div class="card-header"><i class="fas fa-fingerprint me-1"></i> Mon identité</div>
                <div class="card-body">
                    <div id="alerte-cle-revoquee" class="alert alert-danger d-none" role="alert">
                        <i class="fas fa-ban me-1"></i>
                        <strong>Clé révoquée.</strong> La clé de ce trousseau a été révoquée : plus personne ne peut
                        vous écrire avec. Supprimez ce trousseau puis créez-en un nouveau.
                        <div class="mt-2">
                            <button type="button" id="btn-supprimer-trousseau-rev" class="btn btn-sm btn-danger">
                                Supprimer ce trousseau et en créer un nouveau
                            </button>
                        </div>
                    </div>

                    <div id="alerte-sauvegarde" class="alert alert-warning d-none" role="alert">
                        <i class="fas fa-triangle-exclamation me-1"></i>
                        Exportez une sauvegarde maintenant : c'est le seul moyen de récupérer votre trousseau
                        si cet ordinateur est perdu.
                    </div>

                    <div class="small text-muted mb-1">Mon empreinte (à communiquer oralement pour vérification)</div>
                    <code id="mon-empreinte" class="sed-code sed-empreinte d-block px-3 py-2 mb-3">—</code>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" id="btn-exporter" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-download me-1"></i> Exporter une sauvegarde
                        </button>
                        <label class="btn btn-outline-secondary btn-sm mb-0" for="fichier-import">
                            <i class="fas fa-upload me-1"></i> Importer une sauvegarde
                        </label>
                        <input type="file" id="fichier-import" accept=".json,application/json" class="d-none">
                    </div>
                    <p class="small text-muted mt-3 mb-0">
                        La sauvegarde reste chiffrée par votre phrase secrète. Conservez-la sur un support
                        séparé (clé USB protégée), jamais par messagerie ordinaire.
                    </p>
                </div>
            </div>
        </div>

        {{-- Vérification des empreintes --}}
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header"><i class="fas fa-user-check me-1"></i> Vérification des identités</div>
                <div class="card-body">
                    <p class="small text-muted">
                        Chaque agent a une empreinte unique. Comparez-la <strong>de vive voix</strong> avec la personne
                        avant d'échanger des informations sensibles : c'est ce qui protège contre un serveur compromis.
                        Si une clé change, l'envoi vers cet agent est bloqué jusqu'à votre validation.
                    </p>
                    <div class="table-responsive">
                        <table id="table-empreintes" class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Agent</th>
                                    <th>Organisme</th>
                                    <th>Empreinte</th>
                                    <th>Statut</th>
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