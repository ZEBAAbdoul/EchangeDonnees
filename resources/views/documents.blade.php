<section id="section-documents" class="echange-section cache">
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0">Documents reçus</h3></div>
                <div class="card-body table-responsive p-0">
                    <table id="liste-documents" class="table table-hover mb-0">
                        <thead><tr><th>De</th><th>Niveau</th><th>Taille</th><th>Date</th><th></th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <form id="form-document" class="card">
                <div class="card-header"><h3 class="card-title mb-0">Envoyer un document</h3></div>
                <div class="card-body">
                    <p class="aide">Le fichier et son nom sont chiffrés dans votre navigateur avant l'envoi (50 Mo maximum).</p>
                    <div class="form-group">
                        <label>Fichier</label>
                        <input type="file" name="fichier" class="form-control-file" required>
                    </div>
                    <div class="form-group">
                        <label>Niveau de classification</label>
                        <select name="classification" class="form-control">
                            <option value="confidentiel">Confidentiel</option>
                            <option value="secret">Secret</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Destinataires</label>
                        <div id="choix-doc" class="liste-choix"></div>
                    </div>
                </div>
                <div class="card-footer"><button type="submit" class="btn btn-primary"><i class="fas fa-lock mr-1"></i> Chiffrer et envoyer</button></div>
            </form>
        </div>
    </div>
</section>