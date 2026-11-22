<section id="section-conversations" class="echange-section">
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header"><h3 class="card-title mb-0">Mes conversations</h3></div>
                <div class="card-body table-responsive p-0">
                    <table id="liste-conversations" class="table table-hover mb-0">
                        <thead><tr><th>Créée par</th><th>Niveau</th><th>Dernière activité</th><th></th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <form id="form-conversation" class="card">
                <div class="card-header"><h3 class="card-title mb-0">Nouvelle conversation</h3></div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Niveau de classification</label>
                        <select name="classification" class="form-control">
                            <option value="confidentiel">Confidentiel</option>
                            <option value="secret">Secret</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Participants</label>
                        <div id="choix-conv" class="liste-choix"></div>
                    </div>
                </div>
                <div class="card-footer"><button type="submit" class="btn btn-primary">Créer la conversation</button></div>
            </form>
        </div>
    </div>

    <div id="fil" class="card cache">
        <div class="card-header"><h3 id="titre-fil" class="card-title mb-0"></h3></div>
        <div class="card-body">
            <div id="messages" class="messages"></div>
            <form id="form-message">
                <div class="form-group">
                    <textarea name="texte" rows="3" class="form-control" placeholder="Votre message (chiffré avant envoi)" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-lock mr-1"></i> Chiffrer et envoyer</button>
            </form>
        </div>
    </div>
</section>