@extends('layouts.site')

@section('title', 'Nos missions — DGTI')
@section('description', 'Les missions de la Direction Générale des Transmissions et de l’Informatique : numérique, transmissions, information géographique, formation et sécurité du système d’information.')

{{--
    Contenu établi d'après l'arrêté portant organisation de la DGTI (3 février 2025).
    Les données sont dans les tableaux $generale et $axes ci-dessous : elles peuvent être déplacées plus tard
    dans la base de données ou un fichier de configuration.
    Les liens « Voir les attributions » pointent vers la page Organigramme (ancres #u-SIGLE).
    Adaptez $organigrammeUrl si la route porte un autre nom.
--}}

@php
    $organigrammeUrl = url('/organigramme');

    $generale = [
        'mission' => 'Assurer la mise en œuvre des politiques et stratégies nationales et ministérielles relatives aux transmissions et aux systèmes d’information au sein du ministère.',
        'points' => [
            'Réaliser, déployer, administrer et maintenir les applications numériques de la transformation digitale du ministère.',
            'Assurer la gestion du parc informatique, de l’infrastructure de communication électronique et des systèmes numériques.',
            'Assurer la liaison par radio télécommunication entre le cabinet, les états-majors et les forces de sécurité intérieure.',
            'Administrer les bases de données du ministère et mettre en œuvre la politique d’information géographique.',
            'Renforcer les capacités du personnel en matière numérique, et assurer la formation et le support technique des utilisateurs.',
            'Assurer la cohérence, la sécurité et l’évolution du système d’information.',
        ],
    ];

    $axes = [
        [
            'sig' => 'DT', 'nom' => 'Transmissions et vidéo protection', 'type' => 'k-tech',
            'phrase' => 'Faire circuler l’information entre le cabinet, les états-majors et les forces de sécurité, et surveiller les axes des villes et les lieux publics.',
            'points' => [
                'Établir les liaisons radio et gérer les fréquences attribuées au ministère.',
                'Exploiter les installations téléphoniques et l’annuaire du ministère.',
                'Administrer le système de vidéo protection et accompagner les officiers de police judiciaire dans leurs enquêtes.',
            ],
            'services' => ['STR' => 'Transmissions radio', 'ST' => 'Téléphonie', 'SVP' => 'Vidéo protection'],
        ],
        [
            'sig' => 'DSI', 'nom' => 'Systèmes d’information', 'type' => 'k-tech',
            'phrase' => 'Mettre en œuvre la politique numérique du ministère : réseaux, applications, bases de données et support aux utilisateurs.',
            'points' => [
                'Concevoir, mettre en service et assurer le bon fonctionnement des réseaux informatiques.',
                'Développer et maintenir les applications et les e-services.',
                'Assurer la sécurité du système d’information et la maintenance du matériel informatique.',
            ],
            'services' => ['SRS' => 'Réseaux et systèmes', 'SID' => 'Ingénierie de développement', 'SES' => 'Exploitation et support', 'USI' => 'Urbaniste des SI'],
        ],
        [
            'sig' => 'DIG', 'nom' => 'Information géographique', 'type' => 'k-tech',
            'phrase' => 'Mettre en place, gérer et promouvoir l’utilisation de l’information géographique pour la sécurité du territoire.',
            'points' => [
                'Réaliser la cartographie des services de sécurité et de la situation sécuritaire.',
                'Exploiter les outils de télédétection et de géolocalisation, dont les drones de surveillance.',
                'Former le personnel du ministère à l’utilisation de ces outils.',
            ],
            'services' => ['SIGT' => 'Information géographique et télédétection', 'SPCTG' => 'Production cartographique et géolocalisation'],
        ],
        [
            'sig' => 'DESF', 'nom' => 'Études, stratégie et formation', 'type' => 'k-tech',
            'phrase' => 'Concevoir les projets stratégiques liés au numérique et former le personnel du ministère.',
            'points' => [
                'Coordonner l’élaboration du SDSI, du SMSI et du PUSI.',
                'Organiser la formation et le perfectionnement du personnel en matière numérique.',
                'Assurer la veille technologique et participer à la prévention et à la lutte contre la cybercriminalité.',
            ],
            'services' => ['SEAT' => 'Études et analyse technique', 'SFPP' => 'Formation et perfectionnement', 'SVIT' => 'Veille et innovation'],
        ],
        [
            'sig' => 'DASP', 'nom' => 'Suivi des programmes et sécurité des SI', 'type' => 'k-tech',
            'phrase' => 'Planifier, coordonner et évaluer les activités de la DGTI, et protéger l’intégrité de ses systèmes d’information.',
            'points' => [
                'Élaborer le programme d’activités et produire les rapports d’exécution.',
                'Définir et mettre en œuvre la politique de sécurité du système d’information.',
                'Tenir à jour les plans de secours informatique et de continuité d’activités.',
            ],
            'services' => ['SPPC' => 'Programme, planification et capitalisation', 'SQSSI' => 'Qualité et sécurité des SI'],
        ],
    ];

    $autres = [
        ['sig' => 'AR', 'nom' => 'Antennes régionales', 'type' => 'k-reg',
         'texte' => 'Services déconcentrés qui mettent en œuvre les politiques de la DGTI dans leur région, évaluent les besoins locaux et font le lien avec les services techniques régionaux.'],
        ['sig' => 'SAF', 'nom' => 'Service administratif et financier', 'type' => 'k-appui',
         'texte' => 'Gère les ressources humaines, le budget, la régie d’avances et le patrimoine de la direction générale.'],
        ['sig' => 'SSECU', 'nom' => 'Service de sécurité', 'type' => 'k-appui',
         'texte' => 'Assure la sécurité des installations, des équipements et du personnel, et l’escorte des missions.'],
        ['sig' => 'SEC', 'nom' => 'Secrétariat particulier', 'type' => 'k-appui',
         'texte' => 'Traite le courrier, organise les audiences et centralise l’information utile au Directeur général.'],
    ];
@endphp

@section('content')

    <!-- ===================== EN-TÊTE ===================== -->
    <section class="dgm-head">
        <div class="dg-wrap">
            <nav class="dgm-crumbs" aria-label="Fil d’Ariane">
                <a href="{{ url('/') }}">Accueil</a>
                <span aria-hidden="true">›</span>
                <span>Direction</span>
                <span aria-hidden="true">›</span>
                <span>Missions</span>
            </nav>
            <h1>Nos missions</h1>
            <p class="dgm-lede">
                Ce que fait la DGTI pour le Ministère de la Sécurité : faire communiquer les forces, faire fonctionner
                les systèmes et mettre le numérique au service de la sécurité.
            </p>
        </div>
    </section>

    <!-- ===================== MISSION GÉNÉRALE ===================== -->
    <section class="dgm-general">
        <div class="dg-wrap dgm-general-grid">
            <div class="dgm-general-main">
                <h2>Mission générale</h2>
                <p class="dgm-statement">{{ $generale['mission'] }}</p>
                <a class="dgm-link dgm-link-light" href="{{ $organigrammeUrl }}#u-DGTI">Voir les attributions de la direction générale</a>
            </div>
            <ul class="dgm-general-list">
                @foreach ($generale['points'] as $p)
                    <li>{{ $p }}</li>
                @endforeach
            </ul>
        </div>
    </section>

    <!-- ===================== MISSIONS PAR DIRECTION ===================== -->
    <section class="dgm-section">
        <div class="dg-wrap dgm-wrap">
            <div class="dgm-intro">
                <h2>Les directions techniques</h2>
                <p>Cinq directions portent les missions de la DGTI. Chacune s’appuie sur des services spécialisés.</p>
            </div>

            <div class="dgm-axes">
                @foreach ($axes as $a)
                    <article class="dgm-axis {{ $a['type'] }}" id="m-{{ $a['sig'] }}">
                        <header class="dgm-axis-head">
                            <span class="dgm-sig">{{ $a['sig'] }}</span>
                            <h3>{{ $a['nom'] }}</h3>
                        </header>
                        <div class="dgm-axis-body">
                            <p class="dgm-axis-phrase">{{ $a['phrase'] }}</p>
                            <ul class="dgm-points">
                                @foreach ($a['points'] as $p)
                                    <li>{{ $p }}</li>
                                @endforeach
                            </ul>
                            <div class="dgm-axis-foot">
                                <ul class="dgm-chips" aria-label="Services rattachés">
                                    @foreach ($a['services'] as $code => $label)
                                        <li><a href="{{ $organigrammeUrl }}#u-{{ $code }}" title="{{ $label }}"><b>{{ $code }}</b> {{ $label }}</a></li>
                                    @endforeach
                                </ul>
                                <a class="dgm-link" href="{{ $organigrammeUrl }}#u-{{ $a['sig'] }}">Voir les attributions</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ===================== ANTENNES ET APPUI ===================== -->
    <section class="dgm-section dgm-section-alt">
        <div class="dg-wrap dgm-wrap">
            <div class="dgm-intro">
                <h2>Sur le terrain et en appui</h2>
                <p>Les antennes régionales portent la DGTI dans les régions ; les services d’appui font fonctionner la direction générale.</p>
            </div>

            <div class="dgm-others">
                @foreach ($autres as $o)
                    <a class="dgm-other {{ $o['type'] }}" href="{{ $organigrammeUrl }}#u-{{ $o['sig'] }}">
                        <span class="dgm-sig">{{ $o['sig'] }}</span>
                        <span class="dgm-other-txt">
                            <strong>{{ $o['nom'] }}</strong>
                            <span>{{ $o['texte'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>

            <p class="dgm-more">
                <a class="dg-btn dg-btn-light" href="{{ $organigrammeUrl }}">Consulter l’organigramme complet</a>
            </p>
        </div>
    </section>

    @push('styles')
        <style>
            .k-appui { --c: var(--dg-amber); }
            .k-tech { --c: var(--dg-brand); }
            .k-reg { --c: var(--dg-green); }

            /* ---------- En-tête de page ---------- */
            .dgm-head { position: relative; overflow: hidden; color: #fff; padding: 44px 0 40px; background: linear-gradient(110deg, #06307a 0%, #0a4aa6 55%, #0d62c9 100%); }
            .dgm-head::after { content: ""; position: absolute; left: 0; right: 0; bottom: 0; height: 5px; background: linear-gradient(90deg, var(--dg-sky), #69c4ff); }
            .dgm-crumbs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; font-size: 14px; color: #cfe0f7; }
            .dgm-crumbs a { color: #fff; text-decoration: none; }
            .dgm-crumbs a:hover { text-decoration: underline; }
            .dgm-head h1 { color: #fff; font: 800 clamp(28px, 4vw, 44px)/1.1 var(--dg-sans); text-transform: uppercase; }
            .dgm-lede { margin-top: 12px; max-width: 64ch; color: #eaf3ff; font-size: 17px; }

            /* ---------- Mission générale ---------- */
            .dgm-general { padding: 56px 0; color: #fff; background: var(--dg-navy, #06214f); }
            .dgm-general-grid { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr); gap: 44px; align-items: start; }
            .dgm-general h2 { margin-bottom: 14px; font: 700 15px/1.3 var(--dg-sans); color: #8fc9ff; }
            .dgm-statement { font: 600 clamp(22px, 2.8vw, 31px)/1.4 var(--dg-serif); color: #fff; max-width: 34ch; }
            .dgm-general-list { display: flex; flex-direction: column; gap: 14px; padding-left: 26px; border-left: 3px solid var(--dg-sky); }
            .dgm-general-list li { font-size: 16px; line-height: 1.6; color: #dbe8fb; }

            .dgm-link { display: inline-block; margin-top: 4px; font: 700 14.5px/1.4 var(--dg-sans); color: var(--dg-accent); text-decoration: none; border-bottom: 2px solid transparent; transition: border-color .2s; }
            .dgm-link:hover, .dgm-link:focus-visible { border-bottom-color: currentColor; }
            .dgm-link-light { margin-top: 22px; color: #8fc9ff; }

            /* ---------- Sections ---------- */
            .dgm-section { padding: 64px 0 72px; }
            .dgm-section-alt { background: var(--dg-soft); }
            .dgm-wrap { max-width: 1040px; }
            .dgm-intro { margin-bottom: 34px; }
            .dgm-intro h2 { margin-bottom: 8px; font-size: clamp(27px, 3.4vw, 36px); font-weight: 700; }
            .dgm-intro p { max-width: 60ch; color: var(--dg-muted); }

            .dgm-sig { display: grid; place-items: center; flex-shrink: 0; min-width: 62px; padding: 7px 10px; border-radius: 8px; font: 800 15px/1 var(--dg-sans); color: #fff; background: var(--c); }
            .k-appui .dgm-sig { color: #1b1500; }

            /* ---------- Directions ---------- */
            .dgm-axes { display: flex; flex-direction: column; }
            .dgm-axis { display: grid; grid-template-columns: 280px minmax(0, 1fr); gap: 0 40px; padding: 34px 0; border-top: 1px solid var(--dg-line); scroll-margin-top: 90px; }
            .dgm-axis:last-child { border-bottom: 1px solid var(--dg-line); }
            .dgm-axis-head { display: flex; flex-direction: column; align-items: flex-start; gap: 12px; }
            .dgm-axis-head h3 { font: 700 21px/1.3 var(--dg-serif); color: var(--dg-navy); }
            .dgm-axis-phrase { margin-bottom: 14px; font-size: 17px; line-height: 1.7; color: var(--dg-ink); max-width: 62ch; }

            .dgm-points { display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px; }
            .dgm-points li { position: relative; padding-left: 20px; font-size: 15.5px; line-height: 1.6; color: var(--dg-muted); }
            .dgm-points li::before { content: ""; position: absolute; left: 2px; top: .68em; width: 8px; height: 8px; border-radius: 2px; background: var(--c); transform: rotate(45deg); }

            .dgm-axis-foot { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px 24px; }
            .dgm-chips { display: flex; flex-wrap: wrap; gap: 8px; }
            .dgm-chips a { display: inline-block; padding: 5px 11px; font-size: 13px; line-height: 1.3; color: var(--dg-muted); background: #fff; border: 1px solid var(--dg-line); border-radius: 999px; text-decoration: none; transition: background .2s, color .2s, border-color .2s; }
            .dgm-chips a b { margin-right: 4px; color: var(--dg-brand); font-family: var(--dg-sans); }
            .dgm-chips a:hover, .dgm-chips a:focus-visible { background: var(--dg-brand); border-color: var(--dg-brand); color: #fff; }
            .dgm-chips a:hover b, .dgm-chips a:focus-visible b { color: #fff; }

            /* ---------- Antennes et appui ---------- */
            .dgm-others { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
            .dgm-other { display: flex; gap: 16px; padding: 18px; background: #fff; border: 1px solid var(--dg-line); border-left: 5px solid var(--c); border-radius: 10px; text-decoration: none; color: var(--dg-ink); transition: border-color .2s, box-shadow .25s; }
            .dgm-other:hover, .dgm-other:focus-visible { border-color: var(--dg-sky); border-left-color: var(--c); box-shadow: 0 14px 30px -22px rgba(5, 20, 60, .65); }
            .dgm-other-txt { display: flex; flex-direction: column; gap: 4px; }
            .dgm-other-txt strong { font: 700 16px/1.35 var(--dg-serif); color: var(--dg-navy); }
            .dgm-other-txt span { font-size: 14.5px; line-height: 1.55; color: var(--dg-muted); }
            .dgm-other:first-child { grid-column: 1 / -1; }

            .dgm-more { margin-top: 34px; text-align: center; }

            /* ---------- Focus clavier ---------- */
            .dgm-link:focus-visible, .dgm-chips a:focus-visible, .dgm-other:focus-visible { outline: 3px solid var(--dg-sky); outline-offset: 3px; }

            /* ---------- Responsive ---------- */
            @media (max-width: 900px) {
                .dgm-general-grid { grid-template-columns: 1fr; gap: 30px; }
                .dgm-axis { grid-template-columns: 1fr; gap: 16px; }
                .dgm-axis-head { flex-direction: row; align-items: center; }
            }

            @media (max-width: 700px) {
                .dgm-general { padding: 44px 0; }
                .dgm-section { padding: 48px 0 56px; }
                .dgm-others { grid-template-columns: 1fr; }
                .dgm-general-list { padding-left: 18px; }
            }

            @media print {
                .dgm-axis, .dgm-other { break-inside: avoid; }
                .dgm-more { display: none; }
            }
        </style>
    @endpush

@endsection