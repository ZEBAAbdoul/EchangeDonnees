<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a3a8a">
    <title>@yield('title', 'SED — Système d’Échange d’Informations')</title>

    @php
        $nomSite = 'SED — Système d’Échange d’Informations';
        $descSite = 'SED, Système d’Échange d’Informations : plateformes en ligne, actualités, documents et contacts.';
        // Ligne affichée sous le nom dans le bandeau : à adapter
        $tutelle = $param_site->tutelle ?? 'Ministère de la Sécurité';
        $logo = !empty($param_site->logo_url) ? asset($param_site->logo_url) : null;

        $tel1 = $param_site->telephone ?? null;
        $email = trim($param_site->email_contact ?? ($param_site->email ?? '')) ?: null;
        // Si le champ contient plusieurs numéros séparés par une virgule, le lien tel: utilise le premier
        $telHref = fn($t) => preg_replace('/[^\d+]/', '', explode(',', $t)[0]);

        // Liens utiles : paramètre « liens_utiles » (titre + url), sinon liste vide (colonne masquée)
        $liensUtiles = !empty($param_site->liens_utiles)
            ? collect($param_site->liens_utiles)
                ->filter(fn($l) => !empty($l['titre']) && !empty($l['url']))
                ->map(fn($l) => [$l['titre'], $l['url']])
                ->values()
                ->all()
            : [];

        // État actif du menu
        $navAccueil = request()->is('/');
        $navPresentation = request()->is('qui-sommes-nous*', 'organigramme*', 'mission*', 'projets*');
        $navServices = request()->is('services*');
        $navActualites = request()->is('actualites*', 'toutes-les-actualites*');
        $navRessources = request()->is('documents*', 'phototheque*', 'videotheque*', 'galerie*');
        $navFaq = request()->is('faq*');
        $navContact = request()->is('contacts*', 'contact*');

        // Le champ telephone peut contenir plusieurs numéros séparés par « , », « / » ou « ; »
        $telephones = collect(preg_split('/\s*[,\/;]\s*/', (string) ($param_site->telephone ?? '')))
            ->map(fn($t) => trim($t))
            ->filter()
            ->values();
    @endphp

    <meta name="description" content="@yield('description', $descSite)">
    @if ($logo)
        <link rel="icon" href="{{ $logo }}">
    @endif
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:site_name" content="{{ $nomSite }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:title" content="@yield('title', $nomSite)">
    <meta property="og:description" content="@yield('description', $descSite)">
    @if ($logo)
        <meta property="og:image" content="{{ $logo }}">
        <meta property="og:image:alt" content="Logo du SED">
    @endif
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary">

    @stack('head')

    <script>
        document.documentElement.classList.add('js');
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Public+Sans:wght@400;500;600;700;800&family=Source+Serif+4:opsz,wght@8..60,500;8..60,600;8..60,700&display=swap"
        rel="stylesheet">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <style>
        /* Charte SED : modifiez ces variables pour changer toute la palette */
        :root {
            --sed-navy: #0b2a55;
            --sed-ink: #1b2536;
            --sed-muted: #566277;
            --sed-bg: #ffffff;
            --sed-soft: #f3f6fb;
            --sed-line: #dbe3ef;
            --sed-accent: #0a6fb8;
            --sed-red: #d8232a;
            --sed-amber: #f2b705;
            --sed-green: #0c8f4a;
            --sed-brand: #0a3a8a;
            --sed-brand-2: #0d56c2;
            --sed-sky: #1e88e5;
            --sed-band: #e7f0fb;
            --sed-serif: "Source Serif 4", Georgia, "Times New Roman", serif;
            --sed-sans: "Public Sans", system-ui, -apple-system, "Segoe UI", sans-serif;
            --sed-ease: cubic-bezier(.2, .7, .2, 1);
            --sed-cyan: #2fd8ff;
            --sed-blue: #4b7bff;
        }

        @property --a { syntax: "<angle>"; inherits: false; initial-value: 0deg; }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; scroll-padding-top: 80px; }

        body {
            margin: 0;
            font-family: var(--sed-sans);
            background: var(--sed-bg);
            color: var(--sed-ink);
            line-height: 1.65;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4 { margin: 0; font-family: var(--sed-serif); color: var(--sed-navy); line-height: 1.2; }
        p { margin: 0; }
        ul { margin: 0; padding: 0; list-style: none; }
        img { max-width: 100%; display: block; }
        a { color: var(--sed-accent); }
        button { font-family: inherit; cursor: pointer; }
        ::selection { background: var(--sed-brand); color: #fff; }
        :focus-visible { outline: 3px solid var(--sed-amber); outline-offset: 3px; }

        .sed-wrap { width: min(1160px, 100% - 40px); margin-inline: auto; }
        .sed-sr { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); }

        .skip-link { position: absolute; left: -999px; top: 0; z-index: 999; background: var(--sed-brand); color: #fff; padding: 10px 18px; font-weight: 600; }
        .skip-link:focus { left: 12px; top: 12px; }

        /* Barre de progression */
        .sed-progress {
            position: fixed; top: 0; left: 0; width: 100%; height: 3px; z-index: 100;
            transform-origin: left; transform: scaleX(0);
            background: linear-gradient(90deg, var(--sed-red) 0 33.3%, var(--sed-amber) 33.3% 66.6%, var(--sed-green) 66.6%);
        }

        /* Boutons et liens */
        .sed-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 12px 22px; font: 600 15px/1.2 var(--sed-sans); text-decoration: none;
            border: 2px solid transparent; border-radius: 6px;
            transition: background .2s, color .2s, border-color .2s, box-shadow .2s;
        }
        .sed-btn-primary { background: var(--sed-brand); color: #fff; }
        .sed-btn-primary:hover { background: var(--sed-brand-2); }
        .sed-btn-light { background: #fff; color: var(--sed-brand); border-color: var(--sed-brand); }
        .sed-btn-light:hover { background: var(--sed-brand); color: #fff; }
        .sed-link { font-weight: 600; color: var(--sed-accent); text-decoration: underline; text-underline-offset: 4px; text-decoration-thickness: 1px; }
        .sed-link:hover { text-decoration-thickness: 2px; }

        /* ================= BARRE DU HAUT, BANDEAU, DRAPEAU ================= */
        .sed-topzone { position: relative; }

        .sed-topbar { background: linear-gradient(90deg, #0a3a8a, #0b57b8 60%, #1685dc); color: #fff; font-size: 14.5px; }
        .sed-topbar-in {
            display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
            min-height: 54px; padding-block: 8px; padding-right: 130px;
        }

        .sed-social { display: flex; gap: 10px; }
        .sed-social a {
            display: grid; place-items: center; width: 38px; height: 38px; border-radius: 50%;
            background: #fff; color: var(--sed-brand); transition: transform .3s var(--sed-ease), background .2s;
        }
        .sed-social a:hover { transform: translateY(-3px) scale(1.06); background: var(--sed-amber); }
        .sed-social svg { width: 20px; height: 20px; }

        .sed-contact { display: flex; align-items: center; gap: 26px; flex-wrap: wrap; margin-left: auto; }
        .sed-contact a, .sed-contact span { display: inline-flex; align-items: center; gap: 8px; color: #fff; text-decoration: none; }
        .sed-contact a:hover { text-decoration: underline; }
        .sed-contact svg { width: 18px; height: 18px; opacity: .9; flex-shrink: 0; }

        .sed-band {
            background-color: var(--sed-band);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='104' viewBox='0 0 120 104'%3E%3Cg fill='none' stroke='%230a4aa6' stroke-opacity='.11' stroke-width='1.5'%3E%3Cpath d='M30 0l30 17v35L30 69 0 52V17zM90 35l30 17v35l-30 17-30-17V52z'/%3E%3C/g%3E%3C/svg%3E");
            border-bottom: 1px solid var(--sed-line);
        }
        .sed-band-in { display: flex; align-items: center; gap: 28px; min-height: 150px; padding-block: 14px; padding-right: 150px; }
        .sed-band-logo { flex-shrink: 0; text-decoration: none; }
        .sed-band-logo img { height: 90px; width: auto; }
        .sed-band-name {
            flex: 1; max-width: 780px; margin-inline: auto; text-align: center; color: var(--sed-brand);
            font: 600 clamp(15px, 1.7vw, 21px)/1.4 var(--sed-sans); text-transform: uppercase;
        }
        .sed-band-name small { display: block; margin-top: 6px; font-weight: 500; font-size: .72em; letter-spacing: .04em; color: var(--sed-muted); }

        /* Sigle (affiché si aucun logo) avec anneau lumineux */
        .sed-mono {
            position: relative; display: grid; place-items: center; width: 52px; height: 52px; flex-shrink: 0;
            border-radius: 13px; background: var(--sed-brand); color: #fff; font: 800 13.5px/1 var(--sed-sans);
        }
        .sed-mono::before {
            content: ""; position: absolute; inset: -2px; border-radius: 15px; padding: 2px;
            background: conic-gradient(from var(--a), var(--sed-cyan), var(--sed-blue), #19d98b, var(--sed-cyan));
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
            mask: linear-gradient(#000 0 0) content-box exclude, linear-gradient(#000 0 0);
            animation: sedRing 6s linear infinite;
        }
        @keyframes sedRing { to { --a: 360deg; } }
        .sed-band .sed-mono { width: 100px; height: 100px; border-radius: 20px; font-size: 26px; }
        .sed-band .sed-mono::before { border-radius: 22px; padding: 3px; }

        /* Drapeau accroché au bord droit (retirez le bloc <svg class="sed-flag"> pour le supprimer) */
        .sed-flag {
            position: absolute; top: 0; right: 0; z-index: 5; height: 100%; width: auto; aspect-ratio: 150 / 240;
            filter: drop-shadow(-6px 8px 10px rgba(5, 20, 60, .25));
            transform-origin: top right; animation: sedWave 7s ease-in-out infinite; pointer-events: none;
        }
        @keyframes sedWave { 0%, 100% { transform: skewY(0); } 50% { transform: skewY(1.4deg); } }
        .sed-flag-folds { transform-box: fill-box; transform-origin: center; animation: sedFolds 6s ease-in-out infinite; }
        @keyframes sedFolds { 50% { transform: translateX(-5px); } }

        /* ================= MENU ================= */
        .sed-header { position: sticky; top: 0; z-index: 60; background: var(--sed-brand); box-shadow: 0 8px 20px -12px rgba(5, 20, 60, .7); }
        .sed-header-in { display: flex; align-items: stretch; gap: 14px; min-height: 56px; position: relative; }

        .sed-nav-brand { display: none; align-items: center; gap: 10px; margin-right: 6px; color: #fff; text-decoration: none; font: 800 17px/1 var(--sed-sans); }
        .sed-nav-brand img { height: 34px; width: auto; background: #fff; border-radius: 6px; padding: 2px; }
        .sed-header.is-scrolled .sed-nav-brand { display: flex; animation: sedDrop .3s var(--sed-ease); }

        .sed-panel { display: flex; flex: 1; align-items: stretch; justify-content: space-between; gap: 14px; }
        .sed-nav > ul { display: flex; align-items: stretch; }
        .sed-nav li { position: relative; display: flex; }

        .sed-nav a, .sed-toplink {
            display: flex; align-items: center; gap: 6px; height: 56px; padding: 0 14px; color: #fff;
            background: none; border: 0; text-decoration: none; font: 600 14px/1 var(--sed-sans);
            text-transform: uppercase; letter-spacing: .01em; transition: background .2s;
        }
        .sed-nav a:hover, .sed-toplink:hover, .sed-nav li.open > .sed-toplink,
        .sed-nav .is-active > a, .sed-nav .is-active > .sed-toplink { background: var(--sed-sky); }

        .sed-chev { width: 10px; height: 10px; transition: transform .2s; }
        .sed-nav li.open .sed-chev { transform: rotate(180deg); }

        .sed-dropdown {
            display: none; position: absolute; top: 100%; left: 0; min-width: 250px; padding: 6px; background: #fff;
            border-top: 3px solid var(--sed-sky); border-radius: 0 0 10px 10px; box-shadow: 0 22px 44px -14px rgba(5, 20, 60, .55);
        }
        .sed-nav li.open > .sed-dropdown { display: block; animation: sedDrop .25s var(--sed-ease); }
        @keyframes sedDrop { from { opacity: 0; transform: translateY(-8px); } }

        .sed-nav .sed-dropdown a {
            display: block; height: auto; padding: 11px 14px; color: var(--sed-brand); background: none;
            border-radius: 6px; text-transform: none; font-weight: 600; font-size: 14.5px;
        }
        .sed-nav .sed-dropdown a:hover { background: #eaf3ff; }

        .sed-search { display: flex; align-self: center; }
        .sed-search input {
            width: 210px; height: 44px; padding: 0 16px; border: 0; border-radius: 6px 0 0 6px;
            font: inherit; font-size: 15px; color: var(--sed-ink); background: #fff;
        }
        .sed-search input:focus { outline: 3px solid var(--sed-amber); outline-offset: -3px; }
        .sed-search button {
            display: grid; place-items: center; width: 50px; height: 44px; border: 0; border-radius: 0 6px 6px 0;
            background: var(--sed-sky); color: #fff; transition: background .2s;
        }
        .sed-search button:hover { background: #3b9cf0; }
        .sed-search svg { width: 19px; height: 19px; }

        .sed-burger {
            display: none; width: 44px; height: 44px; margin: auto 0 auto auto; align-items: center; justify-content: center;
            background: rgba(255, 255, 255, .12); color: #fff; border: 1px solid rgba(255, 255, 255, .4); border-radius: 8px;
        }
        .sed-burger svg { width: 20px; height: 20px; }

        @media (max-width: 1100px) {
            .sed-nav-brand { display: flex; }
            .sed-burger { display: flex; }
            .sed-panel {
                display: none; position: absolute; top: 100%; left: -20px; right: -20px; flex-direction: column; gap: 12px;
                padding: 6px 20px 20px; background: var(--sed-brand); border-top: 1px solid rgba(255, 255, 255, .18);
                max-height: calc(100vh - 70px); overflow-y: auto;
            }
            .sed-panel.is-open { display: flex; animation: sedDrop .25s var(--sed-ease); }
            .sed-nav > ul { flex-direction: column; }
            .sed-nav li { flex-direction: column; }
            .sed-nav a, .sed-toplink { height: 48px; width: 100%; }
            .sed-dropdown { position: static; border: 0; border-radius: 0; box-shadow: none; background: rgba(255, 255, 255, .08); }
            .sed-nav .sed-dropdown a { color: #fff; }
            .sed-nav .sed-dropdown a:hover { background: rgba(255, 255, 255, .14); }
            .sed-search input { flex: 1; width: auto; }
            .sed-search { align-self: stretch; }
        }

        @media (max-width: 900px) {
            .sed-flag { display: none; }
            .sed-topbar-in { padding-right: 0; justify-content: center; }
            .sed-contact { margin-left: 0; justify-content: center; gap: 8px 20px; }
            .sed-band-in { padding-right: 0; min-height: 0; gap: 16px; flex-direction: column; text-align: center; }
            .sed-band-logo img { height: 84px; }
            .sed-band .sed-mono { width: 80px; height: 80px; font-size: 22px; }
        }

        /* ================= PIED DE PAGE ================= */
        .sed-footer { background: var(--sed-brand); color: #c9d9f2; }
        .sed-footer h2 { color: #fff; font-size: 20px; margin-bottom: 16px; }
        .sed-footer-grid { display: grid; grid-template-columns: 1.3fr 1fr; gap: 48px; padding: 56px 0 40px; }
        .sed-footer address { display: flex; flex-direction: column; gap: 7px; font-style: normal; }
        .sed-footer a { color: #fff; text-decoration: none; }
        .sed-footer a:hover { text-decoration: underline; }
        .sed-links { display: flex; flex-direction: column; gap: 9px; }
        .sed-copy {
            display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap; padding: 20px 0 28px;
            border-top: 1px solid rgba(255, 255, 255, .18); font-size: 14px;
        }
        @media (max-width: 900px) { .sed-footer-grid { grid-template-columns: 1fr; gap: 32px; } }

        .sed-top {
            position: fixed; right: 22px; bottom: 22px; z-index: 70; width: 46px; height: 46px; display: grid; place-items: center;
            color: #fff; background: var(--sed-brand); border-radius: 50%; box-shadow: 0 8px 20px -8px rgba(11, 42, 85, .6);
            opacity: 0; pointer-events: none; transition: opacity .25s;
        }
        .sed-top.is-on { opacity: 1; pointer-events: auto; }
        .sed-top svg { width: 18px; height: 18px; }

        /* Apparition douce : sans JavaScript, tout reste visible */
        .js .sed-reveal { opacity: 0; transform: translateY(22px); transition: opacity .8s var(--sed-ease), transform .8s var(--sed-ease); transition-delay: var(--d, 0s); }
        .js .sed-reveal.is-in { opacity: 1; transform: none; }

        @media (prefers-reduced-motion: reduce) {
            .js .sed-reveal { opacity: 1; transform: none; }
            html { scroll-behavior: auto; }
            *, *::before, *::after { transition: none !important; animation: none !important; }
        }
    </style>
    @stack('styles')
</head>

<body>
    <a class="skip-link" href="#main">Aller au contenu principal</a>
    <div class="sed-progress" id="sedProgress" aria-hidden="true"></div>

    <!-- ===================== BARRE DU HAUT + BANDEAU + DRAPEAU ===================== -->
    <div class="sed-topzone">
        <div class="sed-topbar">
            <div class="sed-wrap sed-topbar-in">
                <div class="sed-social">
                    @if (!empty($param_site->facebook_url))
                        <a href="{{ $param_site->facebook_url }}" target="_blank" rel="noopener" aria-label="Facebook">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12z" />
                            </svg>
                        </a>
                    @endif
                    @if (!empty($param_site->linkedin_url))
                        <a href="{{ $param_site->linkedin_url }}" target="_blank" rel="noopener" aria-label="LinkedIn">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M20.45 20.45h-3.56v-5.58c0-1.33-.02-3.04-1.85-3.04-1.86 0-2.14 1.45-2.14 2.94v5.68H9.34V9h3.42v1.56h.05c.48-.9 1.64-1.85 3.38-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.07 2.07 0 1 1 0-4.13 2.07 2.07 0 0 1 0 4.13zM7.12 20.45H3.56V9h3.56v11.45z" />
                            </svg>
                        </a>
                    @endif
                    @if (!empty($param_site->youtube_url))
                        <a href="{{ $param_site->youtube_url }}" target="_blank" rel="noopener" aria-label="YouTube">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M23 12s0-3.6-.46-5.3a2.9 2.9 0 0 0-2-2C18.9 4.2 12 4.2 12 4.2s-6.9 0-8.54.5a2.9 2.9 0 0 0-2 2C1 8.4 1 12 1 12s0 3.6.46 5.3a2.9 2.9 0 0 0 2 2c1.64.5 8.54.5 8.54.5s6.9 0 8.54-.5a2.9 2.9 0 0 0 2-2C23 15.6 23 12 23 12z" />
                                <path d="M9.75 15.5v-7l6 3.5-6 3.5z" fill="#fff" />
                            </svg>
                        </a>
                    @endif
                </div>

                <div class="sed-contact">
                    @if ($tel1)
                        <a href="tel:{{ $telHref($tel1) }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.34 1.79.66 2.65a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.43-1.27a2 2 0 0 1 2.11-.45c.86.32 1.75.54 2.65.66A2 2 0 0 1 22 16.92z" />
                            </svg>
                            {{ $tel1 }}
                        </a>
                    @endif
                    @if ($email)
                        <a href="mailto:{{ $email }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <rect x="2" y="5" width="20" height="14" rx="2" />
                                <path d="M2 7l10 7 10-7" />
                            </svg>
                            {{ $email }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="sed-band">
            <div class="sed-wrap sed-band-in">
                <a href="{{ url('/') }}" class="sed-band-logo" aria-label="Accueil">
                    @if ($logo)
                        <img src="{{ $logo }}" alt="Logo du SED">
                    @else
                        <span class="sed-mono" aria-hidden="true">SED</span>
                    @endif
                </a>
                <p class="sed-band-name">
                    Système d’Échange d’Informations
                    <small>{{ $tutelle }}</small>
                </p>
            </div>
        </div>

        {{-- Drapeau du Burkina Faso (étoile jaune sur fond rouge et vert), dessiné en SVG --}}
        <svg class="sed-flag" viewBox="0 0 150 240" aria-hidden="true">
            <defs>
                <clipPath id="sedFlagClip">
                    <path d="M58 0C52 38 30 70 26 118C22 165 8 202 0 240H150V0Z" />
                </clipPath>
                <linearGradient id="sedFlagShade" gradientUnits="userSpaceOnUse" x1="0" y1="0" x2="150" y2="0">
                    <stop offset="0" stop-color="#000" stop-opacity=".30" />
                    <stop offset=".14" stop-color="#fff" stop-opacity=".22" />
                    <stop offset=".3" stop-color="#000" stop-opacity=".2" />
                    <stop offset=".48" stop-color="#fff" stop-opacity=".2" />
                    <stop offset=".64" stop-color="#000" stop-opacity=".22" />
                    <stop offset=".82" stop-color="#fff" stop-opacity=".16" />
                    <stop offset="1" stop-color="#000" stop-opacity=".18" />
                </linearGradient>
                <linearGradient id="sedFlagDepth" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0" stop-color="#fff" stop-opacity=".1" />
                    <stop offset="1" stop-color="#000" stop-opacity=".22" />
                </linearGradient>
                <filter id="sedFlagBlur" x="-30%" y="-10%" width="160%" height="120%">
                    <feGaussianBlur stdDeviation="3.5" />
                </filter>
            </defs>

            <g clip-path="url(#sedFlagClip)">
                <rect width="150" height="125" fill="#e8262c" />
                <path d="M-10 119C25 108 55 130 95 119S140 112 160 121V250H-10Z" fill="#139a49" />
                <polygon fill="#fcd116" transform="translate(80 118) rotate(-14) skewY(6)"
                    points="0,-30 7.05,-9.71 28.53,-9.27 11.41,3.71 17.63,24.27 0,12 -17.63,24.27 -11.41,3.71 -28.53,-9.27 -7.05,-9.71" />

                <g class="sed-flag-folds" filter="url(#sedFlagBlur)" fill="none">
                    <path d="M92 0C84 40 60 76 52 124C44 172 30 208 20 240" stroke="#fff" stroke-opacity=".32" stroke-width="14" />
                    <path d="M112 0C104 44 82 80 74 128C66 176 52 212 42 240" stroke="#000" stroke-opacity=".22" stroke-width="16" />
                    <path d="M132 0C126 48 104 84 96 132C88 180 76 214 64 240" stroke="#fff" stroke-opacity=".24" stroke-width="12" />
                    <path d="M150 0C144 52 128 90 120 138C112 186 102 218 92 240" stroke="#000" stroke-opacity=".2" stroke-width="14" />
                </g>

                <rect width="150" height="240" fill="url(#sedFlagShade)" />
                <rect width="150" height="240" fill="url(#sedFlagDepth)" />
            </g>
        </svg>
    </div>

    <!-- ===================== MENU ===================== -->
    <header class="sed-header" id="sedHeader">
        <div class="sed-wrap sed-header-in">
            <a href="{{ url('/') }}" class="sed-nav-brand" aria-label="SED, accueil">
                @if ($logo)
                    <img src="{{ $logo }}" alt="">
                @endif
                SED
            </a>

            <button class="sed-burger" id="sedBurger" type="button" aria-label="Ouvrir le menu" aria-expanded="false"
                aria-controls="sedMenu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M3 6h18M3 12h18M3 18h18" />
                </svg>
            </button>

            <div class="sed-panel" id="sedMenu">
                <nav class="sed-nav" aria-label="Navigation principale">
                    <ul>
                        <li class="{{ $navAccueil ? 'is-active' : '' }}">
                            <a href="{{ url('/') }}" @if ($navAccueil) aria-current="page" @endif>Accueil</a>
                        </li>

                        <li class="{{ $navPresentation ? 'is-active' : '' }}">
                            <button class="sed-toplink" type="button" aria-expanded="false">Présentation
                                <svg class="sed-chev" viewBox="0 0 12 8" fill="none" stroke="currentColor"
                                    stroke-width="2" aria-hidden="true">
                                    <path d="M1 1l5 5 5-5" />
                                </svg>
                            </button>
                            <ul class="sed-dropdown">
                                <li><a href="{{ url('/qui-sommes-nous') }}">Qui sommes-nous ?</a></li>
                                <li><a href="{{ url('/organigramme') }}">Organigramme</a></li>
                                <li><a href="{{ url('/mission') }}#missions">Missions</a></li>
                                <li><a href="{{ url('/') }}#projets">Projets</a></li>
                            </ul>
                        </li>

                        <li class="{{ $navServices ? 'is-active' : '' }}">
                            <a href="{{ $param_site->services_url ?? url('/services') }}"
                                @if ($navServices) aria-current="page" @endif>Services</a>
                        </li>

                        <li class="{{ $navActualites ? 'is-active' : '' }}">
                            <a href="{{ url('/toutes-les-actualites') }}"
                                @if ($navActualites) aria-current="page" @endif>Actualité</a>
                        </li>

                        <li class="{{ $navRessources ? 'is-active' : '' }}">
                            <button class="sed-toplink" type="button" aria-expanded="false">Ressources
                                <svg class="sed-chev" viewBox="0 0 12 8" fill="none" stroke="currentColor"
                                    stroke-width="2" aria-hidden="true">
                                    <path d="M1 1l5 5 5-5" />
                                </svg>
                            </button>
                            <ul class="sed-dropdown">
                                <li><a href="{{ url('/documents') }}">Documents</a></li>
                                <li><a href="{{ url('/galerie') }}">Galerie</a></li>
                            </ul>
                        </li>

                        <li class="{{ $navFaq ? 'is-active' : '' }}">
                            <a href="{{ url('/faq') }}" @if ($navFaq) aria-current="page" @endif>FAQ</a>
                        </li>

                        <li class="{{ $navContact ? 'is-active' : '' }}">
                            <a href="{{ url('/contacts') }}"
                                @if ($navContact) aria-current="page" @endif>Contacts</a>
                        </li>
                    </ul>
                </nav>

                <form class="sed-search" role="search" action="{{ url('/recherche') }}" method="GET">
                    <label for="sed-q" class="sed-sr">Rechercher sur le site</label>
                    <input id="sed-q" type="search" name="q" placeholder="Recherche…">
                    <button type="submit" aria-label="Lancer la recherche">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                            stroke-linecap="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="7" />
                            <path d="M21 21l-4.3-4.3" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main id="main">
        @yield('content')
    </main>

    <!-- ===================== PIED DE PAGE ===================== -->
    <footer id="contact" class="sed-footer">
        <div class="sed-wrap sed-footer-grid" @if (empty($liensUtiles)) style="grid-template-columns: 1fr;" @endif>
            <div>
                <h2>Infos contact</h2>
                <address>
                    <span>{{ $param_site->adresse ?? 'Ouagadougou, Burkina Faso' }}</span>
                    @foreach ($telephones as $tel)
                        <a href="tel:{{ preg_replace('/[^\d+]/', '', $tel) }}">{{ $tel }}</a>
                    @endforeach
                    @if ($email)
                        <a href="mailto:{{ $email }}">{{ $email }}</a>
                    @endif
                </address>
            </div>

            @if (!empty($liensUtiles))
                <div>
                    <h2>Liens utiles</h2>
                    <ul class="sed-links">
                        @foreach ($liensUtiles as [$label, $href])
                            <li><a href="{{ $href }}" target="_blank" rel="noopener">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="sed-wrap sed-copy">
            <p>© {{ date('Y') }} SED — Système d’Échange d’Informations. Tous droits réservés.</p>
            <a href="{{ url('/mentions_legale') }}">Mentions légales</a>
        </div>
    </footer>

    <a href="#main" class="sed-top" id="sedTop" aria-label="Haut de page">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M12 19V5M5 12l7-7 7 7" />
        </svg>
    </a>

    <script>
        (function() {
            var panel = document.getElementById('sedMenu');
            var burger = document.getElementById('sedBurger');
            var header = document.getElementById('sedHeader');
            var topBtn = document.getElementById('sedTop');
            var bar = document.getElementById('sedProgress');

            function closeAll() {
                document.querySelectorAll('.sed-nav li.open').forEach(function(li) {
                    li.classList.remove('open');
                    var b = li.querySelector('.sed-toplink');
                    if (b) b.setAttribute('aria-expanded', 'false');
                });
            }

            document.querySelectorAll('.sed-toplink').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    var li = btn.closest('li');
                    var wasOpen = li.classList.contains('open');
                    closeAll();
                    if (!wasOpen) {
                        li.classList.add('open');
                        btn.setAttribute('aria-expanded', 'true');
                    }
                });
            });

            document.addEventListener('click', closeAll);
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAll();
                    panel.classList.remove('is-open');
                    burger.setAttribute('aria-expanded', 'false');
                }
            });

            burger.addEventListener('click', function(e) {
                e.stopPropagation();
                var open = panel.classList.toggle('is-open');
                burger.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
            panel.addEventListener('click', function(e) { e.stopPropagation(); });

            // Le menu devient « collé » en haut : on y affiche alors le sigle SED
            var ticking = false;

            function onScroll() {
                var y = window.scrollY;
                var h = document.documentElement.scrollHeight - window.innerHeight;
                bar.style.transform = 'scaleX(' + (h > 0 ? Math.min(1, y / h) : 0) + ')';
                header.classList.toggle('is-scrolled', y > 210);
                topBtn.classList.toggle('is-on', y > 600);
                ticking = false;
            }
            window.addEventListener('scroll', function() {
                if (ticking) return;
                ticking = true;
                requestAnimationFrame(onScroll);
            }, { passive: true });
            onScroll();

            var reveals = document.querySelectorAll('.sed-reveal');
            if ('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                var io = new IntersectionObserver(function(entries) {
                    entries.forEach(function(en) {
                        if (en.isIntersecting) {
                            en.target.classList.add('is-in');
                            io.unobserve(en.target);
                        }
                    });
                }, { threshold: .12, rootMargin: '0px 0px -6% 0px' });
                reveals.forEach(function(el) { io.observe(el); });
            } else {
                reveals.forEach(function(el) { el.classList.add('is-in'); });
            }
        })();
    </script>
    @stack('scripts')
</body>

</html>