{{--
    Tableau de bord du SED (Système d'Échange d'Informations), poste de traitement.
    Variables (ProfileController::tableauAdmin) :
      $user, $agent, $agentActif, $stats, $graphique, $derniersEchanges, $activiteRecente,
      $fileTraitement, $repartitions
    Seuls des chiffres et des métadonnées sont affichés : le contenu des échanges est chiffré de bout en bout.
--}}
<x-admin>
    @section('title', ' Tableau de bord')

    @section('css')
        <style>
             /* Titre de la page en blanc */
        .pagetitle h1,
        .page-title,
        .content-header h1,
        h1.page-title {
            color: #fff !important;
        }

        .sed-dash { font-variant-numeric: tabular-nums; --rouge: #f87171; --ambre: #f2b705; --vert: #4ade80; --bleu: #60a5fa; }
            .sed-dash { font-variant-numeric: tabular-nums; --rouge: #f87171; --ambre: #f2b705; --vert: #4ade80; --bleu: #60a5fa; }
            .sed-dash .panel { background: rgba(10, 25, 47, .92); border: 1px solid rgba(0, 212, 255, .2); border-radius: 6px; margin-bottom: 1.25rem; }
            .sed-dash .panel-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .75rem 1rem; border-bottom: 1px solid rgba(0, 212, 255, .15); }
            .sed-dash .panel-head h2 { font-size: 1rem; font-weight: 600; margin: 0; color: #fff; }
            .sed-dash .panel-head a, .sed-dash .panel-head span { font-size: .85rem; }
            .sed-dash .panel-body { padding: 1rem; }
            .sed-dash .vide { padding: 2rem 1rem; text-align: center; color: #7a9cc0; margin: 0; }
            

            /* Bandeau d'habilitation : couleur selon le niveau de l'agent */
            .sed-dash .bandeau {
                display: flex; flex-wrap: wrap; gap: .5rem 2rem; align-items: center; justify-content: space-between;
                padding: .55rem 1rem; margin-bottom: 1rem; border-radius: 6px; font-size: .9rem;
                background: rgba(10, 25, 47, .92); border: 1px solid rgba(0, 212, 255, .2); border-left: 6px solid var(--niveau, #60a5fa);
            }
            .sed-dash .bandeau strong { color: #fff; }
            .sed-dash .bandeau .meta { display: flex; flex-wrap: wrap; gap: .25rem 1.5rem; color: #9bb6d6; }
            .sed-dash .bandeau button { background: none; border: 1px solid rgba(0, 212, 255, .3); color: #cfe3ff; border-radius: 4px; padding: .15rem .65rem; font-size: .85rem; }
            .sed-dash .bandeau button:hover { background: rgba(0, 212, 255, .12); color: #fff; }

            .sed-dash .identite h1 { font-size: 1.7rem; font-weight: 600; margin: 0 0 .75rem; color: #fff; }

            /* Situation */
            .sed-dash .situation { display: flex; flex-wrap: wrap; gap: .75rem 1.5rem; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; margin-bottom: 1.25rem; border-radius: 6px; background: rgba(10, 25, 47, .92); border: 1px solid rgba(0, 212, 255, .2); border-left: 6px solid #28c76f; }
            .sed-dash .situation.urgent { background: rgba(239, 68, 68, .1); border-color: rgba(239, 68, 68, .45); border-left-color: #ef4444; }
            .sed-dash .situation .titre { font-size: 1.3rem; font-weight: 600; color: #fff; margin: 0; }
            .sed-dash .situation .detail { margin: .2rem 0 0; color: #9bb6d6; }

            /* Registre */
            .sed-dash .registre { display: flex; flex-wrap: wrap; }
            .sed-dash .cellule { flex: 1 1 12rem; padding: 1rem 1.25rem; color: #e6f1ff; border-right: 1px solid rgba(0, 212, 255, .15); text-decoration: none; }
            .sed-dash .cellule:last-child { border-right: 0; }
            .sed-dash a.cellule:hover { background: rgba(0, 212, 255, .06); color: #fff; text-decoration: none; }
            .sed-dash .cellule .valeur { font-size: 2rem; font-weight: 600; line-height: 1.1; color: var(--tone, #cfe3ff); }
            .sed-dash .cellule .nom { margin-top: .25rem; font-weight: 600; }
            .sed-dash .cellule .precision { font-size: .85rem; color: #7a9cc0; }
            .sed-dash .t-danger { --tone: var(--rouge); } .sed-dash .t-warn { --tone: var(--ambre); }
            .sed-dash .t-ok { --tone: var(--vert); }      .sed-dash .t-blue { --tone: var(--bleu); }
            .sed-dash .t-violet { --tone: #a78bfa; }

            /* File de traitement */
            .sed-dash .file { list-style: none; margin: 0; padding: 0; }
            .sed-dash .file li { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem 1rem; padding: .75rem 1rem .75rem 1.1rem; border-bottom: 1px solid rgba(0, 212, 255, .1); border-left: 5px solid var(--rang, #60a5fa); }
            .sed-dash .file li:last-child { border-bottom: 0; }
            .sed-dash .file .corps { flex: 1 1 14rem; min-width: 0; }
            .sed-dash .file .type { font-weight: 600; color: #fff; }
            .sed-dash .file .sous { font-size: .85rem; color: #7a9cc0; }
            .sed-dash .file .age { font-size: .9rem; color: #cfe3ff; white-space: nowrap; }
            .sed-dash .rang-urgente { --rang: #ef4444; background: rgba(239, 68, 68, .06); }
            .sed-dash .rang-haute { --rang: #f2b705; }
            .sed-dash .rang-normale { --rang: #60a5fa; }

            /* Répartitions */
            .sed-dash .barre { display: flex; height: 10px; border-radius: 3px; overflow: hidden; background: rgba(255, 255, 255, .08); }
            .sed-dash .barre span { display: block; height: 100%; }
            .sed-dash .legende { list-style: none; margin: .6rem 0 0; padding: 0; font-size: .9rem; }
            .sed-dash .legende li { display: flex; justify-content: space-between; padding: .15rem 0; }
            .sed-dash .legende i { display: inline-block; width: .65rem; height: .65rem; border-radius: 2px; margin-right: .5rem; }
            .sed-dash .groupe + .groupe { margin-top: 1.25rem; }
            .sed-dash .groupe h3 { font-size: .9rem; font-weight: 600; margin: 0 0 .5rem; color: #cfe3ff; }

            .sed-dash .echanges th { font-weight: 600; font-size: .85rem; white-space: nowrap; }
            .sed-dash .echanges td { vertical-align: middle; }
            .sed-dash .echanges tr.nouveau td:first-child { box-shadow: inset 3px 0 0 var(--sed-cyan); font-weight: 600; }

            .sed-dash .journal { list-style: none; margin: 0; padding: 0; }
            .sed-dash .journal li { padding: .6rem 1rem .6rem 2rem; position: relative; border-bottom: 1px solid rgba(0, 212, 255, .1); }
            .sed-dash .journal li:last-child { border-bottom: 0; }
            .sed-dash .journal li::before { content: ""; position: absolute; left: 1rem; top: 1.05rem; width: 7px; height: 7px; border-radius: 50%; background: var(--sed-cyan); }
            .sed-dash .journal .qui { font-size: .85rem; color: #7a9cc0; }

            @media (max-width: 767px) { .sed-dash .cellule { border-right: 0; border-bottom: 1px solid rgba(0, 212, 255, .15); } }
        </style>
    @endsection

    @php
        $user = $user ?? auth()->user();
        $agent = $agent ?? null;
        $agentActif = (bool) ($agentActif ?? false);
        $stats = $stats ?? [];
        $graphique = $graphique ?? ['labels' => [], 'messages' => [], 'documents' => [], 'donnees' => []];
        $derniersEchanges = $derniersEchanges ?? collect();
        $activiteRecente = $activiteRecente ?? collect();
        $fileTraitement = $fileTraitement ?? collect();
        $repartitions = $repartitions ?? ['priorite' => [], 'niveau' => []];

        $s = fn (string $cle) => $stats[$cle] ?? 0;
        $pl = fn ($n, string $un, string $plusieurs) => $n > 1 ? $plusieurs : $un;
        $estAdmin = $agentActif && $agent && $agent->estAdmin();
        $prenom = $user->personne?->prenom ?: $user->email;
        $salutation = now()->hour < 18 ? 'Bonjour' : 'Bonsoir';

        $graphiqueVide = array_sum($graphique['messages'] ?? []) + array_sum($graphique['documents'] ?? [])
            + array_sum($graphique['donnees'] ?? []) === 0;

        $lien = fn (string $nom, array $params = []) => \Illuminate\Support\Facades\Route::has($nom) ? route($nom, $params) : '#';
        $page = fn (string $onglet = '') => $lien('admin.echange') === '#' ? '#' : $lien('admin.echange') . $onglet;

        $types = [
            'alerte' => 'Alerte sécuritaire', 'personne' => 'Personne recherchée', 'vehicule' => 'Véhicule recherché',
            'incident' => "Rapport d'incident", 'demande' => "Demande d'information",
        ];
        // niveau => [libellé, classe badge, couleur]
        $niveaux = [
            1 => ['Public', 'info', '#38bdf8'], 2 => ['Restreint', 'success', '#4ade80'],
            3 => ['Confidentiel', 'warning', '#f2b705'], 4 => ['Secret', 'danger', '#ef4444'],
        ];
        $priorites = [
            'urgente' => ['Urgente', 'danger', '#ef4444'], 'haute' => ['Haute', 'warning', '#f2b705'], 'normale' => ['Normale', 'secondary', '#60a5fa'],
        ];
        $statuts = ['nouveau' => ['Nouveau', 'primary'], 'lu' => ['Lu', 'secondary'], 'traite' => ['Traité', 'success'], 'rejete' => ['Rejeté', 'danger'], 'emis' => ['Envoyé', 'info']];
        $actionsAudit = [
            'page.ouverte' => 'Ouverture de la page', 'cle.enregistree' => 'Clé publique enregistrée',
            'conversation.creee' => 'Conversation créée', 'message.envoye' => 'Message envoyé',
            'document.depose' => 'Document déposé', 'document.telecharge' => 'Document téléchargé',
            'donnees.envoyees' => 'Données envoyées', 'donnees.lu' => 'Données lues',
            'donnees.traite' => 'Données traitées', 'donnees.rejete' => 'Données rejetées', 'donnees.photo' => 'Photo consultée',
            'admin.agent.cree' => 'Agent créé', 'admin.agent.modifie' => 'Agent modifié', 'admin.cle.revoquee' => 'Clé révoquée',
            'admin.organisme.cree' => 'Organisme créé', 'admin.organisme.modifie' => 'Organisme modifié',
            'admin.organisme.supprime' => 'Organisme supprimé',
        ];

        $niveauAgent = $agent ? ($niveaux[$agent->habilitation] ?? null) : null;
        $plusAncien = $s('plus_ancien') ? \Illuminate\Support\Carbon::parse($s('plus_ancien')) : null;
        $depuisPlusAncien = $plusAncien ? ', le plus ancien depuis ' . $plusAncien->diffForHumans(null, true) : '';

        $envois = $s('donnees_envoyees') + $s('documents_deposes');
        $registre = [
            ['t-' . ($s('donnees_urgentes') > 0 ? 'danger' : 'blue'), $s('donnees_a_traiter'), 'Données à traiter',
                $s('donnees_urgentes') . ' ' . $pl($s('donnees_urgentes'), 'urgente', 'urgentes'), $page('#/echanges')],
            ['t-blue', $s('conversations'), $pl($s('conversations'), 'Conversation', 'Conversations'),
                $s('messages_recus_7j') . ' ' . $pl($s('messages_recus_7j'), 'message reçu', 'messages reçus') . ' en 7 jours', $page('#/conversations')],
            ['t-ok', $s('documents_recus'), $pl($s('documents_recus'), 'Document reçu', 'Documents reçus'),
                $s('documents_a_telecharger') . ' à télécharger', $page('#/documents')],
            ['', $envois, $pl($envois, 'Envoi', 'Envois'),
                $s('donnees_envoyees') . ' données, ' . $s('documents_deposes') . ' ' . $pl($s('documents_deposes'), 'document', 'documents'), $page('#/echanges')],
        ];
        $registreAdmin = [
            ['t-violet', $s('agents_actifs'), 'Agents actifs', 'sur ' . $s('agents_total') . ' comptes agents', $page('#/admin')],
            ['t-' . ($s('agents_sans_cle') > 0 ? 'warn' : 'ok'), $s('agents_sans_cle'), $pl($s('agents_sans_cle'), 'Agent sans clé', 'Agents sans clé'),
                'ne peuvent pas recevoir de messages chiffrés', $page('#/admin')],
            ['t-blue', $s('organismes'), $pl($s('organismes'), 'Organisme', 'Organismes'), 'rattachés au système', $page('#/admin')],
            ['', $s('actions_24h'), 'Actions en 24 heures', 'consignées au journal d’audit', $page('#/admin')],
        ];

        // Répartitions : [titre, [clé => [libellé, couleur]], données]
        $groupes = [
            ['Par priorité', collect($priorites)->map(fn ($p) => [$p[0], $p[2]])->all(), $repartitions['priorite'] ?? []],
            ['Par classification', collect($niveaux)->map(fn ($n) => [$n[0], $n[2]])->all(), $repartitions['niveau'] ?? []],
        ];
    @endphp

    <div class="sed-dash">

        <h1 class="sr-only">Tableau de bord</h1>

        @if ($agentActif)
            {{-- ============ HABILITATION ET SESSION ============ --}}
            <div class="bandeau" style="--niveau: {{ $niveauAgent[2] ?? '#60a5fa' }}">
                <div class="meta">
                    <span>Habilitation <strong>{{ $niveauAgent[0] ?? $agent->habilitation }}</strong></span>
                    <span>Organisme <strong>{{ $agent->organisme?->sigle ?? '—' }}@if ($agent->organisme?->etat?->code) ({{ $agent->organisme->etat->code }})@endif</strong></span>
                    <span>Clé de chiffrement
                        @if ($agent->cleActive)
                            <strong style="color:var(--vert)">publiée le {{ $agent->cleActive->created_at?->format('d/m/Y') }}</strong>
                        @else
                            <strong style="color:var(--ambre)">non publiée</strong>
                        @endif
                    </span>
                </div>
                <div class="meta">
                    <span>Actualisé à {{ now()->format('H:i') }}</span>
                    <button type="button" onclick="location.reload()">Actualiser</button>
                </div>
            </div>
        @endif

        <header class="identite">
            <h1>{{ $salutation }}, {{ $prenom }}</h1>
        </header>

        @unless ($agentActif)
            <div class="panel">
                <div class="panel-body">
                    <h2 class="h5 text-white mb-2"><i class="fas fa-user-lock mr-2" aria-hidden="true"></i>Compte non rattaché à un agent</h2>
                    <p class="mb-0 text-muted">
                        Votre compte n’est pas encore rattaché à un agent du SED, ou il est désactivé.
                        Demandez à un administrateur de créer ou de réactiver votre profil d’agent pour accéder aux échanges sécurisés.
                    </p>
                </div>
            </div>
        @else

            {{-- ============ SITUATION ============ --}}
            @if ($s('donnees_urgentes') > 0)
                <section class="situation urgent" aria-live="polite">
                    <div>
                        <p class="titre">{{ $s('donnees_urgentes') }} {{ $pl($s('donnees_urgentes'), 'échange urgent attend', 'échanges urgents attendent') }} votre traitement</p>
                        <p class="detail">
                            {{ $s('donnees_a_traiter') }} {{ $pl($s('donnees_a_traiter'), 'échange en attente', 'échanges en attente') }} au total{{ $depuisPlusAncien }}.
                        </p>
                    </div>
                    <a href="{{ $page('#/echanges') }}" class="btn btn-danger">Traiter les urgences</a>
                </section>
            @else
                <section class="situation" aria-live="polite">
                    <div>
                        <p class="titre">Aucun échange urgent</p>
                        <p class="detail">
                            @if ($s('donnees_a_traiter') > 0)
                                {{ $s('donnees_a_traiter') }} {{ $pl($s('donnees_a_traiter'), 'échange reste', 'échanges restent') }} à traiter{{ $depuisPlusAncien }}.
                            @else
                                Tous les échanges reçus ont été traités.
                            @endif
                        </p>
                    </div>
                    <a href="{{ $page() }}" class="btn btn-primary">Ouvrir les échanges sécurisés</a>
                </section>
            @endif

            {{-- ============ REGISTRE ============ --}}
            <section class="panel" aria-label="Mon activité">
                <div class="registre">
                    @foreach ($registre as [$ton, $valeur, $nom, $precision, $href])
                        <a class="cellule {{ $ton }}" href="{{ $href }}">
                            <div class="valeur">{{ $valeur }}</div>
                            <div class="nom">{{ $nom }}</div>
                            <div class="precision">{{ $precision }}</div>
                        </a>
                    @endforeach
                </div>
            </section>

            @if ($estAdmin)
                <section class="panel" aria-label="Administration du système">
                    <div class="panel-head"><h2>Administration du système</h2><a href="{{ $page('#/admin') }}">Ouvrir l’administration</a></div>
                    <div class="registre">
                        @foreach ($registreAdmin as [$ton, $valeur, $nom, $precision, $href])
                            <a class="cellule {{ $ton }}" href="{{ $href }}">
                                <div class="valeur">{{ $valeur }}</div>
                                <div class="nom">{{ $nom }}</div>
                                <div class="precision">{{ $precision }}</div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <div class="row">
                {{-- ============ COLONNE PRINCIPALE ============ --}}
                <div class="col-lg-8">

                    <section class="panel">
                        <div class="panel-head">
                            <h2>File de traitement</h2>
                            <a href="{{ $page('#/echanges') }}">Voir tous les échanges</a>
                        </div>
                        <ul class="file">
                            @forelse ($fileTraitement as $e)
                                <li class="rang-{{ $e->priorite }}">
                                    <div class="corps">
                                        <div class="type">{{ $types[$e->type] ?? $e->type }}</div>
                                        <div class="sous">De {{ $e->expediteur?->nom ?? 'expéditeur inconnu' }}</div>
                                    </div>
                                    <span class="badge badge-{{ $priorites[$e->priorite][1] ?? 'secondary' }}">{{ $priorites[$e->priorite][0] ?? $e->priorite }}</span>
                                    <span class="badge badge-{{ $niveaux[$e->niveau][1] ?? 'secondary' }}">{{ $niveaux[$e->niveau][0] ?? 'Niveau ' . $e->niveau }}</span>
                                    <span class="age">depuis {{ $e->created_at?->diffForHumans(null, true) }}</span>
                                    <a href="{{ $page('#/echanges') }}" class="btn btn-sm btn-outline-primary">Ouvrir</a>
                                </li>
                            @empty
                                <li style="border-left-color:transparent"><p class="vide w-100">Rien à traiter. Les nouveaux échanges reçus apparaîtront ici, les plus urgents en premier.</p></li>
                            @endforelse
                        </ul>
                        <p class="small text-muted px-3 py-2 mb-0">
                            <i class="fas fa-lock mr-1" aria-hidden="true"></i>
                            L’objet et le contenu sont chiffrés. Ils ne s’affichent que dans les échanges sécurisés, trousseau déverrouillé.
                        </p>
                    </section>

                    <section class="panel">
                        <div class="panel-head"><h2>Activité sur six mois</h2></div>
                        <div class="panel-body">
                            @if ($graphiqueVide)
                                <p class="vide">Aucune activité sur les six derniers mois. Les messages, documents et échanges apparaîtront ici dès le premier envoi.</p>
                            @else
                                <div style="position:relative;height:280px;">
                                    <canvas id="graphActivite" role="img" aria-label="Messages, documents et échanges de données par mois"></canvas>
                                </div>
                            @endif
                        </div>
                    </section>

                    {{-- <section class="panel">
                        <div class="panel-head"><h2>Derniers échanges de données</h2><a href="{{ $page('#/echanges') }}">Tout voir</a></div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 echanges">
                                <thead>
                                    <tr><th>Type</th><th>Priorité</th><th>Classification</th><th>Expéditeur</th><th>Date</th><th>Statut</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($derniersEchanges as $e)
                                        @php
                                            $envoye = $e->expediteur_id === $agent->id;
                                            $statut = $e->destinataires->first()?->statut ?? 'emis';
                                        @endphp
                                        <tr class="{{ ! $envoye && $statut === 'nouveau' ? 'nouveau' : '' }}">
                                            <td class="text-nowrap">{{ $types[$e->type] ?? $e->type }}</td>
                                            <td><span class="badge badge-{{ $priorites[$e->priorite][1] ?? 'secondary' }}">{{ $priorites[$e->priorite][0] ?? $e->priorite }}</span></td>
                                            <td><span class="badge badge-{{ $niveaux[$e->niveau][1] ?? 'secondary' }}">{{ $niveaux[$e->niveau][0] ?? 'Niveau ' . $e->niveau }}</span></td>
                                            <td>{{ $envoye ? 'Moi' : ($e->expediteur?->nom ?? 'Inconnu') }}</td>
                                            <td class="text-nowrap text-muted">{{ $e->created_at?->format('d/m/Y H:i') }}</td>
                                            <td><span class="badge badge-{{ $statuts[$statut][1] ?? 'secondary' }}">{{ $statuts[$statut][0] ?? $statut }}</span></td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="vide">Aucun échange de données pour le moment.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section> --}}
                </div>

                {{-- ============ COLONNE DROITE ============ --}}
                <div class="col-lg-4">

                    @unless ($agent->cleActive)
                        <section class="panel" style="border-color:rgba(242,183,5,.55);">
                            <div class="panel-body">
                                <h2 class="h6 text-white mb-1">Créez votre trousseau</h2>
                                <p class="small text-muted mb-2">Personne ne peut vous écrire tant que votre clé de chiffrement n’est pas publiée.</p>
                                <a href="{{ $page('#/trousseau') }}" class="btn btn-warning btn-sm">Créer mon trousseau</a>
                            </div>
                        </section>
                    @endunless

                    <section class="panel">
                        <div class="panel-head"><h2>Échanges reçus</h2><span class="text-muted">{{ $s('donnees_recues') }} au total</span></div>
                        <div class="panel-body">
                            @foreach ($groupes as [$titre, $categories, $donnees])
                                @php $total = array_sum($donnees); @endphp
                                <div class="groupe">
                                    <h3>{{ $titre }}</h3>
                                    @if ($total === 0)
                                        <p class="text-muted small mb-0">Aucune donnée reçue.</p>
                                    @else
                                        <div class="barre" role="img" aria-label="{{ $titre }}">
                                            @foreach ($categories as $cle => [$libelle, $couleur])
                                                @if (($donnees[$cle] ?? 0) > 0)
                                                    <span style="width:{{ round($donnees[$cle] * 100 / $total, 1) }}%;background:{{ $couleur }}" title="{{ $libelle }} : {{ $donnees[$cle] }}" ></span>
                                                @endif
                                            @endforeach
                                        </div>
                                        <ul class="legende">
                                            @foreach ($categories as $cle => [$libelle, $couleur])
                                                @if (($donnees[$cle] ?? 0) > 0)
                                                    <li><span style="color: #fff"><i style="background:{{ $couleur }}"></i>{{ $libelle }}</span><strong style="color: #fff">{{ $donnees[$cle] }}</strong></li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="panel">
                        <div class="panel-head"><h2>Traitement</h2></div>
                        <div class="panel-body">
                            @if (($s('donnees_traitees') + $s('donnees_a_traiter')) === 0)
                                <p class="vide">Aucune donnée reçue pour le moment.</p>
                            @else
                                <div style="position:relative;height:170px;">
                                    <canvas id="graphTraitement" role="img" aria-label="Données traitées et à traiter"></canvas>
                                </div>
                                <ul class="legende">
                                    <li><span><i style="background:#28c76f"></i><span style="color: #fff">Traitées</span></span><strong style="color: #fff">{{ $s('donnees_traitees') }}</strong></li>
                                    <li><span><i style="background:#f2b705"></i><span style="color: #fff">À traiter</span></span><strong style="color: #fff">{{ $s('donnees_a_traiter') }}</strong></li>
                                </ul>
                            @endif
                        </div>
                    </section>

                    {{-- @if ($estAdmin)
                        <section class="panel">
                            <div class="panel-head"><h2>Journal d’audit</h2><a href="{{ $page('#/admin') }}">Consulter</a></div>
                            <ul class="journal">
                                @forelse ($activiteRecente as $j)
                                    <li>
                                        <div>{{ $actionsAudit[$j->action] ?? $j->action }}</div>
                                        <div class="qui">{{ $j->agent?->nom ?? 'Système' }}, {{ \Illuminate\Support\Carbon::parse($j->created_at)->diffForHumans() }}</div>
                                    </li>
                                @empty
                                    <li class="vide">Aucune action consignée.</li>
                                @endforelse
                            </ul>
                        </section>
                    @endif --}}
                </div>
            </div>
        @endunless
    </div>

    {{-- ============ GRAPHIQUES ============ --}}
    @if ($agentActif)
        <script>
            (function () {
                var activite = @json($graphique);
                var traitement = [{{ (int) $s('donnees_traitees') }}, {{ (int) $s('donnees_a_traiter') }}];
                var texte = '#9bb6d6', grille = 'rgba(0,212,255,.1)';

                function dessiner() {
                    var a = document.getElementById('graphActivite');
                    if (a && !a.dataset.pret) {
                        a.dataset.pret = '1';
                        new Chart(a.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: activite.labels,
                                datasets: [
                                    { label: 'Messages', data: activite.messages, backgroundColor: '#00d4ff', borderRadius: 3, minBarLength: 3 },
                                    { label: 'Documents', data: activite.documents, backgroundColor: '#28c76f', borderRadius: 3, minBarLength: 3 },
                                    { label: 'Données structurées', data: activite.donnees, backgroundColor: '#f2b705', borderRadius: 3, minBarLength: 3 }
                                ]
                            },
                            options: {
                                responsive: true, maintainAspectRatio: false,
                                plugins: { legend: { position: 'bottom', labels: { color: texte, boxWidth: 12 } } },
                                scales: {
                                    x: { ticks: { color: texte }, grid: { display: false } },
                                    y: { beginAtZero: true, ticks: { color: texte, stepSize: 1, precision: 0 }, grid: { color: grille } }
                                }
                            }
                        });
                    }

                    var t = document.getElementById('graphTraitement');
                    if (t && !t.dataset.pret) {
                        t.dataset.pret = '1';
                        new Chart(t.getContext('2d'), {
                            type: 'doughnut',
                            data: { labels: ['Traitées', 'À traiter'], datasets: [{ data: traitement, backgroundColor: ['#28c76f', '#f2b705'], borderColor: '#0a192f', borderWidth: 2 }] },
                            options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { display: false } } }
                        });
                    }
                }

                if (!document.getElementById('graphActivite') && !document.getElementById('graphTraitement')) return;

                if (window.Chart) {
                    dessiner();
                } else {
                    var s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
                    s.onload = dessiner;
                    document.head.appendChild(s);
                }
            })();
        </script>
    @endif
</x-admin>