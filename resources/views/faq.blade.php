@extends('layouts.site')

@section('title', 'Questions fréquentes — DGTI')
@section('description', 'Réponses aux questions fréquentes sur la Direction Générale des Transmissions et de l’Informatique : organisation, missions, antennes régionales, formation et contact.')

{{--
    Contenu établi d'après l'arrêté portant organisation de la DGTI (3 février 2025).
    Les questions sont dans le tableau $faq ci-dessous (catégorie => liste de questions/réponses) :
    elles peuvent être déplacées plus tard dans la base de données ou un fichier de configuration.
    Les réponses acceptent du HTML simple (liens). Variable facultative : $param_site->email_contact.
    Adaptez $organigrammeUrl et $missionsUrl si vos routes portent un autre nom.
--}}

@php
    $organigrammeUrl = url('/organigramme');
    $missionsUrl = url('/missions');
    $email = $param_site->email_contact ?? null;

    $faq = [
        'La DGTI' => [
            [
                'q' => 'Qu’est-ce que la DGTI ?',
                'r' => 'La Direction Générale des Transmissions et de l’Informatique est la structure du Ministère de la Sécurité chargée de mettre en œuvre les politiques et stratégies nationales et ministérielles relatives aux transmissions et aux systèmes d’information.',
            ],
            [
                'q' => 'Qui dirige la DGTI ?',
                'r' => 'Un Directeur général, nommé par décret en Conseil des ministres sur proposition du Ministre chargé de la sécurité. Il coordonne, contrôle et évalue l’ensemble des activités des directions.',
            ],
            [
                'q' => 'Comment la DGTI est-elle organisée ?',
                'r' => 'Elle comprend trois services d’appui (Secrétariat particulier, Service administratif et financier, Service de sécurité), cinq directions techniques et des antennes régionales. Le détail figure dans l’<a href="'.$organigrammeUrl.'">organigramme</a>, établi d’après l’arrêté du 3 février 2025.',
            ],
        ],

        'Missions' => [
            [
                'q' => 'Quelles sont les principales missions de la DGTI ?',
                'r' => 'Réaliser et maintenir les applications numériques du ministère, gérer le parc informatique et l’infrastructure de communication, assurer les liaisons radio avec les forces de sécurité, administrer les bases de données, mettre en œuvre la politique d’information géographique et former le personnel au numérique. Elles sont présentées sur la page <a href="'.$missionsUrl.'">Nos missions</a>.',
            ],
            [
                'q' => 'Que signifient SDSI, SMSI et PUSI ?',
                'r' => 'Le SDSI est le schéma directeur du système d’information, le SMSI le système de management de la sécurité de l’information et le PUSI le plan d’urbanisation du système d’information. La DGTI coordonne leur élaboration, leur mise en œuvre et leur suivi.',
            ],
            [
                'q' => 'Que fait la Direction des transmissions ?',
                'r' => 'Elle établit les liaisons par radio télécommunication entre le cabinet, les états-majors et les forces de sécurité intérieure, gère les fréquences radio attribuées au ministère et exploite les installations téléphoniques. Elle comprend le Service des transmissions radio, le Service de la téléphonie et le Service de vidéo protection.',
            ],
            [
                'q' => 'À quoi sert le système de vidéo protection ?',
                'r' => 'Il permet de surveiller en permanence les principaux axes des villes et des lieux publics, de contribuer à la fluidité du trafic routier urbain et à la lutte contre l’insécurité routière, d’alerter les services compétents en cas d’incident et d’accompagner les officiers de police judiciaire dans leurs enquêtes. Il est administré par le Service de vidéo protection.',
            ],
            [
                'q' => 'Quel est le rôle de la Direction de l’information géographique ?',
                'r' => 'Elle collecte et analyse les données géographiques liées à la sécurité, réalise la cartographie des services de sécurité et de la situation sécuritaire du territoire, exploite les outils de géolocalisation et de télédétection et encourage l’utilisation des drones de surveillance dans les opérations de sécurisation.',
            ],
        ],

        'Antennes régionales' => [
            [
                'q' => 'Qu’est-ce qu’une antenne régionale ?',
                'r' => 'C’est un service déconcentré de la DGTI qui met en œuvre, au niveau de la région, les politiques relatives aux transmissions et aux systèmes d’information. Sa zone de compétence peut couvrir une ou plusieurs régions administratives.',
            ],
            [
                'q' => 'Que font les antennes régionales ?',
                'r' => 'Elles veillent au bon fonctionnement des installations et systèmes de la direction générale dans leur zone, assurent la liaison avec les services techniques régionaux en charge des transmissions et de l’informatique, évaluent les besoins des structures de la région et contribuent à l’organisation des missions de la direction générale.',
            ],
            [
                'q' => 'Qui crée une antenne et qui la dirige ?',
                'r' => 'Une antenne est créée par arrêté du Ministre. Elle est dirigée par un chef d’antenne, nommé par arrêté du Ministre chargé de la sécurité sur proposition du Directeur général, avec rang de chef de service.',
            ],
        ],

        'Formation et sécurité' => [
            [
                'q' => 'Qui assure la formation du personnel au numérique ?',
                'r' => 'La Direction des études, de la stratégie et de la formation, par son Service de la formation et du perfectionnement professionnel. Ce service identifie les besoins, élabore le plan de formation, met à niveau le personnel nouvellement affecté et mène des actions de sensibilisation aux bonnes pratiques et à la cybercriminalité.',
            ],
            [
                'q' => 'Qui est responsable de la sécurité du système d’information ?',
                'r' => 'La Direction de l’administration et du suivi des programmes définit et met en œuvre la politique de sécurité du système d’information, par son Service qualité et sécurité des systèmes d’information. La Direction des systèmes d’information en assure la sécurité opérationnelle et la maintenance du matériel informatique.',
            ],
            [
                'q' => 'Qui apporte le support technique aux utilisateurs ?',
                'r' => 'Le Service exploitation et support, rattaché à la Direction des systèmes d’information, assure le bon fonctionnement des applications déployées et des postes de travail, la maintenance du matériel informatique et le conseil sur la bonne utilisation des outils.',
            ],
        ],

        'Contact et courrier' => [
            [
                'q' => 'Comment adresser un courrier à la DGTI ?',
                'r' => 'Le Secrétariat particulier centralise, traite, archive et ventile tous les courriers de la direction générale. Il organise aussi les audiences du Directeur général.',
            ],
            [
                'q' => 'Comment contacter la DGTI ?',
                'r' => $email
                    ? 'Vous pouvez écrire à <a href="mailto:'.e($email).'">'.e($email).'</a>. Pour un courrier officiel, adressez-vous au Secrétariat particulier du Directeur général.'
                    : 'Adressez votre demande au Secrétariat particulier du Directeur général, qui centralise et oriente les courriers vers les services concernés.',
            ],
        ],
    ];

    $total = collect($faq)->flatten(1)->count();
