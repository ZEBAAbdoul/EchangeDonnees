@extends('layouts.site')

@section('title', 'DGTI — Accueil')

{{--
    Variables attendues (toutes optionnelles) :
      $param_site  : hero_image, meta_description, mot_dg_nom, mot_dg_titre, mot_dg_photo_url, mot_dg_contenu,
                     helpdesk_tel, helpdesk_url
      $slides      : (facultatif) diapositives du hero. Objets ou tableaux avec :
                     ->label, ->titre, ->texte, ->bouton, ->lien, ->image
                     Un simple chemin d'image est aussi accepté (les textes par défaut sont alors utilisés).
                     À défaut, trois diapositives par défaut s'affichent, avec le radar animé à droite.
      $stats       : (facultatif) ->valeur (entier), ->libelle, ->suffixe. Rien n'est affiché si absent.
      $actualites  : titre, resume, slug, created_at
      $projets     : titre, image, url
      $partenaires : nom, logo_url, site_web
      $explications: (facultatif) titre, resume, image, url
    Barre du haut, bandeau, menu et pied de page : layouts/site.blade.php.
--}}

@php
    $stats = $stats ?? collect();
    $actualites = $actualites ?? collect();
    $projets = $projets ?? collect();
    $partenaires = $partenaires ?? collect();
    $explications = $explications ?? collect();

    // Diapositives par défaut du hero
    $defaults = [
        [
            'label' => 'Bienvenue',
            'titre' => 'Direction Générale des Transmissions et de l’Informatique',
            'texte' =>
                $param_site->meta_description ??
                'Nous construisons et exploitons les réseaux, les systèmes d’information et les plateformes numériques qui soutiennent le travail des forces de sécurité.',
            'bouton' => 'Découvrir la DGTI',
            'lien' => '#qui-sommes-nous',
            'image' => $param_site->hero_image ?? null,
        ],
        [
            'label' => 'Plateformes',
            'titre' => 'Faites vos démarches en ligne',
            'texte' => 'Retrouvez les services numériques coordonnés par la DGTI, accessibles depuis un seul endroit.',
            'bouton' => 'Voir les plateformes',
            'lien' => '#projets',
            'image' => null,
        ],
        [
            'label' => 'Support',
            'titre' => 'Un incident sur le réseau ou un équipement ?',
            'texte' =>
                'Le support de la DGTI prend votre demande en charge' .
                (!empty($param_site->helpdesk_tel) ? ' : ' . $param_site->helpdesk_tel : '.'),
            'bouton' => 'Contacter le support',
            'lien' => $param_site->helpdesk_url ?? '#contact',
            'image' => null,
        ],
    ];

    // Diapositives du hero (données en dur, images dans public/images/slides/)
    $slides = collect([
        [
            'label' => 'Bienvenue',
            'titre' => 'Direction Générale des Transmissions et de l’Informatique',
            'texte' =>
                'Nous construisons et exploitons les réseaux, les systèmes d’information et les plateformes numériques qui soutiennent le travail des forces de sécurité.',
            'bouton' => 'Découvrir la DGTI',
            'lien' => '#qui-sommes-nous',
            'image' => 'images/slides/slide-1.jpg',
        ],
        [
            'label' => 'Radiocommunication',
            'titre' => 'Des liaisons fiables pour les forces de sécurité',
            'texte' =>
                'La DGTI assure la liaison radio entre le cabinet, les états-majors et les forces de sécurité intérieure.',
            'bouton' => 'Voir nos missions',
            'lien' => '#missions',
            'image' => 'images/slides/slide-2.jpg',
        ],
        [
            'label' => 'Équipe technique',
            'titre' => 'Une équipe à votre service',
            'texte' =>
                'Un incident sur le réseau ou un équipement ? Le support de la DGTI prend votre demande en charge' .
                (!empty($param_site->helpdesk_tel) ? ' : ' . $param_site->helpdesk_tel : '.'),
            'bouton' => 'Contacter le support',
            'lien' => $param_site->helpdesk_url ?? '#contact',
            'image' => 'images/slides/slide-3.WEBP',
        ],
        [
            'label' => 'Plateforme',
            'titre' => 'e-Contravention',
            'texte' =>
                'La plateforme de gestion numérique des contraventions, pour des démarches plus simples et plus transparentes.',
            'bouton' => 'Découvrir e-Contravention',
            'lien' => '#projets',
            'image' => 'images/slides/slide-4.jpg',
        ],
        [
            'label' => 'Sécurité',
            'titre' => 'Vidéoprotection',
            'texte' =>
                'Un dispositif de vidéoprotection pour renforcer la sécurité des espaces publics et appuyer le travail des forces de sécurité.',
            'bouton' => 'Découvrir la vidéoprotection',
            'lien' => '#projets',
            'image' => 'images/slides/slide-5.jpg',
        ],
    ]);

    // Radar animé : affiché à droite quand une diapositive n'a pas d'image
    $radar = <<<'HTML'
    <div class="dg-radar" aria-hidden="true">
        <span class="dg-ring" style="--i:0"></span>
        <span class="dg-ring" style="--i:14%"></span>
        <span class="dg-ring" style="--i:28%"></span>
        <span class="dg-ring" style="--i:42%"></span>
        <span class="dg-cross"></span>
        <span class="dg-sweep"></span>
        <span class="dg-blip" style="--x:68%;--y:24%;--c:#19d98b;--delay:.2s"></span>
        <span class="dg-blip" style="--x:24%;--y:34%;--c:#f2b705;--delay:1.1s"></span>
        <span class="dg-blip" style="--x:78%;--y:66%;--c:#1e88e5;--delay:1.9s"></span>
        <span class="dg-blip" style="--x:36%;--y:76%;--c:#d8232a;--delay:2.6s"></span>
        <span class="dg-tag" style="--x:-4%;--y:14%;--d:0s">transmissions</span>
        <span class="dg-tag" style="--x:70%;--y:2%;--d:1.2s">réseaux</span>
        <span class="dg-tag" style="--x:78%;--y:88%;--d:2.4s">plateformes</span>
        <span class="dg-tag" style="--x:-6%;--y:82%;--d:3.4s">systèmes d’information</span>
        <div class="dg-radar-core"><span>DGTI</span></div>
    </div>
    HTML;

    $nomDg = $param_site->mot_dg_nom ?? 'Zakaria HÉBIÉ';
    $titreDg = $param_site->mot_dg_titre ?? 'Directeur Général des Transmissions et de l’Informatique';
    $initialesDg = collect(explode(' ', trim($nomDg)))
        ->filter()
        ->map(fn($p) => mb_substr($p, 0, 1))
        ->take(2)
        ->implode('');

    // Valeurs de repli : à remplacer par vos données ou à confirmer avec la DGTI.
    $projetsRepli = [
        ['titre' => 'Fiche de voyage', 'image' => null, 'url' => 'https://fichedevoyage.gov.bf'],
        ['titre' => 'SITAR', 'image' => null, 'url' => 'https://activitesreglementees-04.msecu-test.gov.bf'],
        ['titre' => 'DGPN', 'image' => null, 'url' => 'https://msecu-test.gov.bf'],
        ['titre' => 'SYGESS', 'image' => null, 'url' => null],
        ['titre' => 'e-Contravention', 'image' => null, 'url' => null],
        ['titre' => 'Vidéo-verbalisation', 'image' => null, 'url' => null],
    ];
    $listeProjets = $projets->isNotEmpty()
        ? $projets->map(fn($p) => ['titre' => $p->titre, 'image' => $p->image ?? null, 'url' => $p->url ?? null])
        : collect($projetsRepli);

    $missions = [
        [
            'Schéma directeur informatique',
            'Élaborer et mettre en œuvre le schéma directeur informatique du ministère.',
            '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>',
        ],
        [
            'Radiocommunication',
            'Assurer la liaison radio entre le cabinet, les états-majors et les forces de sécurité intérieure.',
            '<path d="M4.9 4.9a10 10 0 0 0 0 14.2M19.1 4.9a10 10 0 0 1 0 14.2M8.1 8.1a5.5 5.5 0 0 0 0 7.8M15.9 8.1a5.5 5.5 0 0 1 0 7.8"/><circle cx="12" cy="12" r="1.5"/>',
        ],
        [
            'Équipement des services',
            'Définir la stratégie d’équipement en matériel de communication, de surveillance et d’informatique.',
            '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/>',
        ],
        [
            'Réseaux et exploitation',
            'Suivre l’exploitation et la maintenance des installations de télécommunication et d’informatique.',
            '<circle cx="12" cy="5" r="2"/><circle cx="5" cy="19" r="2"/><circle cx="19" cy="19" r="2"/><path d="M12 7v4M12 11l-6 6M12 11l6 6"/>',
        ],
    ];

    $acces = [
        [
            'Déclarer un incident',
            'Réseau ou équipement' . (!empty($param_site->helpdesk_tel) ? ' : ' . $param_site->helpdesk_tel : ''),
            $param_site->helpdesk_url ?? '#contact',
        ],
        ['Consulter les plateformes', 'Services numériques de la DGTI', '#projets'],
        ['Télécharger un document', 'Textes, formulaires, rapports', url('/documents')],
        ['Proposer une collaboration', 'Institution, partenaire ou prestataire', '#contact'],
    ];

    $domaines = [
        'transmissions',
        'radiocommunication',
        'systèmes d’information',
        'réseaux',
        'plateformes numériques',
        'équipement des services',
    ];
@endphp

@section('content')

    <!-- ===================== HERO : slider sur fond bleu, forme blanche ondulée à droite ===================== -->
    {{--
    Nouveau slider du hero DGTI. Même variable $slides qu'avant (label, titre, texte, bouton, lien, image).
    1) Remplacez dans accueil le bloc  <section class="dg-hero" id="top"> … </section>  par la section ci-dessous.
    2) Collez le bloc @push('styles') ci-dessous (classes préfixées « dgh- », sans conflit avec l'ancien CSS).
    3) Collez le bloc @push('scripts') ci-dessous.
    4) Vous pouvez ensuite supprimer : $radar, $defaults, le <canvas id="dgNet">, l'ancien CSS .dg-hero / .dg-net / .dg-hs-* /
       .dg-typed / .dg-caret / .dg-btn-pill / radar, et dans le JS les parties « Slider du hero », « Texte qui se tape » et « Réseau animé ».
