<section id="section-echanges" class="echange-section cache">
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0">Échanges de données reçus</h3></div>
                <div class="card-body table-responsive p-0">
                    <table id="liste-echanges" class="table table-hover mb-0">
                        <thead><tr><th>Type</th><th>De</th><th>Statut</th><th>Date</th><th></th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <form id="form-echange" class="card">
                <div class="card-header"><h3 class="card-title mb-0">Envoyer des données</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Type de données</label>
                        <input type="text" name="type_schema" class="form-control" placeholder="ex. alerte.v1" pattern="[A-Za-z0-9_.\-]+" required>
                    </div>
                    <div class="form-group">
                        <label>Niveau de classification</label>
                        <select name="classification" class="form-control">
                            <option value="confidentiel">Confidentiel</option>
                            <option value="secret">Secret</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Contenu (JSON)</label>
                        <textarea name="charge" rows="6" class="form-control mono" placeholder='{"reference": "…"}' required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Destinataires</label>
                        <div id="choix-ech" class="liste-choix"></div>
                    </div>
                </div>
                <div class="card-footer"><button type="submit" class="btn btn-primary"><i class="fas fa-lock mr-1"></i> Chiffrer et envoyer</button></div>
            </form>
        </div>
    </div>

    <div id="detail-echange" class="card cache"></div>
</section>