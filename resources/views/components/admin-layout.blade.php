<section id="section-admin" class="echange-section cache">
    <div class="row">
        <div class="col-lg-6">
            <form id="form-agent" class="card">
                <div class="card-header"><h3 class="card-title mb-0">Créer un agent</h3></div>
                <div class="card-body">
                    <div class="form-group"><label>Nom</label><input type="text" name="nom" class="form-control" required></div>
                    <div class="form-group"><label>Fonction</label><input type="text" name="fonction" class="form-control" required></div>
                    <div class="form-group"><label>Structure (ministère, service)</label><input type="text" name="structure" class="form-control"></div>
                    <div class="form-group">
                        <label>Niveau d'habilitation</label>
                        <select name="niveau_habilitation" class="form-control">
                            <option value="confidentiel">Confidentiel</option>
                            <option value="secret">Secret</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Fin d'habilitation</label><input type="date" name="habilitation_expire_le" class="form-control"></div>
                    <p class="aide mb-0">Rattachez ensuite cet agent à son compte utilisateur (colonne <code>user_id</code>).</p>
                </div>
                <div class="card-footer"><button type="submit" class="btn btn-primary">Créer l'agent</button></div>
            </form>
        </div>

        <div class="col-lg-6">
            <form id="form-cle" class="card">
                <div class="card-header"><h3 class="card-title mb-0">Enregistrer une clé publique</h3></div>
                <div class="card-body">
                    <p class="aide">À faire uniquement après avoir comparé l'empreinte avec l'agent, en personne ou par un canal sûr.</p>
                    <div class="form-group"><label>Agent</label><select name="agent" id="select-agent-cle" class="form-control"></select></div>
                    <div class="form-group">
                        <label>Usage</label>
                        <select name="usage" class="form-control">
                            <option value="chiffrement">Chiffrement</option>
                            <option value="signature">Signature</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Clé publique (base64)</label><textarea name="cle_publique" rows="2" class="form-control mono" required></textarea></div>
                    <div class="form-group"><label>Expiration</label><input type="date" name="expire_le" class="form-control"></div>
                </div>
                <div class="card-footer"><button type="submit" class="btn btn-primary">Enregistrer la clé</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title mb-0">Agents</h3></div>
        <div class="card-body table-responsive p-0">
            <table id="liste-agents" class="table table-hover mb-0">
                <thead><tr><th>Nom</th><th>Fonction</th><th>État</th><th>Clés</th><th></th></tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title mb-0">Journal d'audit</h3></div>
        <div class="card-body">
            <p><button type="button" id="btn-verifier" class="btn btn-outline-primary">Vérifier l'intégrité de la chaîne</button></p>
            <div id="resultat-audit" class="cache"></div>
            <div class="table-responsive">
                <table id="liste-audit" class="table table-sm table-hover">
                    <thead><tr><th>#</th><th>Date</th><th>Acteur</th><th>Action</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>