--}}

    <!-- ===================== HERO : slider ===================== -->
    <section class="dgh" id="top" aria-roledescription="carrousel" aria-label="À la une" style="--dur: 7s">
        <div class="dgh-track">
            @foreach ($slides as $sl)
                @php $tag = $loop->first ? 'h1' : 'h2'; @endphp
                <article class="dgh-slide {{ $loop->first ? 'is-on' : '' }}" role="group" aria-roledescription="diapositive"
                    aria-label="{{ $loop->iteration }} sur {{ $slides->count() }}"
                    @unless ($loop->first) inert aria-hidden="true" @endunless>
                    <div class="dgh-media" aria-hidden="true">
                        @if (!empty($sl['image']))
                            <img src="{{ asset($sl['image']) }}" alt="" width="1600" height="800"
                                @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                        @else
                            <span class="dgh-mono">DGTI</span>
                        @endif
                    </div>

                    <div class="dg-wrap dgh-inner">
                        <div class="dgh-copy">
                            @if (!empty($sl['label']))
                                <p class="dgh-label">{{ $sl['label'] }}</p>
                            @endif
                            <{{ $tag }} class="dgh-title">{{ $sl['titre'] }}</{{ $tag }}>
                                <p class="dgh-text">{{ $sl['texte'] }}</p>
                                @if (!empty($sl['bouton']))
                                    <a href="{{ $sl['lien'] ?? '#' }}" class="dgh-btn">
                                        {{ $sl['bouton'] }}
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M5 12h14M13 6l6 6-6 6" />
                                        </svg>
                                    </a>
                                @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @if ($slides->count() > 1)
            <div class="dgh-bar">
                <div class="dg-wrap dgh-bar-in">
                    <ol class="dgh-tabs" aria-label="Choisir une diapositive">
                        @foreach ($slides as $sl)
                            <li>
                                <button type="button" class="dgh-tab {{ $loop->first ? 'is-on' : '' }}"
                                    aria-label="Diapositive {{ $loop->iteration }} : {{ $sl['label'] ?? $sl['titre'] }}"
                                    @if ($loop->first) aria-current="true" @endif>
                                    <i class="dgh-fill" aria-hidden="true"></i>
                                    <span>{{ $sl['label'] ?? $sl['titre'] }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ol>

                    <div class="dgh-ctrl">
                        <span class="dgh-count" aria-hidden="true">01 / {{ sprintf('%02d', $slides->count()) }}</span>
                        <button type="button" class="dgh-ic dgh-prev" aria-label="Diapositive précédente">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                aria-hidden="true">
                                <path d="M15 5l-7 7 7 7" />
                            </svg>
                        </button>
                        <button type="button" class="dgh-ic dgh-play" aria-label="Mettre le défilement en pause"
                            aria-pressed="false">
                            <svg class="i-pause" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M7 5h3.5v14H7zM13.5 5H17v14h-3.5z" />
                            </svg>
                            <svg class="i-play" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M8 5l11 7-11 7z" />
                            </svg>
                        </button>
                        <button type="button" class="dgh-ic dgh-next" aria-label="Diapositive suivante">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                aria-hidden="true">
                                <path d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </section>

    @push('styles')
        <style>
            .dgh {
                position: relative;
                overflow: hidden;
                color: #fff;
                background: #06214f;
            }

            .dgh::after {
                content: "";
                position: absolute;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 5;
                height: 4px;
                background: linear-gradient(90deg, var(--dg-red) 0 33.3%, var(--dg-amber) 33.3% 66.6%, var(--dg-green) 66.6%);
            }

            .dgh-track {
                display: grid;
            }

            .dgh-slide {
                position: relative;
                grid-area: 1 / 1;
                display: flex;
                align-items: center;
                min-height: clamp(520px, 78vh, 680px);
                padding: 56px 0 120px;
                opacity: 0;
                visibility: hidden;
                transition: opacity .8s var(--dg-ease), visibility .8s;
            }

            .dgh-slide.is-on {
                opacity: 1;
                visibility: visible;
                z-index: 2;
            }

            /* Photo plein cadre + voile bleu : le texte reste lisible à gauche, la photo respire à droite */
            .dgh-media {
                position: absolute;
                inset: 0;
                background: linear-gradient(135deg, #06307a, #0a4aa6 60%, #1e88e5);
            }

            .dgh-media img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .dgh-slide.is-on .dgh-media img {
                animation: dghZoom 9s ease-out both;
            }

            @keyframes dghZoom {
                from {
                    transform: scale(1.07);
                }

                to {
                    transform: scale(1);
                }
            }

            .dgh-slide::before {
                content: "";
                position: absolute;
                inset: 0;
                z-index: 1;
                background: linear-gradient(90deg, rgba(5, 20, 60, .94) 0%, rgba(6, 48, 122, .78) 40%, rgba(6, 48, 122, .28) 72%, rgba(6, 48, 122, .12) 100%), linear-gradient(0deg, rgba(5, 20, 60, .6), transparent 35%);
            }

            .dgh-mono {
                position: absolute;
                right: 8%;
                top: 50%;
                transform: translateY(-50%);
                font: 800 clamp(90px, 16vw, 220px)/1 var(--dg-sans);
                letter-spacing: .04em;
                color: rgba(255, 255, 255, .1);
            }

            .dgh-inner {
                position: relative;
                z-index: 2;
            }

            .dgh-copy {
                max-width: 640px;
            }

            .dgh-copy>* {
                opacity: 0;
                transform: translateY(18px);
            }

            .dgh-slide.is-on .dgh-copy>* {
                opacity: 1;
                transform: none;
                transition: opacity .7s var(--dg-ease), transform .7s var(--dg-ease);
            }

            .dgh-slide.is-on .dgh-copy> :nth-child(1) {
                transition-delay: .1s;
            }

            .dgh-slide.is-on .dgh-copy> :nth-child(2) {
                transition-delay: .2s;
            }

            .dgh-slide.is-on .dgh-copy> :nth-child(3) {
                transition-delay: .3s;
            }

            .dgh-slide.is-on .dgh-copy> :nth-child(4) {
                transition-delay: .4s;
            }

            .dgh-label {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 16px;
                font: 600 16px/1.2 var(--dg-sans);
                color: #bfe0ff;
            }

            .dgh-label::before {
                content: "";
                width: 30px;
                height: 4px;
                border-radius: 2px;
                background: linear-gradient(90deg, var(--dg-red) 0 33%, var(--dg-amber) 33% 66%, var(--dg-green) 66%);
            }

            .dgh-title {
                margin-bottom: 18px;
                color: #fff;
                font: 800 clamp(30px, 4.4vw, 54px)/1.08 var(--dg-sans);
                letter-spacing: -.015em;
                text-wrap: balance;
            }

            .dgh-text {
                max-width: 54ch;
                margin-bottom: 28px;
                font: 400 clamp(16px, 1.5vw, 19px)/1.65 var(--dg-sans);
                color: #e3eefc;
            }

            .dgh-btn {
                display: inline-flex;
                align-items: center;
                gap: 12px;
                padding: 15px 26px;
                font: 700 15px/1 var(--dg-sans);
                color: var(--dg-brand);
                text-decoration: none;
                background: #fff;
                border-radius: 6px;
                transition: background .2s, color .2s, box-shadow .2s;
            }

            .dgh-btn svg {
                width: 18px;
                height: 18px;
                transition: transform .25s var(--dg-ease);
            }

            .dgh-btn:hover,
            .dgh-btn:focus-visible {
                background: var(--dg-amber);
                color: #1b1500;
                box-shadow: 0 0 0 5px rgba(242, 183, 5, .28);
            }

            .dgh-btn:hover svg {
                transform: translateX(4px);
            }

            /* Barre de navigation : une entrée par diapositive, avec sa progression */
            .dgh-bar {
                position: absolute;
                left: 0;
                right: 0;
                bottom: 4px;
                z-index: 4;
                background: rgba(5, 20, 60, .72);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
                border-top: 1px solid rgba(255, 255, 255, .16);
            }

            .dgh-bar-in {
                display: flex;
                align-items: stretch;
                justify-content: space-between;
                gap: 24px;
            }

            .dgh-tabs {
                display: flex;
                flex: 1;
                min-width: 0;
            }

            .dgh-tabs li {
                flex: 1;
                min-width: 0;
            }

            .dgh-tab {
                position: relative;
                display: block;
                width: 100%;
                height: 100%;
                padding: 18px 14px 16px 0;
                text-align: left;
                color: rgba(255, 255, 255, .62);
                background: none;
                border: 0;
                font: 600 14px/1.3 var(--dg-sans);
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                transition: color .2s;
            }

            .dgh-tab:hover,
            .dgh-tab.is-on {
                color: #fff;
            }

            .dgh-tab span {
                display: block;
                overflow: hidden;
                text-overflow: ellipsis;
                padding-right: 14px;
            }

            .dgh-tab::before {
                content: "";
                position: absolute;
                left: 0;
                right: 14px;
                top: 0;
                height: 3px;
                background: rgba(255, 255, 255, .22);
            }

            .dgh-fill {
                position: absolute;
                left: 0;
                right: 14px;
                top: 0;
                height: 3px;
                background: var(--dg-amber);
                transform: scaleX(0);
                transform-origin: left;
            }

            .dgh-tab.is-on .dgh-fill {
                animation: dghFill var(--dur) linear forwards;
            }

            @keyframes dghFill {
                to {
                    transform: scaleX(1);
                }
            }

            .dgh.is-paused .dgh-fill,
            .dgh:hover .dgh-fill,
            .dgh:focus-within .dgh-fill {
                animation-play-state: paused;
            }

            .dgh:not(.is-moving) .dgh-tab.is-on .dgh-fill {
                background: var(--dg-amber);
            }

            .dgh-ctrl {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 10px 0;
            }

            .dgh-count {
                margin-right: 10px;
                font: 500 14px/1 "IBM Plex Mono", ui-monospace, monospace;
                color: #cfe0f7;
            }

            .dgh-ic {
                display: grid;
                place-items: center;
                width: 40px;
                height: 40px;
                padding: 0;
                color: #fff;
                background: transparent;
                border: 1px solid rgba(255, 255, 255, .4);
                border-radius: 6px;
                transition: background .2s, border-color .2s, color .2s;
            }

            .dgh-ic svg {
                width: 17px;
                height: 17px;
            }

            .dgh-ic:hover,
            .dgh-ic:focus-visible {
                background: #fff;
                border-color: #fff;
                color: var(--dg-brand);
            }

            .dgh-play .i-play {
                display: none;
            }

            .dgh.is-paused .dgh-play .i-play {
                display: block;
            }

            .dgh.is-paused .dgh-play .i-pause {
                display: none;
            }

            @media (max-width: 900px) {
                .dgh-slide {
                    min-height: 560px;
                    padding: 44px 0 110px;
                }

                .dgh-slide::before {
                    background: linear-gradient(0deg, rgba(5, 20, 60, .92) 0%, rgba(6, 48, 122, .72) 55%, rgba(6, 48, 122, .45) 100%);
                }

                .dgh-tab span {
                    display: none;
                }

                .dgh-tab {
                    padding: 0;
                    min-height: 56px;
                }

                .dgh-tab::before,
                .dgh-fill {
                    right: 6px;
                    top: 26px;
                    height: 4px;
                    border-radius: 2px;
                }

                .dgh-count {
                    display: none;
                }

                .dgh-bar-in {
                    gap: 12px;
                }
            }

            @media (max-width: 520px) {

                .dgh-prev,
                .dgh-next {
                    display: none;
                }

                .dgh-btn {
                    width: 100%;
                    justify-content: center;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function() {
                var root = document.getElementById('top');
                if (!root) return;
                var slides = [].slice.call(root.querySelectorAll('.dgh-slide'));
                var tabs = [].slice.call(root.querySelectorAll('.dgh-tab'));
                if (slides.length < 2) return;

                var n = slides.length,
                    cur = 0;
                var count = root.querySelector('.dgh-count');
                var playBtn = root.querySelector('.dgh-play');
                var pad = function(v) {
                    return (v < 10 ? '0' : '') + v;
                };

                function show(i) {
                    i = (i + n) % n;
                    slides.forEach(function(s, k) {
                        var on = k === i;
                        s.classList.toggle('is-on', on);
                        if (on) {
                            s.removeAttribute('inert');
                            s.setAttribute('aria-hidden', 'false');
                        } else {
                            s.setAttribute('inert', '');
                            s.setAttribute('aria-hidden', 'true');
                        }
                    });
                    tabs.forEach(function(t, k) {
                        var on = k === i,
                            f = t.querySelector('.dgh-fill');
                        t.classList.toggle('is-on', on);
                        if (on) {
                            t.setAttribute('aria-current', 'true');
                            f.style.animation = 'none';
                            void f.offsetWidth;
                            f.style.animation = '';
                        } else t.removeAttribute('aria-current');
                    });
                    if (count) count.textContent = pad(i + 1) + ' / ' + pad(n);
                    cur = i;
                }

                /* La barre de progression de l'onglet actif pilote le défilement : elle se met en pause avec lui */
                root.addEventListener('animationend', function(e) {
                    if (e.animationName === 'dghFill') show(cur + 1);
                });

                tabs.forEach(function(t, k) {
                    t.addEventListener('click', function() {
                        show(k);
                    });
                });
                root.querySelector('.dgh-prev').addEventListener('click', function() {
                    show(cur - 1);
                });
                root.querySelector('.dgh-next').addEventListener('click', function() {
                    show(cur + 1);
                });

                playBtn.addEventListener('click', function() {
                    var paused = root.classList.toggle('is-paused');
                    playBtn.setAttribute('aria-pressed', paused ? 'true' : 'false');
                    playBtn.setAttribute('aria-label', paused ? 'Reprendre le défilement' :
                        'Mettre le défilement en pause');
                });

                root.addEventListener('keydown', function(e) {
                    if (e.key === 'ArrowLeft') show(cur - 1);
                    if (e.key === 'ArrowRight') show(cur + 1);
                });

                var x0 = null;
                root.addEventListener('touchstart', function(e) {
                    x0 = e.touches[0].clientX;
                }, {
                    passive: true
                });
                root.addEventListener('touchend', function(e) {
                    if (x0 === null) return;
                    var dx = e.changedTouches[0].clientX - x0;
                    if (Math.abs(dx) > 50) show(cur + (dx < 0 ? 1 : -1));
                    x0 = null;
                }, {
                    passive: true
                });
            })();
        </script>
    @endpush

    <!-- ===================== ACCÈS RAPIDES (clairs, à cheval sur le hero) ===================== -->
    {{-- <section id="acces" class="dg-quick" aria-label="Accès rapides">
        <div class="dg-wrap dg-quick-grid">
            @foreach ($acces as [$t, $d, $href])
                <a href="{{ $href }}" class="dg-quick-card">
                    <span class="dg-quick-t">{{ $t }}</span>
                    <span class="dg-quick-d">{{ $d }}</span>
                </a>
            @endforeach
        </div>
    </section> --}}
    

    <!-- ===================== MOT DU DG ===================== -->
    <section id="dg" class="dg-section dg-section-alt">
        <div class="dg-wrap dg-dg">
            <figure class="dg-portrait dg-reveal">
                <div class="dg-frame">
                    @if ($param_site && !empty($param_site->mot_dg_photo_url))
                        <img src="{{ asset($param_site->mot_dg_photo_url) }}" alt="Photo de {{ $nomDg }}">
                    @else
                        <span class="dg-initials" aria-hidden="true">{{ $initialesDg }}</span>
                    @endif
                    <span class="dg-scan" aria-hidden="true"></span>
                    <i class="c c1" aria-hidden="true"></i><i class="c c2" aria-hidden="true"></i><i class="c c3"
                        aria-hidden="true"></i><i class="c c4" aria-hidden="true"></i>
                </div>
                <figcaption>
                    <strong>{{ $nomDg }}</strong>
                    <span>{{ $titreDg }}</span>
                </figcaption>
            </figure>


            <div class="dg-dg-text">

                <h2>Mot du Directeur Général</h2>
                <blockquote>
                    {{ \Illuminate\Support\Str::limit(trim($param_site->mot_dg_contenu ?? 'Le message du Directeur Général sera publié ici.'), 520, '…') }}
                </blockquote>
                <a href="{{ url('/mot-du-directeur') }}" class="dg-link">Lire le message complet</a>
            </div>
        </div>
    </section>

    <!-- ===================== QUI SOMMES-NOUS + MISSIONS ===================== -->
    @php
        // Photo facultative : si le fichier existe, elle remplace le panneau bleu
        $imgAbout = 'images/qui-sommes-nous.jpg';
        $aboutImgOk = file_exists(public_path($imgAbout));
    @endphp
    <section id="qui-sommes-nous" class="dg-section">
        <div class="dg-wrap">
            <div class="dg-about dg-reveal">
                <div class="dg-about-copy">
                    <p class="dg-eyebrow">À propos</p>
                    <h2>Qui sommes-nous ?</h2>
                    <p class="dg-about-lead">
                        La DGTI est la structure du Ministère de la Sécurité chargée des technologies de
                        l’information et de la communication.
                    </p>
                    <p class="dg-about-text">
                        Elle pilote le schéma directeur informatique, les liaisons de transmission et
                        l’équipement numérique des services de sécurité.
                    </p>

                    <ul class="dg-about-points">
                        @foreach (['Schéma directeur informatique', 'Liaisons de transmission', 'Équipement numérique des services'] as $pt)
                            <li>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 12.5l4.5 4.5L19 7.5" />
                                </svg>
                                {{ $pt }}
                            </li>
                        @endforeach
                    </ul>

                    <a href="{{ url('/qui-sommes-nous') }}" class="dg-btn dg-btn-primary">En savoir plus sur la DGTI</a>
                </div>

                <figure class="dg-about-media">
                    @if ($aboutImgOk)
                        <img src="{{ asset($imgAbout) }}" alt="Les équipes de la DGTI" loading="lazy">
                    @else
                        <div class="dg-about-card" aria-hidden="true">
                            <span class="dg-about-mono">DGTI</span>
                        </div>
                    @endif
                    <figcaption class="dg-about-badge">
                        <strong>Ministère de la Sécurité</strong>
                        <span>Transmissions et informatique</span>
                    </figcaption>
                </figure>
            </div>

            <div class="dg-head dg-reveal">
                <div>
                    <h2>Nos missions</h2>
                    <p>Ce que fait la DGTI au quotidien pour les forces de sécurité.</p>
                </div>
            </div>

            <dl id="missions" class="dg-missions">
                @foreach ($missions as [$titre, $texte, $ico])
                    <div class="dg-mission dg-reveal" style="--d:{{ $loop->index * 0.08 }}s">
                        <span class="dg-mission-num" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span>
                        <span class="dg-mi" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                                stroke-linecap="round" stroke-linejoin="round">{!! $ico !!}</svg>
                        </span>
                        <dt>{{ $titre }}</dt>
                        <dd>{{ $texte }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    @if ($stats->isNotEmpty())
        <section class="dg-stats" aria-label="La DGTI en chiffres">
            <div class="dg-wrap dg-stats-grid">
                @foreach ($stats as $st)
                    <div class="dg-stat dg-reveal" style="--d:{{ $loop->index * 0.1 }}s">
                        <strong><span
                                data-count="{{ (int) $st->valeur }}">{{ number_format((int) $st->valeur, 0, ',', ' ') }}</span>{{ $st->suffixe ?? '' }}</strong>
                        <span>{{ $st->libelle }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- ===================== ACTUALITÉS ===================== -->
    @php
        // Le plus récent en premier, quel que soit l'ordre envoyé par le contrôleur
$unes = $actualites->sortByDesc('created_at')->values()->take(5);
$une = $unes->first();
$autres = $unes->slice(1)->values();

// Adresse de l'image d'un article (accessor ->image, sinon colonne image_couverture_url)
$imgUrl = function ($a) {
    $i = $a->image ?? ($a->image_couverture_url ?? null);
    if (!$i) {
        return null;
    }
    if (\Illuminate\Support\Str::startsWith($i, ['http://', 'https://', 'data:'])) {
        return $i;
    }
    return \Illuminate\Support\Str::startsWith($i, '/') ? asset($i) : asset('storage/' . $i);
        };

        $imgUne = $une ? $imgUrl($une) : null;
        $estNouveau = $une && optional($une->created_at)->gte(now()->subDays(14));
    @endphp

    <section id="actualites" class="dg-section dg-section-alt">
        <div class="dg-wrap">
            <div class="dg-head dg-reveal">
                <div>
                    <p class="dg-eyebrow">Actualité</p>
                    <h2>Actualités récentes</h2>
                    <p>Toute l’actualité de la DGTI à travers des articles.</p>
                </div>
                <a href="{{ url('/toutes-les-actualites') }}" class="dg-link">Voir toute l’actualité</a>
            </div>

            @if ($une)
                <div class="dg-news-grid">
                    <div class="dg-news-stage dg-reveal">
                        <a class="dg-news-feature {{ $imgUne ? 'has-img' : '' }}"
                            @if ($imgUne) style="--img: url('{{ $imgUne }}')" @endif
                            href="{{ url('/actualites/' . ($une->slug ?? $une->id)) }}">
                            <i class="dg-news-glow" aria-hidden="true"></i>
                            <i class="dg-news-shine" aria-hidden="true"></i>
                            <i class="dg-news-ring" aria-hidden="true"></i>

                            <span class="dg-news-flags">
                                <span class="dg-news-flag is-main">À la une</span>
                                @if ($estNouveau)
                                    <span class="dg-news-flag is-new">Nouveau</span>
                                @endif
                            </span>

                            <span class="dg-news-body">
                                <time
                                    datetime="{{ optional($une->created_at)->toDateString() }}">{{ optional($une->created_at)->translatedFormat('d F Y') }}</time>
                                <h3>{{ $une->titre }}</h3>
                                @if (!empty($une->chapo ?? $une->resume))
                                    <p>{{ \Illuminate\Support\Str::limit($une->chapo ?? $une->resume, 200) }}</p>
                                @endif
                                <span class="dg-news-cta">Lire l’article <b aria-hidden="true">→</b></span>
                            </span>
                        </a>
                    </div>

                    @if ($autres->isNotEmpty())
                        <div class="dg-news-side">
                            <p class="dg-news-side-title">Aussi dans l’actualité</p>
                            <ul class="dg-news">
                                @foreach ($autres as $a)
                                    @php $thumb = $imgUrl($a); @endphp
                                    <li class="dg-reveal" style="--d:{{ $loop->index * 0.07 }}s">
                                        <a class="dg-news-row" href="{{ url('/actualites/' . ($a->slug ?? $a->id)) }}">
                                            @if ($thumb)
                                                <img class="dg-news-thumb" src="{{ $thumb }}" alt=""
                                                    loading="lazy">
                                            @endif
                                            <span class="dg-news-txt">
                                                <time
                                                    datetime="{{ optional($a->created_at)->toDateString() }}">{{ optional($a->created_at)->translatedFormat('d F Y') }}</time>
                                                <span class="dg-news-title">{{ $a->titre }}</span>
                                            </span>
                                            <span class="dg-news-go" aria-hidden="true">→</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @else
                <p class="dg-muted">Aucune actualité publiée pour le moment.</p>
            @endif
        </div>
    </section>

    <!-- ===================== PROJETS ===================== -->
    <section id="projets" class="dg-section">
        <div class="dg-wrap">
            <div class="dg-head dg-reveal">
                <div>
                    <h2>Nos grands projets</h2>
                    <p>Les plateformes et chantiers numériques portés par la DGTI.</p>
                </div>
            </div>

            <div class="dg-projects">
                @foreach ($listeProjets as $p)
                    <article class="dg-project dg-reveal" style="--d:{{ $loop->index * 0.1 }}s">
                        <div class="dg-project-media">
                            @if (!empty($p['image']))
                                <img src="{{ asset($p['image']) }}" alt="" loading="lazy">
                            @else
                                <span aria-hidden="true">{{ mb_substr($p['titre'], 0, 1) }}</span>
                            @endif
                            <i class="dg-project-scan" aria-hidden="true"></i>
                            <b
                                class="dg-chip {{ !empty($p['url']) ? 'is-on' : '' }}">{{ !empty($p['url']) ? 'En ligne' : 'opérationnel' }}</b>
                        </div>
                        <div class="dg-project-body">
                            <h3>{{ $p['titre'] }}</h3>
                            @if (!empty($p['url']))
                                <a href="{{ $p['url'] }}" class="dg-link" target="_blank" rel="noopener">Ouvrir la
                                    plateforme</a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>



    <!-- ===================== UNE MINUTE POUR COMPRENDRE (facultatif) ===================== -->
    @if ($explications->isNotEmpty())
        <section id="comprendre" class="dg-section dg-section-alt">
            <div class="dg-wrap">
                <div class="dg-head dg-reveal">
                    <div>
                        <h2>Une minute pour comprendre</h2>
                        <p>Les notions du numérique que nous rencontrons chaque jour, expliquées simplement.</p>
                    </div>
                </div>

                <div class="dg-explain">
                    @foreach ($explications->take(3) as $e)
                        <a href="{{ $e->url ?? '#' }}" class="dg-explain-item">
                            @if (!empty($e->image))
                                <img src="{{ asset($e->image) }}" alt="" loading="lazy">
                            @endif
                            <div class="dg-explain-body">
                                <h3>{{ $e->titre }}</h3>
                                <p>{{ \Illuminate\Support\Str::limit($e->resume ?? '', 110) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- ===================== APPEL AU SUPPORT ===================== -->
    {{-- <section class="dg-cta" aria-labelledby="dgCtaTitle">
        <div class="dg-wrap dg-cta-in">
            <div>
                <h2 id="dgCtaTitle">Un incident sur le réseau ou un équipement ?</h2>
                <p>Le support de la DGTI prend votre demande en charge{{ !empty($param_site->helpdesk_tel) ? ' : ' . $param_site->helpdesk_tel : '' }}.</p>
            </div>
            <a href="{{ $param_site->helpdesk_url ?? '#contact' }}" class="dg-btn dg-btn-pill">Contacter le support</a>
        </div>
    </section> --}}

    {{--
    Carte « À la une » épinglée en bas à gauche de l'écran, avec photo, titre et bouton de lecture.
    Elle apparaît quelques secondes après l'arrivée, fait défiler les 3 dernières actualités, se réduit en pastille
    « Actualités » (avec le nombre d'articles) et reste ainsi accessible pendant tout le défilement de la page.

    1) Collez le bloc ci-dessous juste avant @push('styles') de la page d'accueil (ou à la fin de @section('content')).
    2) Collez les blocs @push('styles') et @push('scripts').
    3) Si vous l'adoptez, retirez le bandeau précédent (bandeau-actualites) : les deux feraient doublon.
    4) (Facultatif) Une colonne booléenne « epingle » fait passer un article épinglé en premier :
         Migration : $table->boolean('epingle')->default(false);
--}}

@php
    $widgetNews = $actualites
        ->sortByDesc(fn($a) => [!empty($a->epingle) ? 1 : 0, optional($a->created_at)->timestamp])
        ->values()
        ->take(3);

    $widgetImg = function ($a) {
        $i = $a->image ?? ($a->image_couverture_url ?? null);
        if (!$i) {
            return null;
        }
        if (\Illuminate\Support\Str::startsWith($i, ['http://', 'https://', 'data:'])) {
            return $i;
        }
        return \Illuminate\Support\Str::startsWith($i, '/') ? asset($i) : asset('storage/' . $i);
    };
@endphp

@if ($widgetNews->isNotEmpty())
    <!-- ===================== ACTUALITÉS ÉPINGLÉES ===================== -->
    <aside class="dgw" id="dgWidget" data-state="open" aria-label="Actualités à la une" hidden>
        <button type="button" class="dgw-pill" aria-expanded="true" aria-controls="dgwCard">
            <i class="dgw-dot" aria-hidden="true"></i>Actualités <b>{{ $widgetNews->count() }}</b>
        </button>

        <div class="dgw-card" id="dgwCard">
            <i class="dgw-fill" aria-hidden="true"></i>
            <button type="button" class="dgw-x" aria-label="Réduire les actualités">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M6 12h12" /></svg>
            </button>

            <div class="dgw-track">
                @foreach ($widgetNews as $a)
                    @php
                        $im = $widgetImg($a);
                        $nouveau = optional($a->created_at)->gte(now()->subDays(14));
                    @endphp
                    <article class="dgw-item {{ $loop->first ? 'is-on' : '' }}"
                        @unless ($loop->first) inert aria-hidden="true" @endunless>
                        <a class="dgw-link" href="{{ url('/actualites/' . ($a->slug ?? $a->id)) }}">
                            <span class="dgw-img">
                                @if ($im)
                                    <img src="{{ $im }}" alt="" loading="lazy">
                                @endif
                                <span class="dgw-chips">
                                    <span class="dgw-chip is-main">{{ !empty($a->epingle) ? 'Épinglé' : 'À la une' }}</span>
                                    @if ($nouveau)
                                        <span class="dgw-chip is-new">Nouveau</span>
                                    @endif
                                </span>
                            </span>
                            <span class="dgw-body">
                                <time datetime="{{ optional($a->created_at)->toDateString() }}">{{ optional($a->created_at)->translatedFormat('d F Y') }}</time>
                                <strong>{{ $a->titre }}</strong>
                                <span class="dgw-cta">Lire l’article <b aria-hidden="true">→</b></span>
                            </span>
                        </a>
                    </article>
                @endforeach
            </div>

            <div class="dgw-foot">
                @if ($widgetNews->count() > 1)
                    <div class="dgw-dots" role="group" aria-label="Choisir une actualité">
                        @foreach ($widgetNews as $a)
                            <button type="button" class="{{ $loop->first ? 'is-on' : '' }}" aria-label="Actualité {{ $loop->iteration }} sur {{ $widgetNews->count() }}"></button>
                        @endforeach
                    </div>
                @endif
                <a class="dgw-all" href="{{ url('/toutes-les-actualites') }}">Toutes les actualités</a>
            </div>
        </div>
    </aside>
@endif

@push('styles')
    <style>
        .dgw { position: fixed; left: 22px; bottom: 22px; z-index: 65; width: 340px; max-width: calc(100vw - 44px); }
        .dgw[hidden] { display: none; }
        .dgw { opacity: 0; transform: translateY(24px); transition: opacity .6s var(--dg-ease), transform .6s var(--dg-ease); }
        .dgw.is-in { opacity: 1; transform: none; }

        /* Pastille réduite */
        .dgw-pill { display: none; align-items: center; gap: 10px; padding: 12px 18px; font: 700 15px/1 var(--dg-sans); color: #fff; background: var(--dg-brand); border: 0; border-radius: 999px; box-shadow: 0 14px 30px -12px rgba(5, 20, 60, .75); transition: background .2s; }
        .dgw-pill:hover, .dgw-pill:focus-visible { background: var(--dg-brand-2); }
        .dgw-pill b { padding: 3px 8px; font: 700 12.5px/1 var(--dg-sans); color: #fff; background: var(--dg-red); border-radius: 999px; }
        .dgw-dot { width: 9px; height: 9px; border-radius: 50%; background: var(--dg-amber); animation: dgwPulse 1.6s ease-in-out infinite; }
        @keyframes dgwPulse { 50% { opacity: .35; transform: scale(.7); } }
        .dgw[data-state="min"] .dgw-pill { display: inline-flex; }
        .dgw[data-state="min"] .dgw-card { display: none; }

        /* Carte */
        .dgw-card { position: relative; overflow: hidden; background: #fff; border: 1px solid var(--dg-line); border-radius: 14px; box-shadow: 0 28px 56px -22px rgba(5, 20, 60, .65); }
        .dgw[data-state="open"] .dgw-card { animation: dgwOpen .35s var(--dg-ease); }
        @keyframes dgwOpen { from { opacity: 0; transform: translateY(10px) scale(.98); } }

        /* Progression avant l'actualité suivante (met le défilement en pause avec elle) */
        .dgw-fill { position: absolute; left: 0; right: 0; top: 0; z-index: 4; height: 3px; background: var(--dg-amber); transform: scaleX(0); transform-origin: left; animation: dgwFill 7s linear forwards; }
        @keyframes dgwFill { to { transform: scaleX(1); } }
        .dgw:hover .dgw-fill, .dgw:focus-within .dgw-fill { animation-play-state: paused; }

        .dgw-x { position: absolute; top: 10px; right: 10px; z-index: 5; display: grid; place-items: center; width: 32px; height: 32px; padding: 0; color: #fff; background: rgba(5, 20, 60, .6); border: 1px solid rgba(255, 255, 255, .45); border-radius: 50%; transition: background .2s; }
        .dgw-x svg { width: 16px; height: 16px; }
        .dgw-x:hover, .dgw-x:focus-visible { background: var(--dg-brand); }

        .dgw-track { display: grid; }
        .dgw-item { grid-area: 1 / 1; opacity: 0; visibility: hidden; transform: translateX(14px); transition: opacity .45s var(--dg-ease), transform .45s var(--dg-ease), visibility .45s; }
        .dgw-item.is-on { opacity: 1; visibility: visible; transform: none; }
        .dgw-link { display: block; color: var(--dg-ink); text-decoration: none; }

        .dgw-img { position: relative; display: block; aspect-ratio: 16 / 9; overflow: hidden; background-color: var(--dg-brand); background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='104' viewBox='0 0 120 104'%3E%3Cg fill='none' stroke='%23ffffff' stroke-opacity='.12' stroke-width='1.5'%3E%3Cpath d='M30 0l30 17v35L30 69 0 52V17zM90 35l30 17v35l-30 17-30-17V52z'/%3E%3C/g%3E%3C/svg%3E"), linear-gradient(135deg, #06307a, #0a4aa6 60%, #1e88e5); }
        .dgw-img img { width: 100%; height: 100%; object-fit: cover; transition: transform .6s var(--dg-ease); }
        .dgw-link:hover .dgw-img img { transform: scale(1.05); }
        .dgw-img::after { content: ""; position: absolute; inset: 0; background: linear-gradient(0deg, rgba(5, 20, 60, .5), transparent 55%); }

        .dgw-chips { position: absolute; left: 12px; bottom: 12px; z-index: 2; display: flex; gap: 6px; }
        .dgw-chip { padding: 6px 11px; font: 700 12.5px/1 var(--dg-sans); border-radius: 999px; }
        .dgw-chip.is-main { color: #1b1500; background: var(--dg-amber); }
        .dgw-chip.is-new { color: #fff; background: var(--dg-red); }

        .dgw-body { display: flex; flex-direction: column; gap: 8px; padding: 16px 18px 14px; }
        .dgw-body time { font: 500 13px/1 "IBM Plex Mono", ui-monospace, monospace; color: var(--dg-muted); }
        .dgw-body strong { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; font: 700 18px/1.35 var(--dg-serif); color: var(--dg-navy); }
        .dgw-link:hover strong { color: var(--dg-accent); }
        .dgw-cta { display: inline-flex; align-items: center; gap: 8px; align-self: flex-start; font: 700 14px/1 var(--dg-sans); color: var(--dg-brand); }
        .dgw-cta b { transition: transform .25s var(--dg-ease); }
        .dgw-link:hover .dgw-cta b { transform: translateX(4px); }
        .dgw-link:focus-visible { outline: 3px solid var(--dg-sky); outline-offset: -3px; }

        .dgw-foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 18px 14px; border-top: 1px solid var(--dg-line); }
        .dgw-dots { display: flex; gap: 6px; }
        .dgw-dots button { width: 9px; height: 9px; padding: 0; background: var(--dg-line); border: 0; border-radius: 999px; transition: width .3s var(--dg-ease), background .3s; }
        .dgw-dots button.is-on { width: 24px; background: var(--dg-brand); }
        .dgw-all { margin-left: auto; font: 600 13.5px/1 var(--dg-sans); color: var(--dg-accent); text-decoration: none; }
        .dgw-all:hover { text-decoration: underline; text-underline-offset: 4px; }

        @media (max-width: 700px) {
            .dgw { left: 12px; bottom: 12px; width: auto; right: 78px; max-width: none; }
            .dgw[data-state="open"] { right: 12px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .dgw, .dgw-item { transition: none; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function() {
            var w = document.getElementById('dgWidget');
            if (!w) return;
            var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var pill = w.querySelector('.dgw-pill');
            var items = [].slice.call(w.querySelectorAll('.dgw-item'));
            var dots = [].slice.call(w.querySelectorAll('.dgw-dots button'));
            var fill = w.querySelector('.dgw-fill');
            var cur = 0;

            function setState(s, save) {
                w.setAttribute('data-state', s);
                pill.setAttribute('aria-expanded', s === 'open' ? 'true' : 'false');
                if (save) { try { sessionStorage.setItem('dgwState', s); } catch (e) {} }
                if (s === 'open') restart();
            }
            function restart() {
                fill.style.animation = 'none';
                void fill.offsetWidth;
                fill.style.animation = '';
            }
            function show(i) {
                cur = (i + items.length) % items.length;
                items.forEach(function(it, k) {
                    var on = k === cur;
                    it.classList.toggle('is-on', on);
                    if (on) { it.removeAttribute('inert'); it.setAttribute('aria-hidden', 'false'); }
                    else { it.setAttribute('inert', ''); it.setAttribute('aria-hidden', 'true'); }
                });
                dots.forEach(function(d, k) { d.classList.toggle('is-on', k === cur); });
                restart();
            }

            /* État initial : ouvert sur grand écran, réduit sur mobile, ou le dernier choix du visiteur */
            var saved = null;
            try { saved = sessionStorage.getItem('dgwState'); } catch (e) {}
            setState(saved || (window.innerWidth <= 700 ? 'min' : 'open'), false);

            /* Apparition quelques secondes après l'arrivée */
            setTimeout(function() {
                w.hidden = false;
                requestAnimationFrame(function() { w.classList.add('is-in'); });
                if (w.getAttribute('data-state') === 'open') restart();
            }, reduce ? 0 : 2500);

            w.querySelector('.dgw-x').addEventListener('click', function() { setState('min', true); pill.focus(); });
            pill.addEventListener('click', function() { setState('open', true); });
            w.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && w.getAttribute('data-state') === 'open') { setState('min', true); pill.focus(); }
            });

            if (items.length > 1) {
                /* La barre de progression pilote le défilement ; pas de défilement quand la carte est réduite */
                fill.addEventListener('animationend', function() {
                    if (w.getAttribute('data-state') === 'open' && !document.hidden) show(cur + 1);
                    else restart();
                });
                dots.forEach(function(d, k) { d.addEventListener('click', function() { show(k); }); });
            }
        })();
    </script>
@endpush

    @push('styles')
        <style>
            .dg-section {
                padding: 80px 0;
            }

            .dg-section-alt {
                background: var(--dg-soft);
            }

            .dg-muted {
                color: var(--dg-muted);
            }

            .dg-head {
                display: flex;
                justify-content: space-between;
                align-items: flex-end;
                gap: 20px;
                flex-wrap: wrap;
                margin-bottom: 36px;
            }

            .dg-head h2,
            .dg-about h2,
            .dg-dg-text h2 {
                font-size: clamp(27px, 3.4vw, 38px);
                font-weight: 700;
                margin-bottom: 8px;
            }

            .dg-head p {
                color: var(--dg-muted);
            }

            /* ================= HERO ================= */
            .dg-hero {
                position: relative;
                overflow: hidden;
                color: #fff;
                padding: 56px 0 150px;
                background: linear-gradient(110deg, #06307a 0%, #0a4aa6 55%, #0d62c9 100%);
            }

            .dg-hero::after {
                content: "";
                position: absolute;
                left: 0;
                right: 0;
                bottom: 0;
                height: 6px;
                background: linear-gradient(90deg, var(--dg-sky), #69c4ff);
            }

            .dg-net {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
            }

            /* Forme blanche ondulée à droite */
            .dg-hs-wave {
                position: absolute;
                top: 0;
                right: 0;
                bottom: 0;
                width: 50%;
                height: 100%;
                pointer-events: none;
            }

            .dg-hs-wave .w1 {
                fill: #fff;
            }

            .dg-hs-wave .w2 {
                fill: rgba(255, 255, 255, .2);
                animation: dgFloat 9s ease-in-out infinite;
            }

            @keyframes dgFloat {
                50% {
                    transform: translateX(-6px) scaleY(1.01);
                }
            }

            .dg-hs-track {
                position: relative;
                z-index: 2;
                display: grid;
            }

            .dg-hs-slide {
                grid-area: 1 / 1;
                display: grid;
                grid-template-columns: 1fr .8fr;
                gap: 40px;
                align-items: center;
                min-height: 430px;
                opacity: 0;
                visibility: hidden;
                transition: opacity .7s var(--dg-ease), visibility .7s;
            }

            .dg-hs-slide.is-on {
                opacity: 1;
                visibility: visible;
            }

            .dg-hs-copy {
                max-width: 620px;
            }

            .dg-hs-copy>* {
                opacity: 0;
                transform: translateY(22px);
            }

            .dg-hs-slide.is-on .dg-hs-copy>* {
                opacity: 1;
                transform: none;
                transition: opacity .7s var(--dg-ease), transform .7s var(--dg-ease);
            }

            .dg-hs-slide.is-on .dg-hs-copy> :nth-child(1) {
                transition-delay: .1s;
            }

            .dg-hs-slide.is-on .dg-hs-copy> :nth-child(2) {
                transition-delay: .22s;
            }

            .dg-hs-slide.is-on .dg-hs-copy> :nth-child(3) {
                transition-delay: .34s;
            }

            .dg-hs-slide.is-on .dg-hs-copy> :nth-child(4) {
                transition-delay: .46s;
            }

            .dg-hs-slide.is-on .dg-hs-copy> :nth-child(5) {
                transition-delay: .58s;
            }

            .dg-hs-label {
                margin-bottom: 8px;
                font: 700 20px/1.2 var(--dg-sans);
                text-transform: uppercase;
                letter-spacing: .02em;
            }

            .dg-hs-title {
                margin-bottom: 20px;
                color: #fff;
                font: 800 clamp(30px, 4.2vw, 52px)/1.08 var(--dg-sans);
                text-transform: uppercase;
                letter-spacing: -.01em;
            }

            .dg-hs-text {
                margin-bottom: 20px;
                padding-left: 16px;
                border-left: 3px solid #fff;
                font: 500 clamp(16px, 1.6vw, 20px)/1.55 var(--dg-sans);
                color: #eaf3ff;
                max-width: 52ch;
            }

            .dg-typed {
                margin-bottom: 24px;
                font: 400 15px/1.6 "IBM Plex Mono", ui-monospace, monospace;
                color: #cfe6ff;
                min-height: 1.6em;
            }

            .dg-typed span {
                color: #9fe8ff;
            }

            .dg-caret {
                display: inline-block;
                width: 8px;
                height: 1.05em;
                margin-left: 3px;
                vertical-align: -2px;
                background: #9fe8ff;
                animation: dgBlink 1s steps(1) infinite;
            }

            @keyframes dgBlink {
                50% {
                    opacity: 0;
                }
            }

            .dg-btn-pill {
                padding: 14px 26px;
                color: #fff;
                background: rgba(255, 255, 255, .06);
                border: 2px solid #4db3ff;
                border-radius: 999px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .01em;
                font-size: 14px;
                box-shadow: 0 0 0 0 rgba(77, 179, 255, .5);
            }

            .dg-btn-pill svg {
                width: 18px;
                height: 18px;
            }

            .dg-btn-pill:hover {
                color: var(--dg-brand);
                background: #fff;
                border-color: #fff;
                box-shadow: 0 0 0 5px rgba(77, 179, 255, .28);
            }

            .dg-hs-vis {
                display: grid;
                place-items: center;
            }

            .dg-hs-img {
                max-height: 430px;
                width: auto;
                max-width: 100%;
                object-fit: contain;
                filter: drop-shadow(0 24px 30px rgba(5, 20, 60, .3));
                animation: dgFloatY 7s ease-in-out infinite;
            }

            @keyframes dgFloatY {
                50% {
                    transform: translateY(-8px);
                }
            }

            /* Flèches et pastilles */
            .dg-hs-arrow {
                position: absolute;
                top: 50%;
                z-index: 4;
                display: grid;
                place-items: center;
                width: 28px;
                height: 46px;
                margin-top: -23px;
                padding: 0;
                color: #fff;
                background: var(--dg-brand);
                border: 0;
                transition: background .2s, width .25s var(--dg-ease);
            }

            .dg-hs-arrow svg {
                width: 18px;
                height: 18px;
            }

            .dg-hs-arrow:hover {
                background: var(--dg-sky);
                width: 36px;
            }

            .dg-hs-prev {
                left: 0;
            }

            .dg-hs-next {
                right: 0;
            }

            .dg-hs-dots {
                position: absolute;
                left: 0;
                right: 0;
                bottom: 116px;
                z-index: 4;
                display: flex;
                justify-content: center;
                gap: 8px;
            }

            .dg-hs-dots button {
                width: 10px;
                height: 10px;
                padding: 0;
                border: 0;
                border-radius: 999px;
                background: rgba(255, 255, 255, .5);
                transition: width .3s var(--dg-ease), background .3s;
            }

            .dg-hs-dots button.is-on {
                width: 30px;
                background: #fff;
            }

            /* Radar (version claire pour la forme blanche) */
            .dg-radar {
                position: relative;
                width: min(100%, 380px);
                aspect-ratio: 1;
                border-radius: 50%;
            }

            .dg-ring {
                position: absolute;
                inset: var(--i);
                border-radius: 50%;
                border: 1px solid rgba(10, 58, 138, .22);
            }

            .dg-ring:first-child {
                border-color: rgba(10, 58, 138, .45);
            }

            .dg-cross {
                position: absolute;
                inset: 0;
                background: linear-gradient(rgba(10, 58, 138, .16), rgba(10, 58, 138, .16)) center / 1px 100% no-repeat, linear-gradient(rgba(10, 58, 138, .16), rgba(10, 58, 138, .16)) center / 100% 1px no-repeat;
            }

            .dg-sweep {
                position: absolute;
                inset: 0;
                border-radius: 50%;
                background: conic-gradient(from 0deg, rgba(30, 136, 229, .45), rgba(30, 136, 229, 0) 70deg, transparent 71deg);
                animation: dgSpin 5s linear infinite;
            }

            @keyframes dgSpin {
                to {
                    transform: rotate(360deg);
                }
            }

            .dg-blip {
                position: absolute;
                left: var(--x);
                top: var(--y);
                width: 11px;
                height: 11px;
                margin: -5px 0 0 -5px;
                border-radius: 50%;
                background: var(--c);
                box-shadow: 0 0 12px var(--c);
                animation: dgBlip 5s ease-out infinite var(--delay);
            }

            .dg-blip::after {
                content: "";
                position: absolute;
                inset: -4px;
                border-radius: 50%;
                border: 1px solid var(--c);
                animation: dgBlipRing 5s ease-out infinite var(--delay);
            }

            @keyframes dgBlip {

                0%,
                100% {
                    opacity: .3;
                }

                8% {
                    opacity: 1;
                }

                60% {
                    opacity: .4;
                }
            }

            @keyframes dgBlipRing {
                0% {
                    transform: scale(1);
                    opacity: .9;
                }

                60%,
                100% {
                    transform: scale(3.2);
                    opacity: 0;
                }
            }

            .dg-radar-core {
                position: absolute;
                inset: 31%;
                display: grid;
                place-items: center;
                border-radius: 50%;
                background: radial-gradient(circle, #1161c9, #0a3a8a);
                box-shadow: 0 10px 30px rgba(10, 58, 138, .45);
                color: #fff;
                font: 800 clamp(15px, 2.2vw, 22px)/1 var(--dg-sans);
            }

            .dg-tag {
                position: absolute;
                left: var(--x);
                top: var(--y);
                padding: 5px 11px;
                border-radius: 999px;
                font: 500 12.5px/1.2 "IBM Plex Mono", ui-monospace, monospace;
                color: var(--dg-brand);
                background: #fff;
                border: 1px solid rgba(10, 58, 138, .3);
                box-shadow: 0 6px 16px -8px rgba(10, 58, 138, .5);
                animation: dgBob 6s ease-in-out infinite var(--d);
                white-space: nowrap;
            }

            @keyframes dgBob {
                50% {
                    transform: translateY(-7px);
                }
            }

            /* ================= ACCÈS RAPIDES ================= */
            .dg-quick {
                position: relative;
                z-index: 3;
                margin-top: -96px;
            }

            .dg-quick-grid {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 16px;
            }

            .dg-quick-card {
                display: flex;
                flex-direction: column;
                gap: 4px;
                padding: 22px 22px 24px;
                background: #fff;
                border: 1px solid var(--dg-line);
                border-top: 4px solid var(--dg-brand);
                border-radius: 8px;
                text-decoration: none;
                box-shadow: 0 18px 40px -24px rgba(5, 20, 60, .7);
                transition: transform .25s var(--dg-ease), border-top-color .25s;
            }

            .dg-quick-card:hover {
                transform: translateY(-4px);
                border-top-color: var(--dg-sky);
            }

            .dg-quick-t {
                font: 700 17px/1.3 var(--dg-serif);
                color: var(--dg-navy);
            }

            .dg-quick-d {
                font-size: 14.5px;
                color: var(--dg-muted);
            }

            /* Chiffres */
            .dg-stats {
                padding: 56px 0;
                background: linear-gradient(120deg, #06307a, #0a4aa6);
            }

            .dg-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
                gap: 28px;
                text-align: center;
            }

            .dg-stat strong {
                display: block;
                font: 700 clamp(34px, 4.6vw, 52px)/1.1 var(--dg-serif);
                color: #fff;
            }

            .dg-stat>span {
                color: #cfe0f7;
                font-size: 15px;
            }

            .dg-stat::after {
                content: "";
                display: block;
                width: 40px;
                height: 3px;
                margin: 12px auto 0;
                background: linear-gradient(90deg, var(--dg-red) 0 33%, var(--dg-amber) 33% 66%, var(--dg-green) 66%);
            }

            /* ================= QUI SOMMES-NOUS ================= */
            .dg-about {
                display: grid;
                grid-template-columns: 1.05fr 1fr;
                gap: 64px;
                align-items: center;
                margin-bottom: 72px;
            }

            .dg-eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 12px;
                font: 700 13px/1 var(--dg-sans);
                letter-spacing: .12em;
                text-transform: uppercase;
                color: var(--dg-accent);
            }

            .dg-eyebrow::before {
                content: "";
                width: 28px;
                height: 3px;
                border-radius: 2px;
                background: linear-gradient(90deg, var(--dg-red) 0 33%, var(--dg-amber) 33% 66%, var(--dg-green) 66%);
            }

            .dg-about-lead {
                margin: 6px 0 14px;
                font: 600 clamp(19px, 2vw, 22px)/1.5 var(--dg-serif);
                color: var(--dg-navy);
                max-width: 54ch;
            }

            .dg-about-text {
                font-size: 17px;
                color: var(--dg-muted);
                max-width: 58ch;
                margin-bottom: 22px;
            }

            .dg-about-points {
                display: grid;
                gap: 10px;
                margin-bottom: 30px;
            }

            .dg-about-points li {
                display: flex;
                align-items: center;
                gap: 12px;
                font-weight: 600;
                color: var(--dg-ink);
            }

            .dg-about-points svg {
                flex-shrink: 0;
                width: 26px;
                height: 26px;
                padding: 5px;
                border-radius: 50%;
                color: #fff;
                background: var(--dg-green);
            }

            /* Visuel : photo ou panneau bleu */
            .dg-about-media {
                position: relative;
                margin: 0;
                padding: 0 0 26px 26px;
            }

            /* Cadre décalé derrière le visuel */
            .dg-about-media::before {
                content: "";
                position: absolute;
                left: 0;
                bottom: 0;
                width: 70%;
                height: 70%;
                border: 3px solid var(--dg-amber);
                border-radius: 14px;
            }

            .dg-about-media>img,
            .dg-about-card {
                position: relative;
                display: block;
                width: 100%;
                aspect-ratio: 4 / 3;
                border-radius: 14px;
                object-fit: cover;
                box-shadow: 0 28px 50px -28px rgba(5, 20, 60, .65);
            }

            .dg-about-card {
                display: grid;
                place-items: center;
                overflow: hidden;
                background-color: var(--dg-brand);
                background-image:
                    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='104' viewBox='0 0 120 104'%3E%3Cg fill='none' stroke='%23ffffff' stroke-opacity='.12' stroke-width='1.5'%3E%3Cpath d='M30 0l30 17v35L30 69 0 52V17zM90 35l30 17v35l-30 17-30-17V52z'/%3E%3C/g%3E%3C/svg%3E"),
                    linear-gradient(135deg, #06307a, #0a4aa6 60%, #1e88e5);
            }

            .dg-about-mono {
                font: 800 clamp(54px, 8vw, 92px)/1 var(--dg-sans);
                letter-spacing: .04em;
                color: #fff;
                text-shadow: 0 10px 30px rgba(5, 20, 60, .45);
            }

            .dg-about-badge {
                position: absolute;
                right: -14px;
                bottom: 0;
                display: flex;
                flex-direction: column;
                gap: 2px;
                max-width: 250px;
                padding: 14px 18px;
                background: #fff;
                border-left: 4px solid var(--dg-sky);
                border-radius: 8px;
                box-shadow: 0 18px 36px -18px rgba(5, 20, 60, .6);
            }

            .dg-about-badge strong {
                color: var(--dg-brand);
                font-size: 15px;
            }

            .dg-about-badge span {
                color: var(--dg-muted);
                font-size: 13.5px;
            }

            /* ================= MISSIONS ================= */
            .dg-missions {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 22px;
                margin: 0;
            }

            .dg-mission {
                position: relative;
                padding: 26px 24px 28px;
                background: #fff;
                border: 1px solid var(--dg-line);
                border-top: 3px solid var(--dg-brand);
                border-radius: 12px;
                transition: transform .3s var(--dg-ease), box-shadow .3s, border-color .3s;
            }

            .dg-mission:hover {
                transform: translateY(-6px);
                border-top-color: var(--dg-sky);
                box-shadow: 0 24px 44px -28px rgba(11, 42, 85, .55);
            }

            .dg-mission-num {
                position: absolute;
                top: 18px;
                right: 22px;
                font: 700 30px/1 var(--dg-serif);
                color: var(--dg-line);
                transition: color .3s;
            }

            .dg-mission:hover .dg-mission-num {
                color: #b9d4f5;
            }

            .dg-mi {
                display: grid;
                place-items: center;
                width: 52px;
                height: 52px;
                margin-bottom: 18px;
                border-radius: 12px;
                background: var(--dg-brand);
                color: #fff;
                transition: transform .4s var(--dg-ease), background .3s;
            }

            .dg-mi svg {
                width: 26px;
                height: 26px;
            }

            .dg-mission:hover .dg-mi {
                transform: rotate(-6deg) scale(1.06);
                background: var(--dg-sky);
            }

            .dg-mission dt {
                font: 700 18px/1.3 var(--dg-serif);
                color: var(--dg-navy);
                margin-bottom: 8px;
            }

            .dg-mission dd {
                margin: 0;
                color: var(--dg-muted);
                font-size: 15px;
            }

            /* ================= ACTUALITÉS ================= */
            .dg-news-grid {
                display: grid;
                grid-template-columns: 1.45fr 1fr;
                gap: 32px;
                align-items: stretch;
            }

            /* --- Article à la une --- */
            .dg-news-feature {
                position: relative;
                isolation: isolate;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                gap: 40px;
                min-height: 460px;
                padding: 30px;
                border-radius: 16px;
                text-decoration: none;
                color: #fff;
                background-color: var(--dg-brand);
                background-image:
                    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='104' viewBox='0 0 120 104'%3E%3Cg fill='none' stroke='%23ffffff' stroke-opacity='.1' stroke-width='1.5'%3E%3Cpath d='M30 0l30 17v35L30 69 0 52V17zM90 35l30 17v35l-30 17-30-17V52z'/%3E%3C/g%3E%3C/svg%3E"),
                    linear-gradient(140deg, #06307a, #0a4aa6 60%, #1e88e5);
                box-shadow: 0 30px 60px -34px rgba(5, 20, 60, .8);
                transition: transform .35s var(--dg-ease), box-shadow .35s;
            }

            /* .dg-news-feature:hover {
                                transform: translateY(-4px);
                                box-shadow: 0 38px 70px -32px rgba(5, 20, 60, .85);
                            } */

            /* Photo en arrière-plan + voile bleu sombre pour garder le texte lisible */
            /* .dg-news-feature.has-img::before {
                                content: "";
                                position: absolute;
                                inset: 0;
                                z-index: -2;
                                background: var(--img) center / cover no-repeat;
                                transition: transform .8s var(--dg-ease);
                            } */

            .dg-news-feature.has-img::after {
                content: "";
                position: absolute;
                inset: 0;
                z-index: -1;
                background: linear-gradient(180deg, rgba(6, 48, 122, .25) 0%, rgba(5, 20, 60, .55) 45%, rgba(5, 20, 60, .94) 100%);
            }

            .dg-news-feature.has-img:hover::before {
                transform: scale(1.06);
            }

            .dg-news-flags {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .dg-news-flag {
                padding: 7px 14px;
                border-radius: 999px;
                font: 700 12.5px/1 var(--dg-sans);
                letter-spacing: .08em;
                text-transform: uppercase;
            }

            .dg-news-flag.is-main {
                color: #1b1500;
                background: var(--dg-amber);
            }

            .dg-news-flag.is-new {
                color: #fff;
                background: var(--dg-red);
            }

            .dg-news-body {
                display: flex;
                flex-direction: column;
                gap: 14px;
                max-width: 640px;
            }

            .dg-news-feature time {
                font: 500 14px/1 "IBM Plex Mono", ui-monospace, monospace;
                letter-spacing: .03em;
                color: #cfe6ff;
            }

            .dg-news-feature h3 {
                color: #fff;
                font-size: clamp(26px, 3.2vw, 38px);
                line-height: 1.15;
                text-shadow: 0 2px 20px rgba(5, 20, 60, .5);
            }

            .dg-news-feature p {
                color: #e3eefc;
                font-size: 17px;
            }

            .dg-news-cta {
                align-self: flex-start;
                display: inline-flex;
                align-items: center;
                gap: 10px;
                margin-top: 6px;
                padding: 13px 24px;
                font: 700 14.5px/1 var(--dg-sans);
                color: var(--dg-brand);
                background: #fff;
                border-radius: 999px;
                transition: background .25s, color .25s, box-shadow .25s;
            }

            .dg-news-cta b {
                transition: transform .25s var(--dg-ease);
            }

            .dg-news-feature:hover .dg-news-cta {
                background: var(--dg-amber);
                color: #1b1500;
                box-shadow: 0 0 0 5px rgba(242, 183, 5, .28);
            }

            .dg-news-feature:hover .dg-news-cta b {
                transform: translateX(5px);
            }

            /* --- Autres articles --- */
            .dg-news-side {
                display: flex;
                flex-direction: column;
            }

            .dg-news-side-title {
                margin-bottom: 12px;
                padding-bottom: 12px;
                border-bottom: 2px solid var(--dg-brand);
                font: 700 13px/1 var(--dg-sans);
                letter-spacing: .1em;
                text-transform: uppercase;
                color: var(--dg-brand);
            }

            .dg-news {
                display: flex;
                flex-direction: column;
                flex: 1;
                gap: 12px;
            }

            .dg-news li {
                display: flex;
                flex: 1;
            }

            .dg-news-row {
                position: relative;
                overflow: hidden;
                flex: 1;
                display: flex;
                align-items: center;
                gap: 16px;
                padding: 14px 18px 14px 20px;
                background: #fff;
                border: 1px solid var(--dg-line);
                border-radius: 12px;
                text-decoration: none;
                color: var(--dg-ink);
                transition: transform .3s var(--dg-ease), border-color .3s, box-shadow .3s;
            }

            .dg-news-row::before {
                content: "";
                position: absolute;
                left: 0;
                top: 0;
                bottom: 0;
                width: 4px;
                background: var(--dg-sky);
                transform: scaleY(0);
                transition: transform .3s var(--dg-ease);
            }

            .dg-news-row:hover {
                transform: translateX(5px);
                border-color: var(--dg-sky);
                box-shadow: 0 18px 36px -26px rgba(11, 42, 85, .6);
            }

            .dg-news-row:hover::before {
                transform: scaleY(1);
            }

            .dg-news-thumb {
                flex-shrink: 0;
                width: 92px;
                height: 68px;
                border-radius: 8px;
                object-fit: cover;
            }

            .dg-news-txt {
                flex: 1;
                display: flex;
                flex-direction: column;
                gap: 4px;
                min-width: 0;
            }

            .dg-news-row time {
                color: var(--dg-muted);
                font-size: 13.5px;
            }

            .dg-news-title {
                font-size: 16.5px;
                font-weight: 600;
                line-height: 1.4;
            }

            .dg-news-row:hover .dg-news-title {
                color: var(--dg-accent);
            }

            .dg-news-go {
                color: var(--dg-sky);
                font-weight: 700;
                opacity: 0;
                transform: translateX(-6px);
                transition: opacity .25s, transform .25s var(--dg-ease);
            }

            .dg-news-row:hover .dg-news-go {
                opacity: 1;
                transform: none;
            }

            /* ===== Effets de l'article à la une ===== */
            .dg-news-stage {
                display: flex;
                perspective: 1200px;
            }

            .dg-news-stage>.dg-news-feature {
                flex: 1;
            }

            /* Inclinaison 3D (pilotée par le JavaScript via --rx / --ry) */
            .dg-news-feature {
                transform: rotateX(var(--rx, 0deg)) rotateY(var(--ry, 0deg));
                transition: transform .25s ease-out, box-shadow .35s;
                will-change: transform;
            }

            .dg-news-feature:hover {
                transform: rotateX(var(--rx, 0deg)) rotateY(var(--ry, 0deg)) translateY(-4px);
            }

            /* Le texte reste au-dessus des effets */
            .dg-news-flags,
            .dg-news-body {
                position: relative;
                z-index: 3;
            }

            /* Zoom lent sur la photo (Ken Burns) */
            .dg-news-feature.has-img::before {
                animation: dgKen 18s ease-in-out infinite alternate;
            }

            @keyframes dgKen {
                from {
                    transform: scale(1) translate(0, 0);
                }

                to {
                    transform: scale(1.12) translate(-1.5%, -1%);
                }
            }

            /* Reflet lumineux qui suit le curseur */
            .dg-news-glow {
                position: absolute;
                inset: 0;
                z-index: 1;
                pointer-events: none;
                opacity: 0;
                background: radial-gradient(420px circle at var(--mx, 50%) var(--my, 50%), rgba(255, 255, 255, .26), transparent 60%);
                transition: opacity .35s;
            }

            .dg-news-feature:hover .dg-news-glow {
                opacity: 1;
            }

            /* Balayage de lumière au survol */
            .dg-news-shine {
                position: absolute;
                inset: 0;
                z-index: 1;
                pointer-events: none;
                background: linear-gradient(105deg, transparent 38%, rgba(255, 255, 255, .3) 50%, transparent 62%);
                transform: translateX(-130%);
            }

            .dg-news-feature:hover .dg-news-shine {
                animation: dgShine 1.2s ease;
            }

            @keyframes dgShine {
                to {
                    transform: translateX(130%);
                }
            }

            /* Bordure lumineuse qui tourne (utilise @property --a et dgRing du layout) */
            .dg-news-ring {
                position: absolute;
                inset: 0;
                z-index: 2;
                padding: 2px;
                border-radius: 16px;
                pointer-events: none;
                background: conic-gradient(from var(--a), transparent 0 55%, #69c4ff 70%, #fff 80%, var(--dg-amber) 90%, transparent);
                -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
                -webkit-mask-composite: xor;
                mask: linear-gradient(#000 0 0) content-box exclude, linear-gradient(#000 0 0);
                animation: dgRing 6s linear infinite;
            }

            /* Pastille « Nouveau » qui pulse */
            .dg-news-flag.is-new {
                display: inline-flex;
                align-items: center;
                gap: 7px;
            }

            .dg-news-flag.is-new::before {
                content: "";
                width: 7px;
                height: 7px;
                border-radius: 50%;
                background: #fff;
                animation: dgNewsPulse 1.6s ease-in-out infinite;
            }

            @keyframes dgNewsPulse {
                50% {
                    opacity: .3;
                    transform: scale(.7);
                }
            }

            /* Apparition échelonnée des textes */
            @keyframes dgUp {
                from {
                    opacity: 0;
                    transform: translateY(18px);
                }
            }

            .js .dg-news-stage:not(.is-in) .dg-news-flags,
            .js .dg-news-stage:not(.is-in) .dg-news-body>* {
                opacity: 0;
            }

            .js .dg-news-stage.is-in .dg-news-flags {
                animation: dgUp .8s var(--dg-ease) both;
            }

            .js .dg-news-stage.is-in .dg-news-body>* {
                animation: dgUp .8s var(--dg-ease) both;
            }

            .js .dg-news-stage.is-in .dg-news-body> :nth-child(1) {
                animation-delay: .15s;
            }

            .js .dg-news-stage.is-in .dg-news-body> :nth-child(2) {
                animation-delay: .28s;
            }

            .js .dg-news-stage.is-in .dg-news-body> :nth-child(3) {
                animation-delay: .41s;
            }

            .js .dg-news-stage.is-in .dg-news-body> :nth-child(4) {
                animation-delay: .54s;
            }

            /* Projets */
            .dg-projects {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
                gap: 24px;
            }

            .dg-project {
                background: #fff;
                border: 1px solid var(--dg-line);
                border-radius: 10px;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                transition: box-shadow .3s, border-color .3s;
            }

            .dg-project:hover {
                border-color: var(--dg-sky);
                box-shadow: 0 18px 40px -24px rgba(11, 42, 85, .6);
            }

            .dg-project-media {
                position: relative;
                display: grid;
                place-items: center;
                aspect-ratio: 16 / 9;
                overflow: hidden;
                background: linear-gradient(120deg, #06307a, #0a4aa6, #1e88e5, #06307a);
                background-size: 300% 300%;
                animation: dgMesh 12s ease-in-out infinite;
            }

            .dg-project-media>span {
                font: 700 64px/1 var(--dg-serif);
                color: #fff;
            }

            .dg-project-media img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .dg-project-scan {
                position: absolute;
                left: 0;
                right: 0;
                top: 0;
                height: 2px;
                background: #9fe8ff;
                box-shadow: 0 0 18px 3px #9fe8ff;
                opacity: 0;
            }

            .dg-project:hover .dg-project-scan {
                opacity: 1;
                animation: dgProbe 1.6s ease-in-out infinite;
            }

            @keyframes dgProbe {

                0%,
                100% {
                    top: 0;
                }

                50% {
                    top: calc(100% - 2px);
                }
            }

            .dg-chip {
                position: absolute;
                top: 12px;
                right: 12px;
                padding: 4px 10px;
                border-radius: 999px;
                font: 600 12px/1.3 var(--dg-sans);
                color: #fff;
                background: rgba(255, 255, 255, .18);
                border: 1px solid rgba(255, 255, 255, .3);
            }

            .dg-chip.is-on {
                background: rgba(12, 143, 74, .9);
                border-color: #19d98b;
            }

            .dg-project-body {
                display: flex;
                flex-direction: column;
                gap: 8px;
                padding: 20px 22px 24px;
                min-height: 92px;
            }

            .dg-project h3 {
                font-size: 20px;
            }

            /* Mot du DG */
            .dg-dg {
                display: grid;
                grid-template-columns: 260px 1fr;
                gap: 56px;
                align-items: center;
            }

            .dg-portrait {
                margin: 0;
            }

            .dg-frame {
                position: relative;
                overflow: hidden;
                border-radius: 6px;
                background: var(--dg-brand);
            }

            .dg-portrait img,
            .dg-initials {
                width: 100%;
                aspect-ratio: 4 / 5;
                object-fit: cover;
            }

            .dg-scan {
                position: absolute;
                left: 0;
                right: 0;
                top: 0;
                height: 36%;
                background: linear-gradient(180deg, transparent, rgba(77, 179, 255, .3));
                border-bottom: 1px solid #4db3ff;
                animation: dgScan 4.5s ease-in-out infinite;
                pointer-events: none;
            }

            @keyframes dgScan {
                0% {
                    transform: translateY(-100%);
                }

                100% {
                    transform: translateY(300%);
                }
            }

            .dg-frame .c {
                position: absolute;
                width: 22px;
                height: 22px;
                border: 2px solid #fff;
                filter: drop-shadow(0 0 4px rgba(0, 0, 0, .4));
            }

            .dg-frame .c1 {
                top: 8px;
                left: 8px;
                border-right: 0;
                border-bottom: 0;
            }

            .dg-frame .c2 {
                top: 8px;
                right: 8px;
                border-left: 0;
                border-bottom: 0;
            }

            .dg-frame .c3 {
                bottom: 8px;
                left: 8px;
                border-right: 0;
                border-top: 0;
            }

            .dg-frame .c4 {
                bottom: 8px;
                right: 8px;
                border-left: 0;
                border-top: 0;
            }

            .dg-initials {
                display: grid;
                place-items: center;
                background: var(--dg-brand);
                color: #fff;
                font: 700 64px/1 var(--dg-serif);
            }

            .dg-portrait figcaption {
                display: flex;
                flex-direction: column;
                margin-top: 14px;
            }

            .dg-portrait figcaption span {
                font-size: 14px;
                color: var(--dg-muted);
            }

            .dg-dg-text h2 {
                margin-bottom: 20px;
            }

            .dg-dg-text blockquote {
                margin: 0 0 22px;
                padding-left: 24px;
                border-left: 4px solid var(--dg-amber);
                font: 400 20px/1.75 var(--dg-serif);
                max-width: 62ch;
            }

            /* Appel au support */
            .dg-cta {
                position: relative;
                overflow: hidden;
                padding: 64px 0;
                color: #fff;
                background: linear-gradient(110deg, #06307a, #0a4aa6 60%, #0d62c9);
            }

            .dg-cta::before {
                content: "";
                position: absolute;
                inset: 0;
                background-image: linear-gradient(rgba(255, 255, 255, .08) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, .08) 1px, transparent 1px);
                background-size: 48px 48px;
                -webkit-mask-image: linear-gradient(90deg, transparent, #000);
                mask-image: linear-gradient(90deg, transparent, #000);
            }

            .dg-cta-in {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 28px;
                flex-wrap: wrap;
            }

            .dg-cta h2 {
                color: #fff;
                font-size: clamp(24px, 3vw, 34px);
                margin-bottom: 8px;
            }

            .dg-cta p {
                color: #dbe8fb;
            }

            /* Partenaires */
            .dg-partners {
                display: flex;
                flex-wrap: wrap;
                gap: 16px;
            }

            .dg-partner {
                display: grid;
                place-items: center;
                width: 170px;
                height: 86px;
                padding: 14px 18px;
                background: #fff;
                border: 1px solid var(--dg-line);
                border-radius: 8px;
            }

            .dg-partner img {
                max-height: 100%;
                max-width: 100%;
                object-fit: contain;
            }

            a.dg-partner:hover {
                border-color: var(--dg-brand);
            }

            /* Une minute pour comprendre */
            .dg-explain {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 24px;
            }

            .dg-explain-item {
                display: flex;
                flex-direction: column;
                background: #fff;
                border: 1px solid var(--dg-line);
                border-radius: 8px;
                overflow: hidden;
                text-decoration: none;
                color: inherit;
            }

            .dg-explain-item:hover h3 {
                text-decoration: underline;
                text-underline-offset: 4px;
            }

            .dg-explain-item img {
                width: 100%;
                aspect-ratio: 16 / 10;
                object-fit: cover;
            }

            .dg-explain-body {
                padding: 18px 22px 22px;
            }

            .dg-explain-body h3 {
                font-size: 18px;
                margin-bottom: 8px;
            }

            .dg-explain-body p {
                color: var(--dg-muted);
                font-size: 15px;
            }

            /* Carte à la une avec photo en arrière-plan */
            .dg-news-feature {
                position: relative;
                overflow: hidden;
                isolation: isolate;
            }

            .dg-news-feature.has-img {
                animation: none;
            }

            .dg-news-feature.has-img::before {
                content: "";
                position: absolute;
                inset: 0;
                z-index: -2;
                background: var(--img) center / cover no-repeat;
                transition: transform .8s var(--dg-ease);
            }

            /* Voile bleu sombre pour garder le texte lisible */
            .dg-news-feature.has-img::after {
                content: "";
                position: absolute;
                inset: 0;
                z-index: -1;
                background: linear-gradient(180deg, rgba(6, 48, 122, .15) 0%, rgba(5, 20, 60, .9) 100%);
            }

            .dg-news-feature.has-img:hover::before {
                transform: scale(1.06);
            }

            /* ================= RESPONSIVE ================= */
            @media (max-width: 980px) {

                .dg-dg,
                .dg-about,
                .dg-explain,
                .dg-news-grid {
                    grid-template-columns: 1fr;
                    gap: 36px;
                }

                .dg-missions {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .dg-quick-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .dg-portrait {
                    max-width: 240px;
                }

                /* Hero : la forme blanche disparaît, l'image passe sous le texte dans un disque clair */
                .dg-hs-wave {
                    display: none;
                }

                .dg-hs-slide {
                    grid-template-columns: 1fr;
                    gap: 28px;
                    min-height: 0;
                }

                .dg-hs-vis {
                    justify-self: center;
                    width: min(100%, 320px);
                    padding: 18px;
                    border-radius: 50%;
                    background: #fff;
                }

                .dg-hs-img {
                    max-height: 260px;
                    border-radius: 12px;
                }

                .dg-radar {
                    width: 100%;
                }

                .dg-tag {
                    display: none;
                }

                .dg-hs-arrow {
                    display: none;
                }

                .dg-hs-dots {
                    bottom: 112px;
                }
            }

            @media (max-width: 700px) {
                .dg-section {
                    padding: 56px 0;
                }

                .dg-hero {
                    padding: 40px 0 150px;
                }

                .dg-quick-grid,
                .dg-missions {
                    grid-template-columns: 1fr;
                }

                .dg-news-feature {
                    min-height: 360px;
                    padding: 22px;
                }

                .dg-news-thumb {
                    width: 72px;
                    height: 56px;
                }

                .dg-news-go {
                    opacity: 1;
                    transform: none;
                }

                .dg-partner {
                    width: calc(50% - 8px);
                }

                .dg-hs-label {
                    font-size: 17px;
                }

            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function() {
                var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                /* ----- Slider du hero : défilement automatique, flèches, pastilles, balayage tactile ----- */
                var hero = document.getElementById('top');
                var slides = hero ? hero.querySelectorAll('.dg-hs-slide') : [];
                if (slides.length > 1) {
                    var dots = hero.querySelectorAll('.dg-hs-dots button');
                    var cur = 0,
                        timer = null;

                    function show(i) {
                        i = (i + slides.length) % slides.length;
                        slides.forEach(function(s, k) {
                            var on = k === i;
                            s.classList.toggle('is-on', on);
                            s.setAttribute('aria-hidden', on ? 'false' : 'true');
                        });
                        dots.forEach(function(d, k) {
                            d.classList.toggle('is-on', k === i);
                        });
                        cur = i;
                    }

                    function play() {
                        if (reduce) return;
                        stop();
                        timer = setInterval(function() {
                            if (!document.hidden) show(cur + 1);
                        }, 6500);
                    }

                    function stop() {
                        clearInterval(timer);
                    }

                    var prev = hero.querySelector('.dg-hs-prev'),
                        next = hero.querySelector('.dg-hs-next');
                    if (prev) prev.addEventListener('click', function() {
                        show(cur - 1);
                        play();
                    });
                    if (next) next.addEventListener('click', function() {
                        show(cur + 1);
                        play();
                    });
                    dots.forEach(function(d, k) {
                        d.addEventListener('click', function() {
                            show(k);
                            play();
                        });
                    });

                    hero.addEventListener('mouseenter', stop);
                    hero.addEventListener('mouseleave', play);
                    hero.addEventListener('focusin', stop);
                    hero.addEventListener('focusout', play);

                    var x0 = null;
                    hero.addEventListener('touchstart', function(e) {
                        x0 = e.touches[0].clientX;
                    }, {
                        passive: true
                    });
                    hero.addEventListener('touchend', function(e) {
                        if (x0 === null) return;
                        var dx = e.changedTouches[0].clientX - x0;
                        if (Math.abs(dx) > 50) {
                            show(cur + (dx < 0 ? 1 : -1));
                            play();
                        }
                        x0 = null;
                    }, {
                        passive: true
                    });
                    play();
                }

                /* ----- Compteurs animés ----- */
                var counters = document.querySelectorAll('[data-count]');
                if (counters.length && 'IntersectionObserver' in window && !reduce) {
                    var co = new IntersectionObserver(function(entries) {
                        entries.forEach(function(en) {
                            if (!en.isIntersecting) return;
                            var el = en.target,
                                target = +el.getAttribute('data-count'),
                                t0 = performance.now(),
                                d = 1700;
                            co.unobserve(el);
                            (function f(now) {
                                var p = Math.min(1, (now - t0) / d),
                                    e = 1 - Math.pow(1 - p, 3);
                                el.textContent = Math.round(target * e).toLocaleString('fr-FR');
                                if (p < 1) requestAnimationFrame(f);
                            })(t0);
                        });
                    }, {
                        threshold: .6
                    });
                    counters.forEach(function(el) {
                        el.textContent = '0';
                        co.observe(el);
                    });
                }

                /* ----- Texte qui se tape et s'efface ----- */
                var typed = document.getElementById('dgTyped');
                if (typed && !reduce) {
                    var words = [];
                    try {
                        words = JSON.parse(typed.getAttribute('data-words'));
                    } catch (e) {}
                    if (words.length) {
                        var wi = 0,
                            ci = 0,
                            del = false;
                        typed.textContent = '';
                        (function tick() {
                            var w = words[wi];
                            if (!del) {
                                ci++;
                                typed.textContent = w.slice(0, ci);
                                if (ci === w.length) {
                                    del = true;
                                    return setTimeout(tick, 1700);
                                }
                                return setTimeout(tick, 55);
                            }
                            ci--;
                            typed.textContent = w.slice(0, ci);
                            if (ci === 0) {
                                del = false;
                                wi = (wi + 1) % words.length;
                                return setTimeout(tick, 320);
                            }
                            setTimeout(tick, 28);
                        })();
                    }
                }

                /* ----- Réseau animé en fond du hero : nœuds, liaisons, paquets, réaction au curseur ----- */
                var canvas = document.getElementById('dgNet');
                if (!canvas || !canvas.getContext) return;
                var ctx = canvas.getContext('2d'),
                    area = canvas.parentElement;
                var w = 0,
                    h = 0,
                    pts = [],
                    packets = [],
                    mouse = {
                        x: -9999,
                        y: -9999
                    },
                    running = true,
                    frameCount = 0,
                    raf = 0,
                    LINK = 135;

                function resize() {
                    var dpr = Math.min(window.devicePixelRatio || 1, 2),
                        r = canvas.getBoundingClientRect();
                    w = r.width;
                    h = r.height;
                    canvas.width = w * dpr;
                    canvas.height = h * dpr;
                    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
                    var n = Math.min(70, Math.round(w * h / 15000));
                    pts = [];
                    for (var i = 0; i < n; i++) pts.push({
                        x: Math.random() * w,
                        y: Math.random() * h,
                        vx: (Math.random() - .5) * .35,
                        vy: (Math.random() - .5) * .35,
                        r: 1 + Math.random() * 1.6
                    });
                    if (reduce) draw();
                }

                function draw() {
                    ctx.clearRect(0, 0, w, h);
                    for (var i = 0; i < pts.length; i++) {
                        var a = pts[i];
                        for (var j = i + 1; j < pts.length; j++) {
                            var b = pts[j],
                                dx = a.x - b.x,
                                dy = a.y - b.y,
                                d = Math.sqrt(dx * dx + dy * dy);
                            if (d < LINK) {
                                ctx.strokeStyle = 'rgba(255,255,255,' + (0.2 * (1 - d / LINK)) + ')';
                                ctx.lineWidth = 1;
                                ctx.beginPath();
                                ctx.moveTo(a.x, a.y);
                                ctx.lineTo(b.x, b.y);
                                ctx.stroke();
                            }
                        }
                        var mx = a.x - mouse.x,
                            my = a.y - mouse.y,
                            md = Math.sqrt(mx * mx + my * my);
                        if (md < 170) {
                            ctx.strokeStyle = 'rgba(255,201,51,' + (0.55 * (1 - md / 170)) + ')';
                            ctx.beginPath();
                            ctx.moveTo(a.x, a.y);
                            ctx.lineTo(mouse.x, mouse.y);
                            ctx.stroke();
                        }
                    }
                    ctx.fillStyle = 'rgba(255,255,255,.7)';
                    for (var k = 0; k < pts.length; k++) {
                        ctx.beginPath();
                        ctx.arc(pts[k].x, pts[k].y, pts[k].r, 0, 6.2832);
                        ctx.fill();
                    }
                    for (var p = packets.length - 1; p >= 0; p--) {
                        var pk = packets[p],
                            x = pk.a.x + (pk.b.x - pk.a.x) * pk.t,
                            y = pk.a.y + (pk.b.y - pk.a.y) * pk.t;
                        ctx.fillStyle = pk.c;
                        ctx.shadowColor = pk.c;
                        ctx.shadowBlur = 12;
                        ctx.beginPath();
                        ctx.arc(x, y, 2.6, 0, 6.2832);
                        ctx.fill();
                        ctx.shadowBlur = 0;
                        if (!reduce) pk.t += 0.025;
                        if (pk.t >= 1) packets.splice(p, 1);
                    }
                }

                function step() {
                    if (!running) return;
                    frameCount++;
                    for (var i = 0; i < pts.length; i++) {
                        var q = pts[i];
                        q.x += q.vx;
                        q.y += q.vy;
                        if (q.x < 0 || q.x > w) q.vx *= -1;
                        if (q.y < 0 || q.y > h) q.vy *= -1;
                    }
                    if (frameCount % 28 === 0 && packets.length < 8 && pts.length > 1) {
                        var a = pts[Math.floor(Math.random() * pts.length)];
                        for (var t = 0; t < pts.length; t++) {
                            var b = pts[t],
                                dx = a.x - b.x,
                                dy = a.y - b.y;
                            if (b !== a && Math.sqrt(dx * dx + dy * dy) < LINK) {
                                var colors = ['#ffffff', '#ffc933', '#7fd8ff'];
                                packets.push({
                                    a: a,
                                    b: b,
                                    t: 0,
                                    c: colors[Math.floor(Math.random() * 3)]
                                });
                                break;
                            }
                        }
                    }
                    draw();
                    raf = requestAnimationFrame(step);
                }

                area.addEventListener('pointermove', function(e) {
                    var r = canvas.getBoundingClientRect();
                    mouse.x = e.clientX - r.left;
                    mouse.y = e.clientY - r.top;
                }, {
                    passive: true
                });
                area.addEventListener('pointerleave', function() {
                    mouse.x = mouse.y = -9999;
                });

                var rt;
                window.addEventListener('resize', function() {
                    clearTimeout(rt);
                    rt = setTimeout(resize, 150);
                });

                function setRunning(on) {
                    if (reduce || on === running) return;
                    running = on;
                    if (on) raf = requestAnimationFrame(step);
                    else cancelAnimationFrame(raf);
                }
                /* On ne dessine que si le hero est visible et l'onglet actif */
                if ('IntersectionObserver' in window) {
                    new IntersectionObserver(function(en) {
                        setRunning(en[0].isIntersecting && !document.hidden);
                    }).observe(area);
                }
                document.addEventListener('visibilitychange', function() {
                    setRunning(!document.hidden);
                });

                resize();
                if (!reduce) raf = requestAnimationFrame(step);
            })();
        </script>

        <script>
            (function() {
                var stage = document.querySelector('.dg-news-stage');
                if (!stage) return;
                var card = stage.querySelector('.dg-news-feature');
                var calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                var mouse = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
                if (!card || calm || !mouse) return;

                var MAX = 5,
                    raf = 0; // inclinaison maximale en degrés

                stage.addEventListener('pointermove', function(e) {
                    var r = card.getBoundingClientRect();
                    var x = (e.clientX - r.left) / r.width;
                    var y = (e.clientY - r.top) / r.height;
                    cancelAnimationFrame(raf);
                    raf = requestAnimationFrame(function() {
                        card.style.setProperty('--rx', ((.5 - y) * 2 * MAX).toFixed(2) + 'deg');
                        card.style.setProperty('--ry', ((x - .5) * 2 * MAX).toFixed(2) + 'deg');
                        card.style.setProperty('--mx', (x * 100).toFixed(1) + '%');
                        card.style.setProperty('--my', (y * 100).toFixed(1) + '%');
                    });
                }, {
                    passive: true
                });

                stage.addEventListener('pointerleave', function() {
                    cancelAnimationFrame(raf);
                    card.style.setProperty('--rx', '0deg');
                    card.style.setProperty('--ry', '0deg');
                });
            })();
        </script>
    @endpush
@endsection
