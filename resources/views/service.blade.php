@extends('layouts.site')

@section('title', 'Services — DGTI')
@section('description', 'Les services numériques et techniques de la Direction Générale des Transmissions et de
    l’Informatique : plateformes en ligne, services aux institutions et support.')

    {{--
    Variables attendues (toutes optionnelles) :
      $param_site : helpdesk_tel, helpdesk_url
    Les services sont écrits en dur dans le tableau $services ci-dessous.
    Pour en ajouter un : copier une ligne et adapter la catégorie, le titre, le texte, l'icône et le lien.
    Barre du haut, bandeau, menu et pied de page : layouts/site.blade.php.
--}}

    @php
        $tel = $param_site->helpdesk_tel ?? null;
        $helpUrl = $param_site->helpdesk_url ?? url('/contacts');

        $categories = [
            'public' => 'Pour le public',
            'institutions' => 'Pour les institutions',
            'support' => 'Support et ressources',
        ];

        // statut : 'En ligne' (lien actif), 'Opérationnel' (sans lien public) ou null
        // url : null = pas de bouton
        $services = [
            // ---- Pour le public ----
            [
                'cat' => 'public',
                'titre' => 'Fiche de voyage',
                'texte' => 'Remplissez votre fiche de voyage en ligne avant votre déplacement.',
                'statut' => 'En ligne',
                'url' => 'https://fichedevoyage.gov.bf',
                'externe' => true,
                'ico' =>
                    '<path d="M17.8 19.2 16 11l3.5-3.5C21 6 21 4 19 4c-1 0-1.5.5-3.5 2.5L11 11 2.8 9.2c-.5-.1-1 .1-1.2.6l-.3.5c-.2.4-.1.9.3 1.2L8 15l-3 3-2.5-.5c-.4-.1-.8.1-1 .5l-.2.4c-.2.4 0 .9.4 1.1L5 21l1.5 3.5c.2.4.7.6 1.1.4l.4-.2c.4-.2.6-.6.5-1L8 21l3-3 3.5 6c.3.4.8.5 1.2.3l.5-.3c.5-.2.7-.7.6-1.2z"/>',
            ],
            [
                'cat' => 'public',
                'titre' => 'SITAR',
                'texte' => 'Plateforme de gestion des activités réglementées.',
                'statut' => 'En ligne',
                'url' => 'https://activitesreglementees-04.msecu-test.gov.bf',
                'externe' => true,
                'ico' => '<path d="M12 3l2.6 5.6 6.1.7-4.5 4.2 1.2 6L12 16.5 6.6 19.5l1.2-6L3.3 9.3l6.1-.7z"/>',
            ],
            [
                'cat' => 'public',
                'titre' => 'DGPN',
                'texte' => 'Plateforme numérique en lien avec la DGPN.',
                'statut' => 'En ligne',
                'url' => 'https://msecu-test.gov.bf',
                'externe' => true,
                'ico' => '<path d="M12 3l8 3v6c0 4.5-3.2 8-8 9-4.8-1-8-4.5-8-9V6z"/><path d="M9 12l2 2 4-4"/>',
            ],
            [
                'cat' => 'public',
                'titre' => 'e-Contravention',
                'texte' =>
                    'Gestion numérique des contraventions, pour des démarches plus simples et plus transparentes.',
                'statut' => 'Opérationnel',
                'url' => null,
                'ico' => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/>',
            ],
            [
                'cat' => 'public',
                'titre' => 'Vidéo-verbalisation',
                'texte' => 'Constat des infractions à partir des images de vidéoprotection.',
                'statut' => 'Opérationnel',
                'url' => null,
                'ico' => '<path d="M15 10l5-3v10l-5-3z"/><rect x="3" y="6" width="12" height="12" rx="2"/>',
            ],

            // ---- Pour les institutions ----
            [
                'cat' => 'institutions',
                'titre' => 'Radiocommunication',
                'texte' => 'Liaison radio entre le cabinet, les états-majors et les forces de sécurité intérieure.',
                'statut' => null,
                'url' => null,
                'ico' =>
                    '<path d="M4.9 4.9a10 10 0 0 0 0 14.2M19.1 4.9a10 10 0 0 1 0 14.2M8.1 8.1a5.5 5.5 0 0 0 0 7.8M15.9 8.1a5.5 5.5 0 0 1 0 7.8"/><circle cx="12" cy="12" r="1.5"/>',
            ],
            [
                'cat' => 'institutions',
                'titre' => 'Réseaux et exploitation',
                'texte' => 'Exploitation et maintenance des installations de télécommunication et d’informatique.',
                'statut' => null,
                'url' => null,
                'ico' =>
                    '<circle cx="12" cy="5" r="2"/><circle cx="5" cy="19" r="2"/><circle cx="19" cy="19" r="2"/><path d="M12 7v4M12 11l-6 6M12 11l6 6"/>',
            ],
            [
                'cat' => 'institutions',
                'titre' => 'Équipement des services',
                'texte' => 'Stratégie d’équipement en matériel de communication, de surveillance et d’informatique.',
                'statut' => null,
                'url' => null,
                'ico' => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/>',
            ],
            [
                'cat' => 'institutions',
                'titre' => 'Schéma directeur informatique',
                'texte' => 'Élaboration et mise en œuvre du schéma directeur informatique du ministère.',
                'statut' => null,
                'url' => null,
                'ico' => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>',
            ],
            [
                'cat' => 'institutions',
                'titre' => 'Vidéoprotection',
                'texte' => 'Dispositif de vidéoprotection pour renforcer la sécurité des espaces publics.',
                'statut' => 'Opérationnel',
                'url' => null,
                'ico' => '<path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
            ],

            // ---- Support et ressources ----
            [
                'cat' => 'support',
                'titre' => 'Déclarer un incident',
                'texte' =>
                    'Un problème sur le réseau ou un équipement ? Le support de la DGTI prend votre demande en charge' .
                    ($tel ? ' : ' . $tel : '.'),
                'statut' => null,
                'url' => $helpUrl,
                'bouton' => 'Contacter le support',
                'ico' =>
                    '<path d="M3 14v-2a9 9 0 0 1 18 0v2"/><rect x="2" y="14" width="4" height="6" rx="1.5"/><rect x="18" y="14" width="4" height="6" rx="1.5"/><path d="M20 20c0 1.5-2 2-5 2"/>',
            ],
            [
                'cat' => 'support',
                'titre' => 'Documents et formulaires',
                'texte' => 'Textes, formulaires et rapports à télécharger.',
                'statut' => null,
                'url' => url('/documents'),
                'bouton' => 'Voir les documents',
                'ico' =>
                    '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h6"/>',
            ],
            [
                'cat' => 'support',
                'titre' => 'Proposer une collaboration',
                'texte' => 'Institution, partenaire ou prestataire : parlons de votre projet avec la DGTI.',
                'statut' => null,
                'url' => url('/contacts'),
                'bouton' => 'Nous contacter',
                'ico' => '<path d="M8 12l3 3 5-6"/><path d="M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18z"/>',
            ],
        ];

        $compte = collect($services)->countBy('cat');
    @endphp

@section('content')

    <!-- ===================== EN-TÊTE DE PAGE ===================== -->
    <section class="sv-hero">
        <div class="dg-wrap">
            <nav class="sv-crumbs" aria-label="Fil d’Ariane">
                <a href="{{ url('/') }}">Accueil</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">Services</span>
            </nav>
            <p class="sv-kicker">Nos services</p>
            <h1>Services</h1>
            <p class="sv-hero-text">
                Plateformes en ligne, services aux institutions et support : tout ce que la DGTI met à votre
                disposition, au même endroit.
            </p>
        </div>
    </section>

    <!-- ===================== OUTILS : RECHERCHE + FILTRES ===================== -->
    <section class="sv-tools" aria-label="Rechercher un service">
        <div class="dg-wrap sv-tools-in">
            <form class="sv-search" role="search" onsubmit="return false">
                <label for="svQ" class="dg-sr">Rechercher un service</label>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"
                    aria-hidden="true">
                    <circle cx="11" cy="11" r="7" />
                    <path d="M21 21l-4.3-4.3" />
                </svg>
                <input id="svQ" type="search" placeholder="Rechercher un service…" autocomplete="off">
            </form>

            <div class="sv-filters" role="group" aria-label="Filtrer par catégorie">
                <button type="button" class="is-on" data-filter="all" aria-pressed="true">
                    Tous <span>{{ count($services) }}</span>
                </button>
                @foreach ($categories as $key => $label)
                    <button type="button" data-filter="{{ $key }}" aria-pressed="false">
                        {{ $label }} <span>{{ $compte[$key] ?? 0 }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ===================== LISTE DES SERVICES ===================== -->
    <section class="sv-section">
        <div class="dg-wrap">
            <p class="sv-count dg-sr" id="svCount" aria-live="polite"></p>

            <div class="sv-grid" id="svGrid">
                @foreach ($services as $s)
                    <article class="sv-card dg-reveal" style="--d:{{ ($loop->index % 6) * 0.06 }}s"
                        data-cat="{{ $s['cat'] }}"
                        data-text="{{ \Illuminate\Support\Str::lower($s['titre'] . ' ' . $s['texte']) }}">
                        <div class="sv-card-top">
                            <span class="sv-ico" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                                    stroke-linecap="round" stroke-linejoin="round">{!! $s['ico'] !!}</svg>
                            </span>
                            @if (!empty($s['statut']))
                                <b class="sv-chip {{ $s['statut'] === 'En ligne' ? 'is-on' : '' }}">{{ $s['statut'] }}</b>
                            @endif
                        </div>

                        <p class="sv-cat">{{ $categories[$s['cat']] }}</p>
                        <h2>{{ $s['titre'] }}</h2>
                        <p class="sv-text">{{ $s['texte'] }}</p>

                        @if (!empty($s['url']))
                            <a href="{{ $s['url'] }}" class="dg-link sv-open"
                                @if (!empty($s['externe'])) target="_blank" rel="noopener" @endif>
                                {{ $s['bouton'] ?? 'Ouvrir la plateforme' }} <span aria-hidden="true">→</span>
                            </a>
                        @endif
                    </article>
                @endforeach
            </div>

            <p class="sv-empty" id="svEmpty" hidden>
                Aucun service ne correspond à votre recherche.
                <button type="button" id="svReset">Afficher tous les services</button>
            </p>
        </div>
    </section>

    <!-- ===================== SUPPORT ===================== -->
    <section class="sv-cta" aria-labelledby="svCtaTitle">
        <div class="dg-wrap sv-cta-in">
            <div>
                <h2 id="svCtaTitle">Un incident sur le réseau ou un équipement ?</h2>
                <p>Le support de la DGTI prend votre demande en charge{{ $tel ? ' : ' . $tel : '.' }}</p>
            </div>
            <a href="{{ $helpUrl }}" class="dg-btn sv-cta-btn">Contacter le support</a>
        </div>
    </section>

    @push('styles')
        <style>
            .sv-section {
                padding: 56px 0 88px;
            }

            /* ================= EN-TÊTE ================= */
            .sv-hero {
                position: relative;
                overflow: hidden;
                padding: 64px 0 72px;
                color: #fff;
                background-color: var(--dg-brand);
                background-image:
                    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='104' viewBox='0 0 120 104'%3E%3Cg fill='none' stroke='%23ffffff' stroke-opacity='.1' stroke-width='1.5'%3E%3Cpath d='M30 0l30 17v35L30 69 0 52V17zM90 35l30 17v35l-30 17-30-17V52z'/%3E%3C/g%3E%3C/svg%3E"),
                    linear-gradient(110deg, #06307a 0%, #0a4aa6 55%, #0d62c9 100%);
            }

            .sv-hero::after {
                content: "";
                position: absolute;
                left: 0;
                right: 0;
                bottom: 0;
                height: 6px;
                background: linear-gradient(90deg, var(--dg-sky), #69c4ff);
            }

            .sv-hero .dg-wrap {
                position: relative;
                z-index: 1;
            }

            .sv-crumbs {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 26px;
                font-size: 14px;
                color: #cfe0f7;
            }

            .sv-crumbs a {
                color: #fff;
                text-decoration: none;
            }

            .sv-crumbs a:hover {
                text-decoration: underline;
            }

            .sv-crumbs [aria-current] {
                color: #9fe8ff;
            }

            .sv-kicker {
                margin-bottom: 10px;
                font: 700 15px/1 var(--dg-sans);
                letter-spacing: .14em;
                text-transform: uppercase;
                color: #9fe8ff;
            }

            .sv-hero h1 {
                margin-bottom: 16px;
                color: #fff;
                font: 800 clamp(34px, 5vw, 56px)/1.08 var(--dg-sans);
                text-transform: uppercase;
                letter-spacing: -.01em;
            }

            .sv-hero-text {
                max-width: 58ch;
                padding-left: 16px;
                border-left: 3px solid #fff;
                font: 500 clamp(16px, 1.6vw, 20px)/1.55 var(--dg-sans);
                color: #eaf3ff;
            }

            /* ================= RECHERCHE ET FILTRES ================= */
            .sv-tools {
                position: relative;
                z-index: 5;
                background: #fff;
                border-bottom: 1px solid var(--dg-line);
                box-shadow: 0 14px 30px -26px rgba(5, 20, 60, .6);
            }

            .sv-tools-in {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
                flex-wrap: wrap;
                padding-block: 18px;
            }

            .sv-search {
                position: relative;
                flex: 0 1 340px;
            }

            .sv-search svg {
                position: absolute;
                left: 14px;
                top: 50%;
                width: 19px;
                height: 19px;
                margin-top: -9.5px;
                color: var(--dg-muted);
                pointer-events: none;
            }

            .sv-search input {
                width: 100%;
                height: 46px;
                padding: 0 16px 0 42px;
                font: inherit;
                font-size: 15px;
                color: var(--dg-ink);
                background: var(--dg-soft);
                border: 1px solid var(--dg-line);
                border-radius: 999px;
                transition: border-color .2s, background .2s, box-shadow .2s;
            }

            .sv-search input:focus {
                outline: none;
                background: #fff;
                border-color: var(--dg-sky);
                box-shadow: 0 0 0 4px rgba(30, 136, 229, .18);
            }

            .sv-filters {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .sv-filters button {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 11px 18px;
                font: 600 14.5px/1 var(--dg-sans);
                color: var(--dg-brand);
                background: #fff;
                border: 1px solid var(--dg-line);
                border-radius: 999px;
                transition: background .2s, color .2s, border-color .2s;
            }

            .sv-filters button span {
                min-width: 22px;
                padding: 3px 7px;
                font-size: 12px;
                text-align: center;
                color: var(--dg-brand);
                background: var(--dg-band);
                border-radius: 999px;
                transition: background .2s, color .2s;
            }

            .sv-filters button:hover {
                border-color: var(--dg-sky);
            }

            .sv-filters button.is-on {
                color: #fff;
                background: var(--dg-brand);
                border-color: var(--dg-brand);
            }

            .sv-filters button.is-on span {
                color: var(--dg-brand);
                background: #fff;
            }

            /* ================= CARTES ================= */
            .sv-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 22px;
            }

            .sv-card {
                position: relative;
                display: flex;
                flex-direction: column;
                padding: 26px 26px 24px;
                background: #fff;
                border: 1px solid var(--dg-line);
                border-top: 3px solid var(--dg-brand);
                border-radius: 12px;
                transition: transform .3s var(--dg-ease), box-shadow .3s, border-color .3s;
            }

            .sv-card:hover {
                transform: translateY(-6px);
                border-top-color: var(--dg-sky);
                box-shadow: 0 24px 44px -28px rgba(11, 42, 85, .55);
            }

            .sv-card[hidden] {
                display: none;
            }

            .sv-card-top {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                margin-bottom: 18px;
            }

            .sv-ico {
                display: grid;
                place-items: center;
                width: 52px;
                height: 52px;
                border-radius: 12px;
                background: var(--dg-brand);
                color: #fff;
                transition: transform .4s var(--dg-ease), background .3s;
            }

            .sv-ico svg {
                width: 26px;
                height: 26px;
            }

            .sv-card:hover .sv-ico {
                transform: rotate(-6deg) scale(1.06);
                background: var(--dg-sky);
            }

            .sv-chip {
                padding: 5px 11px;
                font: 600 12px/1.3 var(--dg-sans);
                color: var(--dg-brand);
                background: var(--dg-band);
                border: 1px solid #c9dcf3;
                border-radius: 999px;
            }

            .sv-chip.is-on {
                color: #fff;
                background: rgba(12, 143, 74, .92);
                border-color: #19d98b;
            }

            .sv-cat {
                margin-bottom: 6px;
                font: 700 12px/1 var(--dg-sans);
                letter-spacing: .1em;
                text-transform: uppercase;
                color: var(--dg-accent);
            }

            .sv-card h2 {
                margin-bottom: 8px;
                font-size: 20px;
            }

            .sv-text {
                flex: 1;
                color: var(--dg-muted);
                font-size: 15px;
            }

            .sv-open {
                align-self: flex-start;
                margin-top: 18px;
            }

            .sv-open span {
                display: inline-block;
                transition: transform .25s var(--dg-ease);
            }

            .sv-open:hover span {
                transform: translateX(4px);
            }

            .sv-empty {
                padding: 48px 0;
                text-align: center;
                color: var(--dg-muted);
                font-size: 17px;
            }

            .sv-empty[hidden] {
                display: none;
            }

            .sv-empty button {
                display: block;
                margin: 14px auto 0;
                padding: 11px 20px;
                font: 600 14.5px/1 var(--dg-sans);
                color: #fff;
                background: var(--dg-brand);
                border: 0;
                border-radius: 999px;
            }

            .sv-empty button:hover {
                background: var(--dg-sky);
            }

            /* ================= SUPPORT ================= */
            .sv-cta {
                position: relative;
                overflow: hidden;
                padding: 64px 0;
                color: #fff;
                background: linear-gradient(110deg, #06307a, #0a4aa6 60%, #0d62c9);
            }

            .sv-cta::before {
                content: "";
                position: absolute;
                inset: 0;
                background-image: linear-gradient(rgba(255, 255, 255, .08) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, .08) 1px, transparent 1px);
                background-size: 48px 48px;
                -webkit-mask-image: linear-gradient(90deg, transparent, #000);
                mask-image: linear-gradient(90deg, transparent, #000);
            }

            .sv-cta-in {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 28px;
                flex-wrap: wrap;
            }

            .sv-cta h2 {
                color: #fff;
                font-size: clamp(24px, 3vw, 34px);
                margin-bottom: 8px;
            }

            .sv-cta p {
                color: #dbe8fb;
            }

            .sv-cta-btn {
                padding: 14px 28px;
                color: var(--dg-brand);
                background: #fff;
                border-radius: 999px;
                font-weight: 700;
                text-transform: uppercase;
                font-size: 14px;
            }

            .sv-cta-btn:hover {
                background: var(--dg-amber);
                color: #1b1500;
                box-shadow: 0 0 0 5px rgba(242, 183, 5, .28);
            }

            /* ================= RESPONSIVE ================= */
            @media (max-width: 980px) {
                .sv-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .sv-search {
                    flex: 1 1 100%;
                }
            }

            @media (max-width: 700px) {
                .sv-hero {
                    padding: 44px 0 52px;
                }

                .sv-section {
                    padding: 40px 0 64px;
                }

                .sv-grid {
                    grid-template-columns: 1fr;
                }

                .sv-filters {
                    flex-wrap: nowrap;
                    overflow-x: auto;
                    padding-bottom: 4px;
                    margin-inline: -4px;
                }

                .sv-filters button {
                    flex-shrink: 0;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function() {
                var cards = Array.prototype.slice.call(document.querySelectorAll('.sv-card'));
                var buttons = document.querySelectorAll('.sv-filters button');
                var input = document.getElementById('svQ');
                var empty = document.getElementById('svEmpty');
                var count = document.getElementById('svCount');
                var reset = document.getElementById('svReset');
                var filter = 'all';

                function norm(s) {
                    return (s || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                }

                function apply() {
                    var q = norm(input.value.trim());
                    var shown = 0;
                    cards.forEach(function(c) {
                        var okCat = filter === 'all' || c.getAttribute('data-cat') === filter;
                        var okTxt = !q || norm(c.getAttribute('data-text')).indexOf(q) !== -1;
                        var ok = okCat && okTxt;
                        c.hidden = !ok;
                        if (ok) {
                            c.classList.add('is-in'); // visible même si l'apparition au défilement n'a pas eu lieu
                            shown++;
                        }
                    });
                    empty.hidden = shown !== 0;
                    count.textContent = shown + (shown > 1 ? ' services affichés' : ' service affiché');
                }

                buttons.forEach(function(b) {
                    b.addEventListener('click', function() {
                        filter = b.getAttribute('data-filter');
                        buttons.forEach(function(x) {
                            var on = x === b;
                            x.classList.toggle('is-on', on);
                            x.setAttribute('aria-pressed', on ? 'true' : 'false');
                        });
                        apply();
                    });
                });

                input.addEventListener('input', apply);
                if (reset) reset.addEventListener('click', function() {
                    input.value = '';
                    var all = document.querySelector('.sv-filters [data-filter="all"]');
                    if (all) all.click();
                    else apply();
                });
            })();
        </script>
    @endpush
@endsection