@endphp

@section('content')

    <!-- ===================== EN-TÊTE ===================== -->
    <section class="dgf-head">
        <div class="dg-wrap">
            <nav class="dgf-crumbs" aria-label="Fil d’Ariane">
                <a href="{{ url('/') }}">Accueil</a>
                <span aria-hidden="true">›</span>
                <span>Questions fréquentes</span>
            </nav>
            <h1>Questions fréquentes</h1>
            <p class="dgf-lede">
                Les réponses aux questions les plus courantes sur la DGTI, son organisation, ses missions et la façon de la joindre.
            </p>
        </div>
    </section>

    <!-- ===================== FAQ ===================== -->
    <section class="dgf-section">
        <div class="dg-wrap dgf-wrap">

            <div class="dgf-tools">
                <label class="dgf-search" for="dgfSearch">
                    <span class="dgf-visually-hidden">Rechercher une question</span>
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="8.5" cy="8.5" r="5.5" /><path d="M13 13l4 4" /></svg>
                    <input type="search" id="dgfSearch" placeholder="Rechercher une question…" autocomplete="off">
                </label>

                <div class="dgf-cats" role="group" aria-label="Filtrer par thème">
                    <button type="button" class="dgf-cat is-active" data-cat="" aria-pressed="true">Toutes</button>
                    @foreach ($faq as $cat => $items)
                        <button type="button" class="dgf-cat" data-cat="{{ \Illuminate\Support\Str::slug($cat) }}" aria-pressed="false">{{ $cat }}</button>
                    @endforeach
                </div>
            </div>

            <p class="dgf-count" id="dgfCount" aria-live="polite">{{ $total }} questions</p>

            @foreach ($faq as $cat => $items)
                <section class="dgf-group" data-cat="{{ \Illuminate\Support\Str::slug($cat) }}">
                    <h2>{{ $cat }}</h2>
                    <div class="dgf-list">
                        @foreach ($items as $i)
                            <details class="dgf-item">
                                <summary>
                                    <span>{{ $i['q'] }}</span>
                                    <svg class="dgf-chev" viewBox="0 0 12 8" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 1l5 5 5-5" /></svg>
                                </summary>
                                <div class="dgf-answer"><p>{!! $i['r'] !!}</p></div>
                            </details>
                        @endforeach
                    </div>
                </section>
            @endforeach

            <p class="dgf-empty" id="dgfEmpty" hidden>Aucune question ne correspond à votre recherche. Essayez un autre mot, ou retirez le filtre de thème.</p>

            <aside class="dgf-help">
                <div>
                    <h2>Vous ne trouvez pas votre réponse ?</h2>
                    <p>
                        @if ($email)
                            Écrivez-nous à <a href="mailto:{{ $email }}">{{ $email }}</a>.
                        @else
                            Adressez votre demande au Secrétariat particulier du Directeur général.
                        @endif
                    </p>
                </div>
                <a class="dg-btn dg-btn-light" href="{{ $organigrammeUrl }}">Consulter l’organigramme</a>
            </aside>
        </div>
    </section>

    @push('scripts')
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => collect($faq)->flatten(1)->map(fn ($i) => [
                    '@type' => 'Question',
                    'name' => $i['q'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($i['r'])],
                ])->values(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
        </script>
        <script>
            (function() {
                var input = document.getElementById('dgfSearch');
                var cats = document.querySelectorAll('.dgf-cat');
                var groups = document.querySelectorAll('.dgf-group');
                var items = document.querySelectorAll('.dgf-item');
                var count = document.getElementById('dgfCount');
                var empty = document.getElementById('dgfEmpty');
                var cat = '';

                function norm(s) {
                    return (s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                }

                /* Filtre par thème et par texte (sans tenir compte des accents) */
                function apply() {
                    var q = norm(input.value.trim());
                    var shown = 0;

                    groups.forEach(function(g) {
                        var inCat = !cat || g.getAttribute('data-cat') === cat;
                        var visibleInGroup = 0;
                        g.querySelectorAll('.dgf-item').forEach(function(it) {
                            var ok = inCat && (!q || norm(it.textContent).indexOf(q) !== -1);
                            it.hidden = !ok;
                            if (ok) visibleInGroup++;
                        });
                        g.hidden = visibleInGroup === 0;
                        shown += visibleInGroup;
                    });

                    count.textContent = shown + (shown > 1 ? ' questions' : ' question');
                    empty.hidden = shown !== 0;
                }

                input.addEventListener('input', apply);

                cats.forEach(function(b) {
                    b.addEventListener('click', function() {
                        cat = b.getAttribute('data-cat');
                        cats.forEach(function(x) {
                            var on = x === b;
                            x.classList.toggle('is-active', on);
                            x.setAttribute('aria-pressed', on ? 'true' : 'false');
                        });
                        apply();
                    });
                });

                /* Une seule réponse ouverte à la fois */
                items.forEach(function(it) {
                    it.addEventListener('toggle', function() {
                        if (!it.open) return;
                        items.forEach(function(o) { if (o !== it) o.open = false; });
                    });
                });
            })();
        </script>
    @endpush

    @push('styles')
        <style>
            /* ---------- En-tête de page ---------- */
            .dgf-head { position: relative; overflow: hidden; color: #fff; padding: 44px 0 40px; background: linear-gradient(110deg, #06307a 0%, #0a4aa6 55%, #0d62c9 100%); }
            .dgf-head::after { content: ""; position: absolute; left: 0; right: 0; bottom: 0; height: 5px; background: linear-gradient(90deg, var(--dg-sky), #69c4ff); }
            .dgf-crumbs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; font-size: 14px; color: #cfe0f7; }
            .dgf-crumbs a { color: #fff; text-decoration: none; }
            .dgf-crumbs a:hover { text-decoration: underline; }
            .dgf-head h1 { color: #fff; font: 800 clamp(28px, 4vw, 44px)/1.1 var(--dg-sans); text-transform: uppercase; }
            .dgf-lede { margin-top: 12px; max-width: 64ch; color: #eaf3ff; font-size: 17px; }

            .dgf-section { padding: 52px 0 80px; }
            .dgf-wrap { max-width: 820px; }
            .dgf-visually-hidden { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }

            /* ---------- Recherche et thèmes ---------- */
            .dgf-tools { display: flex; flex-direction: column; gap: 18px; }
            .dgf-search { position: relative; display: block; }
            .dgf-search svg { position: absolute; left: 16px; top: 50%; width: 18px; height: 18px; margin-top: -9px; color: var(--dg-muted); pointer-events: none; }
            .dgf-search input { width: 100%; padding: 14px 16px 14px 46px; font: 400 16px/1.4 var(--dg-sans); color: var(--dg-ink); background: #fff; border: 1px solid var(--dg-line); border-radius: 10px; transition: border-color .2s, box-shadow .2s; }
            .dgf-search input:focus { outline: none; border-color: var(--dg-sky); box-shadow: 0 0 0 4px rgba(30, 136, 229, .18); }

            .dgf-cats { display: flex; flex-wrap: wrap; gap: 8px; }
            .dgf-cat { padding: 7px 15px; font: 600 14px/1.3 var(--dg-sans); color: var(--dg-brand); background: #fff; border: 1px solid var(--dg-line); border-radius: 999px; cursor: pointer; transition: background .2s, color .2s, border-color .2s; }
            .dgf-cat:hover { border-color: var(--dg-brand); }
            .dgf-cat.is-active { color: #fff; background: var(--dg-brand); border-color: var(--dg-brand); }
            .dgf-cat:focus-visible, .dgf-item summary:focus-visible { outline: 3px solid var(--dg-sky); outline-offset: 2px; }

            .dgf-count { margin: 22px 0 6px; font-size: 14px; color: var(--dg-muted); }

            /* ---------- Questions ---------- */
            .dgf-group { margin-top: 30px; }
            .dgf-group[hidden], .dgf-item[hidden], .dgf-empty[hidden] { display: none; }
            .dgf-group h2 { margin-bottom: 12px; padding-bottom: 8px; font: 700 20px/1.3 var(--dg-serif); color: var(--dg-navy); border-bottom: 2px solid var(--dg-brand); }

            .dgf-list { display: flex; flex-direction: column; gap: 8px; }
            .dgf-item { background: #fff; border: 1px solid var(--dg-line); border-radius: 10px; transition: border-color .25s, box-shadow .25s; }
            .dgf-item[open] { border-color: var(--dg-sky); box-shadow: 0 16px 34px -26px rgba(5, 20, 60, .7); }
            .dgf-item summary { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 16px 18px; font: 600 16.5px/1.4 var(--dg-sans); color: var(--dg-ink); cursor: pointer; list-style: none; }
            .dgf-item summary::-webkit-details-marker { display: none; }
            .dgf-chev { width: 14px; height: 14px; flex-shrink: 0; color: var(--dg-brand); transition: transform .25s var(--dg-ease); }
            .dgf-item[open] .dgf-chev { transform: rotate(180deg); }
            .dgf-answer { padding: 0 18px 18px; }
            .dgf-answer p { max-width: 68ch; font-size: 16px; line-height: 1.7; color: var(--dg-muted); }
            .dgf-answer a { font-weight: 700; color: var(--dg-accent); }

            .dgf-empty { margin-top: 30px; padding: 22px; text-align: center; color: var(--dg-muted); background: var(--dg-soft); border-radius: 10px; }

            /* ---------- Aide ---------- */
            .dgf-help { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 18px 28px; margin-top: 52px; padding: 26px 28px; background: var(--dg-soft); border-left: 5px solid var(--dg-brand); border-radius: 10px; }
            .dgf-help h2 { margin-bottom: 4px; font: 700 20px/1.3 var(--dg-serif); color: var(--dg-navy); }
            .dgf-help p { color: var(--dg-muted); }
            .dgf-help p a { font-weight: 700; color: var(--dg-accent); }

            @media (max-width: 700px) {
                .dgf-section { padding: 40px 0 60px; }
                .dgf-item summary { padding: 14px; font-size: 16px; }
                .dgf-answer { padding: 0 14px 16px; }
                .dgf-help { padding: 22px 18px; }
            }
        </style>
    @endpush

@endsection