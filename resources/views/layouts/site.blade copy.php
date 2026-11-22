<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#050b17">
    <title>@yield('title', 'DGTI — Direction Générale des Transmissions et de l’Informatique')</title>

    @php
        $nomSite = 'DGTI — Direction Générale des Transmissions et de l’Informatique';
        $descSite = 'Site de la Direction Générale des Transmissions et de l’Informatique du Ministère de la Sécurité : plateformes en ligne, actualités, documents et contacts.';
        $logo = !empty($param_site->logo_url) ? asset($param_site->logo_url) : null;

        $tel1 = $param_site->telephone ?? null;
        $tel2 = $param_site->telephone2 ?? null;
        $email = $param_site->email ?? null;
        $telHref = fn($t) => preg_replace('/[^\d+]/', '', $t);

        $liensUtiles = !empty($param_site->liens_utiles)
            ? collect($param_site->liens_utiles)->filter(fn($l) => !empty($l['titre']) && !empty($l['url']))->map(fn($l) => [$l['titre'], $l['url']])->values()->all()
            : [
                ['ANPTIC', 'https://anptic.gov.bf'],
                ['Ministère de la Transition digitale', 'https://www.mdenp.gov.bf/'],
                ['ARCEP', 'https://www.arcep.bf/'],
                ['ANSSI', 'https://anssi.bf/'],
                ['Service public', 'https://service-public.gov.bf/'],
            ];

        // État actif du menu
        $navAccueil = request()->is('/');
        $navDirection = request()->is('qui-sommes-nous*', 'organigramme*', 'missions*', 'projets*', 'mot-du-directeur-general*');
        $navServices = request()->is('services*');
        $navActualites = request()->is('actualites*');
        $navRessources = request()->is('documents*', 'phototheque*', 'videotheque*');
        $navFaq = request()->is('faq*');
        $navContact = request()->is('contacts*', 'contact*');
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
        <meta property="og:image:alt" content="Logo de la DGTI">
    @endif
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary">

    @stack('head')

    {{-- Sans JavaScript, tout reste visible : la classe "js" active les animations d'entrée. --}}
    <script>
        document.documentElement.classList.add('js');
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500&family=Sora:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        @property --a {
            syntax: "<angle>";
            inherits: false;
            initial-value: 0deg;
        }

        :root {
            --dg-bg: #050b17;
            --dg-bg2: #0a1630;
            --dg-panel: rgba(255, 255, 255, .045);
            --dg-panel-strong: rgba(255, 255, 255, .08);
            --dg-line: rgba(120, 170, 255, .16);
            --dg-line-strong: rgba(47, 216, 255, .45);
            --dg-text: #e8f0ff;
            --dg-muted: #9bb0cc;
            --dg-cyan: #2fd8ff;
            --dg-blue: #4b7bff;
            --dg-green: #19d98b;
            --dg-amber: #ffc933;
            --dg-red: #ff4d5e;
            --dg-ease: cubic-bezier(.2, .7, .2, 1);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 90px;
        }

        body {
            margin: 0;
            font-family: "Sora", system-ui, sans-serif;
            background: var(--dg-bg);
            color: var(--dg-text);
            line-height: 1.65;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        /* Grille technique en fond, estompée vers le bas */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            background-image:
                linear-gradient(var(--dg-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--dg-line) 1px, transparent 1px);
            background-size: 56px 56px;
            opacity: .32;
            -webkit-mask-image: radial-gradient(ellipse 80% 60% at 50% 0%, #000 0%, transparent 78%);
            mask-image: radial-gradient(ellipse 80% 60% at 50% 0%, #000 0%, transparent 78%);
        }

        .dg-topbar,
        .dg-flagband,
        .dg-header,
        main,
        .dg-footer {
            position: relative;
            z-index: 1;
        }

        .dg-header {
            z-index: 60;
        }

        h1,
        h2,
        h3,
        h4 {
            color: var(--dg-text);
            margin: 0;
            line-height: 1.15;
            letter-spacing: -.015em;
        }

        p {
            margin: 0;
        }

        ul {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        img {
            max-width: 100%;
            display: block;
        }

        a {
            color: inherit;
        }

        button {
            font-family: inherit;
            cursor: pointer;
        }

        ::selection {
            background: var(--dg-cyan);
            color: #04101f;
        }

        :focus-visible {
            outline: 3px solid var(--dg-amber);
            outline-offset: 3px;
        }

        .dg-wrap {
            width: min(1160px, 100% - 40px);
            margin-inline: auto;
        }

        .dg-sr {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0 0 0 0);
        }

        .skip-link {
            position: absolute;
            left: -999px;
            top: 0;
            background: var(--dg-cyan);
            color: #04101f;
            padding: 10px 18px;
            z-index: 999;
            font-weight: 700;
        }

        .skip-link:focus {
            left: 12px;
            top: 12px;
        }

        /* ---------- Barre de progression de lecture (tricolore) ---------- */
        .dg-progress {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            z-index: 100;
            transform-origin: left;
            transform: scaleX(0);
            background: linear-gradient(90deg, var(--dg-red) 0 33.3%, var(--dg-amber) 33.3% 66.6%, var(--dg-green) 66.6%);
            box-shadow: 0 0 12px rgba(47, 216, 255, .6);
        }

        /* ---------- Bande tricolore animée ---------- */
        .dg-flagband {
            display: flex;
            height: 4px;
            overflow: hidden;
        }

        .dg-flagband i {
            flex: 1;
        }

        .dg-flagband i:nth-child(1) {
            background: var(--dg-red);
        }

        .dg-flagband i:nth-child(2) {
            background: var(--dg-amber);
        }

        .dg-flagband i:nth-child(3) {
            background: var(--dg-green);
        }

        .dg-flagband::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(100deg, transparent 30%, rgba(255, 255, 255, .75) 50%, transparent 70%);
            transform: translateX(-100%);
            animation: dgSweep 5s ease-in-out 1s infinite;
        }

        @keyframes dgSweep {
            0% {
                transform: translateX(-100%);
            }

            40%,
            100% {
                transform: translateX(100%);
            }
        }

        /* ---------- Boutons et liens ---------- */
        .dg-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 14px 26px;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            border: 1px solid transparent;
            border-radius: 999px;
            overflow: hidden;
            cursor: pointer;
            font-family: inherit;
            transition: transform .25s var(--dg-ease), box-shadow .25s var(--dg-ease), background .25s, border-color .25s, color .25s;
        }

        .dg-btn::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(110deg, transparent 35%, rgba(255, 255, 255, .45) 50%, transparent 65%);
            transform: translateX(-120%);
        }

        .dg-btn:hover::after {
            transform: translateX(120%);
            transition: transform .7s var(--dg-ease);
        }

        .dg-btn-primary {
            background: linear-gradient(135deg, var(--dg-cyan), var(--dg-blue));
            color: #04101f;
            box-shadow: 0 0 0 0 rgba(47, 216, 255, .5), 0 10px 30px -10px rgba(47, 216, 255, .6);
        }

        .dg-btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 0 0 4px rgba(47, 216, 255, .18), 0 16px 36px -10px rgba(47, 216, 255, .75);
        }

        .dg-btn-light {
            background: var(--dg-panel);
            border-color: var(--dg-line-strong);
            color: var(--dg-text);
            backdrop-filter: blur(6px);
        }

        .dg-btn-light:hover {
            transform: translateY(-3px);
            background: rgba(47, 216, 255, .12);
            border-color: var(--dg-cyan);
        }

        .dg-link {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--dg-cyan);
            font-weight: 600;
            text-decoration: none;
        }

        .dg-link::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: -4px;
            height: 1px;
            background: currentColor;
            transform: scaleX(.25);
            transform-origin: left;
            transition: transform .3s var(--dg-ease);
        }

        .dg-link:hover::after {
            transform: scaleX(1);
        }

        .dg-link .dg-arrow {
            transition: transform .3s var(--dg-ease);
        }

        .dg-link:hover .dg-arrow {
            transform: translateX(5px);
        }

        /* ---------- Surfaces en verre avec spot lumineux ---------- */
        .dg-glass {
            position: relative;
            background: var(--dg-panel);
            border: 1px solid var(--dg-line);
            border-radius: 16px;
            backdrop-filter: blur(10px);
            overflow: hidden;
            transition: border-color .3s, transform .35s var(--dg-ease);
        }

        .dg-glass::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(260px circle at var(--mx, 50%) var(--my, 50%), rgba(47, 216, 255, .17), transparent 70%);
            opacity: 0;
            transition: opacity .3s;
            pointer-events: none;
        }

        .dg-glass:hover {
            border-color: var(--dg-line-strong);
        }

        .dg-glass:hover::before {
            opacity: 1;
        }

        /* ---------- Apparition au défilement ---------- */
        .js .dg-reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity .9s var(--dg-ease), transform .9s var(--dg-ease);
            transition-delay: var(--d, 0s);
        }

        .js .dg-reveal.is-in {
            opacity: 1;
            transform: none;
        }

        /* ---------- Barre du haut ---------- */
        .dg-topbar {
            background: #030811;
            color: var(--dg-muted);
            font-size: 13px;
            border-bottom: 1px solid var(--dg-line);
        }

        .dg-topbar-in {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            padding: 8px 0;
        }

        .dg-topbar ul {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .dg-topbar a {
            color: var(--dg-muted);
            text-decoration: none;
            transition: color .2s;
        }

        .dg-topbar a:hover {
            color: var(--dg-cyan);
        }

        .dg-live {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: "IBM Plex Mono", monospace;
            color: var(--dg-green);
        }

        .dg-live i {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--dg-green);
            box-shadow: 0 0 0 0 rgba(25, 217, 139, .6);
            animation: dgPing 2s ease-out infinite;
        }

        @keyframes dgPing {
            70% {
                box-shadow: 0 0 0 9px rgba(25, 217, 139, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(25, 217, 139, 0);
            }
        }

        /* ---------- En-tête et menu ---------- */
        .dg-header {
            position: sticky;
            top: 0;
            background: rgba(5, 11, 23, .66);
            backdrop-filter: blur(16px) saturate(140%);
            -webkit-backdrop-filter: blur(16px) saturate(140%);
            border-bottom: 1px solid var(--dg-line);
            transition: background .3s;
        }

        .dg-header.is-scrolled {
            background: rgba(5, 11, 23, .92);
        }

        .dg-header-in {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            padding: 12px 0;
            position: relative;
            transition: padding .3s var(--dg-ease);
        }

        .dg-header.is-scrolled .dg-header-in {
            padding: 7px 0;
        }

        .dg-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            color: var(--dg-text);
            max-width: 430px;
        }

        .dg-brand img {
            height: 50px;
            width: auto;
            background: #fff;
            border-radius: 10px;
            padding: 4px;
        }

        .dg-mono {
            position: relative;
            display: grid;
            place-items: center;
            width: 52px;
            height: 52px;
            border-radius: 13px;
            background: #07142b;
            font-weight: 800;
            font-size: 13.5px;
            flex-shrink: 0;
        }

        .dg-mono::before {
            content: "";
            position: absolute;
            inset: -2px;
            border-radius: 15px;
            padding: 2px;
            background: conic-gradient(from var(--a), var(--dg-cyan), var(--dg-blue), var(--dg-green), var(--dg-cyan));
            -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor;
            mask: linear-gradient(#000 0 0) content-box exclude, linear-gradient(#000 0 0);
            animation: dgRing 6s linear infinite;
        }

        @keyframes dgRing {
            to {
                --a: 360deg;
            }
        }

        .dg-brand-name {
            font-weight: 600;
            font-size: 14.5px;
            line-height: 1.3;
        }

        .dg-brand-name small {
            display: block;
            font-weight: 400;
            font-size: 12.5px;
            color: var(--dg-muted);
            margin-top: 2px;
        }

        .dg-nav>ul {
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .dg-nav li {
            position: relative;
        }

        .dg-nav a,
        .dg-toplink {
            position: relative;
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 13px;
            color: var(--dg-muted);
            background: none;
            border: 0;
            font-weight: 500;
            font-size: 14.5px;
            text-decoration: none;
            transition: color .2s;
        }

        .dg-nav>ul>li>a::after,
        .dg-nav>ul>li>.dg-toplink::after {
            content: "";
            position: absolute;
            left: 13px;
            right: 13px;
            bottom: 2px;
            height: 2px;
            border-radius: 2px;
            background: var(--dg-cyan);
            box-shadow: 0 0 10px var(--dg-cyan);
            transform: scaleX(0);
            transition: transform .3s var(--dg-ease);
        }

        .dg-nav a:hover,
        .dg-toplink:hover,
        .dg-nav li.open>.dg-toplink {
            color: var(--dg-text);
        }

        .dg-nav>ul>li>a:hover::after,
        .dg-nav>ul>li>.dg-toplink:hover::after,
        .dg-nav>ul>li.is-active>a::after,
        .dg-nav>ul>li.is-active>.dg-toplink::after {
            transform: scaleX(1);
        }

        .dg-nav .is-active>a,
        .dg-nav .is-active>.dg-toplink {
            color: var(--dg-cyan);
        }

        .dg-chev {
            width: 10px;
            height: 10px;
            transition: transform .2s;
        }

        .dg-nav li.open .dg-chev {
            transform: rotate(180deg);
        }

        .dg-dropdown {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            min-width: 230px;
            padding: 8px;
            background: rgba(8, 18, 42, .96);
            backdrop-filter: blur(14px);
            border: 1px solid var(--dg-line-strong);
            border-radius: 14px;
            box-shadow: 0 20px 50px -12px rgba(0, 0, 0, .7), 0 0 30px -10px rgba(47, 216, 255, .35);
        }

        .dg-nav li.open>.dg-dropdown {
            display: block;
            animation: dgDrop .25s var(--dg-ease);
        }

        @keyframes dgDrop {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
        }

        .dg-dropdown a {
            border-radius: 9px;
            font-size: 14px;
        }

        .dg-dropdown a:hover {
            background: rgba(47, 216, 255, .1);
            color: var(--dg-cyan);
        }

        .dg-burger {
            display: none;
            width: 44px;
            height: 44px;
            align-items: center;
            justify-content: center;
            background: var(--dg-panel);
            color: var(--dg-text);
            border: 1px solid var(--dg-line-strong);
            border-radius: 12px;
        }

        .dg-burger svg {
            width: 20px;
            height: 20px;
        }

        @media (max-width: 980px) {
            .dg-burger {
                display: flex;
            }

            .dg-nav {
                display: none;
                position: absolute;
                top: 100%;
                left: -20px;
                right: -20px;
                background: rgba(5, 11, 23, .97);
                border-bottom: 1px solid var(--dg-line-strong);
                padding: 8px 20px 18px;
                max-height: calc(100vh - 120px);
                overflow-y: auto;
            }

            .dg-nav.is-open {
                display: block;
                animation: dgDrop .25s var(--dg-ease);
            }

            .dg-nav>ul {
                flex-direction: column;
                align-items: stretch;
            }

            .dg-nav>ul>li>a::after,
            .dg-nav>ul>li>.dg-toplink::after {
                display: none;
            }

            .dg-dropdown {
                position: static;
                border: 0;
                box-shadow: none;
                background: transparent;
                padding: 0 0 6px 14px;
            }

            .dg-nav .is-active>a,
            .dg-nav .is-active>.dg-toplink {
                box-shadow: inset 3px 0 0 var(--dg-cyan);
            }
        }

        @media (max-width: 640px) {
            .dg-brand-name small {
                display: none;
            }

            .dg-brand-name {
                font-size: 13px;
            }
        }

        /* ---------- Pied de page ---------- */
        .dg-footer {
            background: #030811;
            color: var(--dg-muted);
            border-top: 1px solid var(--dg-line);
        }

        .dg-footer h2 {
            color: var(--dg-text);
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .dg-footer-grid {
            display: grid;
            grid-template-columns: 1.3fr 1fr 1fr;
            gap: 48px;
            padding: 56px 0 40px;
        }

        .dg-footer address {
            display: flex;
            flex-direction: column;
            gap: 7px;
            font-style: normal;
        }

        .dg-footer a {
            color: var(--dg-text);
            text-decoration: none;
        }

        .dg-footer a:hover {
            color: var(--dg-cyan);
        }

        .dg-news-form {
            display: flex;
            gap: 8px;
            margin-top: 14px;
            flex-wrap: wrap;
        }

        .dg-news-form input {
            flex: 1;
            min-width: 190px;
            padding: 13px 18px;
            color: var(--dg-text);
            background: var(--dg-panel-strong);
            border: 1px solid var(--dg-line);
            border-radius: 999px;
            font: inherit;
            font-size: 14.5px;
        }

        .dg-news-form input::placeholder {
            color: var(--dg-muted);
        }

        .dg-news-form input:focus {
            outline: none;
            border-color: var(--dg-cyan);
            box-shadow: 0 0 0 3px rgba(47, 216, 255, .2);
        }

        .dg-links {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .dg-links a {
            display: inline-block;
            transition: transform .25s var(--dg-ease), color .2s;
        }

        .dg-links a:hover {
            transform: translateX(6px);
        }

        .dg-copy {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            padding: 20px 0 28px;
            border-top: 1px solid var(--dg-line);
            font-size: 13.5px;
        }

        @media (max-width: 900px) {
            .dg-footer-grid {
                grid-template-columns: 1fr;
                gap: 32px;
            }
        }

        /* ---------- Retour en haut ---------- */
        .dg-top {
            position: fixed;
            right: 22px;
            bottom: 22px;
            z-index: 70;
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            color: var(--dg-cyan);
            background: rgba(8, 18, 42, .85);
            border: 1px solid var(--dg-line-strong);
            border-radius: 50%;
            backdrop-filter: blur(8px);
            opacity: 0;
            transform: translateY(16px);
            pointer-events: none;
            transition: opacity .3s, transform .3s var(--dg-ease), box-shadow .3s;
        }

        .dg-top.is-on {
            opacity: 1;
            transform: none;
            pointer-events: auto;
        }

        .dg-top:hover {
            box-shadow: 0 0 22px rgba(47, 216, 255, .5);
        }

        .dg-top svg {
            width: 18px;
            height: 18px;
        }

        /* ---------- Mouvement réduit : tout reste visible, rien ne bouge ---------- */
        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            *,
            *::before,
            *::after {
                animation: none !important;
                transition: none !important;
            }

            .js .dg-reveal {
                opacity: 1;
                transform: none;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <a class="skip-link" href="#main">Aller au contenu principal</a>
    <div class="dg-progress" id="dgProgress" aria-hidden="true"></div>

    <!-- ===================== BARRE DU HAUT ===================== -->
    <div class="dg-topbar">
        <div class="dg-wrap dg-topbar-in">
            <ul>
                <li class="dg-live"><i></i> Portail en ligne</li>
                @if ($tel1)
                    <li><a href="tel:{{ $telHref($tel1) }}">{{ $tel1 }}</a></li>
                @endif
                @if ($email)
                    <li><a href="mailto:{{ $email }}">{{ $email }}</a></li>
                @endif
            </ul>
            <ul>
                @if (!empty($param_site->facebook_url))
                    <li><a href="{{ $param_site->facebook_url }}" target="_blank" rel="noopener">Facebook</a></li>
                @endif
                @if (!empty($param_site->linkedin_url))
                    <li><a href="{{ $param_site->linkedin_url }}" target="_blank" rel="noopener">LinkedIn</a></li>
                @endif
                @if (!empty($param_site->youtube_url))
                    <li><a href="{{ $param_site->youtube_url }}" target="_blank" rel="noopener">YouTube</a></li>
                @endif
            </ul>
        </div>
    </div>
    <div class="dg-flagband" aria-hidden="true"><i></i><i></i><i></i></div>

    <!-- ===================== EN-TÊTE ===================== -->
    <header class="dg-header" id="dgHeader">
        <div class="dg-wrap dg-header-in">
            <a href="{{ url('/') }}" class="dg-brand">
                @if ($logo)
                    <img src="{{ $logo }}" alt="Logo de la DGTI">
                @else
                    <span class="dg-mono" aria-hidden="true">DGTI</span>
                @endif
                <span class="dg-brand-name">
                    Direction Générale des Transmissions et de l’Informatique
                    <small>Ministère de la Sécurité</small>
                </span>
            </a>

            <button class="dg-burger" id="dgBurger" type="button" aria-label="Ouvrir le menu" aria-expanded="false"
                aria-controls="dgMenu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M3 6h18M3 12h18M3 18h18" />
                </svg>
            </button>

            <nav class="dg-nav" id="dgMenu" aria-label="Navigation principale">
                <ul>
                    <li class="{{ $navAccueil ? 'is-active' : '' }}">
                        <a href="{{ url('/') }}" @if ($navAccueil) aria-current="page" @endif>Accueil</a>
                    </li>

                    <li class="{{ $navDirection ? 'is-active' : '' }}">
                        <button class="dg-toplink" type="button" aria-expanded="false">Direction
                            <svg class="dg-chev" viewBox="0 0 12 8" fill="none" stroke="currentColor" stroke-width="2"
                                aria-hidden="true">
                                <path d="M1 1l5 5 5-5" />
                            </svg>
                        </button>
                        <ul class="dg-dropdown">
                            <li><a href="{{ url('/qui-sommes-nous') }}">Qui sommes-nous ?</a></li>
                            <li><a href="{{ url('/organigramme') }}">Organigramme</a></li>
                            <li><a href="{{ url('/') }}#missions">Missions</a></li>
                            <li><a href="{{ url('/') }}#projets">Projets</a></li>
                            <li><a href="{{ url('/mot-du-directeur-general') }}">Mot du Directeur Général</a></li>
                        </ul>
                    </li>

                    <li class="{{ $navServices ? 'is-active' : '' }}">
                        <a href="{{ $param_site->services_url ?? url('/services') }}"
                            @if ($navServices) aria-current="page" @endif>Services</a>
                    </li>

                    <li class="{{ $navActualites ? 'is-active' : '' }}">
                        <a href="{{ url('/actualites') }}" @if ($navActualites) aria-current="page" @endif>Actualité</a>
                    </li>

                    <li class="{{ $navRessources ? 'is-active' : '' }}">
                        <button class="dg-toplink" type="button" aria-expanded="false">Ressources
                            <svg class="dg-chev" viewBox="0 0 12 8" fill="none" stroke="currentColor" stroke-width="2"
                                aria-hidden="true">
                                <path d="M1 1l5 5 5-5" />
                            </svg>
                        </button>
                        <ul class="dg-dropdown">
                            <li><a href="{{ url('/documents') }}">Documents</a></li>
                            <li><a href="{{ url('/phototheque') }}">Photothèque</a></li>
                            <li><a href="{{ url('/videotheque') }}">Vidéothèque</a></li>
                        </ul>
                    </li>

                    <li class="{{ $navFaq ? 'is-active' : '' }}">
                        <a href="{{ url('/faq') }}" @if ($navFaq) aria-current="page" @endif>FAQ</a>
                    </li>

                    <li class="{{ $navContact ? 'is-active' : '' }}">
                        <a href="{{ url('/contacts') }}" @if ($navContact) aria-current="page" @endif>Contacts</a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main id="main">
        @yield('content')
    </main>

    <!-- ===================== PIED DE PAGE ===================== -->
    <footer id="contact" class="dg-footer">
        <div class="dg-flagband" aria-hidden="true"><i></i><i></i><i></i></div>

        <div class="dg-wrap dg-footer-grid">
            <div class="dg-reveal">
                <h2>Infos contact</h2>
                <address>
                    <span>{{ $param_site->adresse ?? '01 BP 6466 Ouagadougou 01, Burkina Faso' }}</span>
                    @if ($tel1)
                        <a href="tel:{{ $telHref($tel1) }}">{{ $tel1 }}</a>
                    @endif
                    @if ($tel2)
                        <a href="tel:{{ $telHref($tel2) }}">{{ $tel2 }}</a>
                    @endif
                    @if ($email)
                        <a href="mailto:{{ $email }}">{{ $email }}</a>
                    @endif
                    @if (!empty($param_site->horaires))
                        <span>{{ $param_site->horaires }}</span>
                    @endif
                    @if (!empty($param_site->maps_url))
                        <a href="{{ $param_site->maps_url }}" target="_blank" rel="noopener">Géolocalisation</a>
                    @endif
                </address>
            </div>

            <div class="dg-reveal" style="--d:.1s">
                <h2>Newsletter</h2>
                <p>Restez informé de l’évolution de nos activités.</p>
                <form action="{{ url('/newsletter') }}" method="POST" class="dg-news-form">
                    @csrf
                    <label for="dg-nl-email" class="dg-sr">Votre adresse e-mail</label>
                    <input id="dg-nl-email" type="email" name="email" placeholder="Votre adresse e-mail" required>
                    <button type="submit" class="dg-btn dg-btn-primary">S’abonner</button>
                </form>
            </div>

            <div class="dg-reveal" style="--d:.2s">
                <h2>Liens utiles</h2>
                <ul class="dg-links">
                    @foreach ($liensUtiles as [$label, $href])
                        <li><a href="{{ $href }}" target="_blank" rel="noopener">{{ $label }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="dg-wrap dg-copy">
            <p>© {{ date('Y') }} Direction Générale des Transmissions et de l’Informatique. Tous droits réservés.</p>
            <a href="{{ url('/mentions-legales') }}">Mentions légales</a>
        </div>
    </footer>

    <a href="#top" class="dg-top" id="dgTop" aria-label="Haut de page">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M12 19V5M5 12l7-7 7 7" />
        </svg>
    </a>

    <script>
        (function() {
            var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var nav = document.getElementById('dgMenu');
            var burger = document.getElementById('dgBurger');
            var header = document.getElementById('dgHeader');
            var bar = document.getElementById('dgProgress');
            var topBtn = document.getElementById('dgTop');

            /* ----- Menus déroulants et menu mobile ----- */
            function closeAll() {
                document.querySelectorAll('.dg-nav li.open').forEach(function(li) {
                    li.classList.remove('open');
                    var b = li.querySelector('.dg-toplink');
                    if (b) b.setAttribute('aria-expanded', 'false');
                });
            }

            document.querySelectorAll('.dg-toplink').forEach(function(btn) {
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
                    nav.classList.remove('is-open');
                    burger.setAttribute('aria-expanded', 'false');
                }
            });

            burger.addEventListener('click', function(e) {
                e.stopPropagation();
                var open = nav.classList.toggle('is-open');
                burger.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
            nav.addEventListener('click', function(e) {
                e.stopPropagation();
            });

            /* ----- Défilement : progression, en-tête compact, retour en haut ----- */
            var ticking = false;

            function onScroll() {
                var y = window.scrollY;
                var h = document.documentElement.scrollHeight - window.innerHeight;
                bar.style.transform = 'scaleX(' + (h > 0 ? Math.min(1, y / h) : 0) + ')';
                header.classList.toggle('is-scrolled', y > 10);
                topBtn.classList.toggle('is-on', y > 600);
                ticking = false;
            }
            window.addEventListener('scroll', function() {
                if (!ticking) {
                    ticking = true;
                    requestAnimationFrame(onScroll);
                }
            }, {
                passive: true
            });
            onScroll();

            /* ----- Apparition des éléments .dg-reveal ----- */
            var reveals = document.querySelectorAll('.dg-reveal');
            if ('IntersectionObserver' in window && !reduce) {
                var io = new IntersectionObserver(function(entries) {
                    entries.forEach(function(en) {
                        if (en.isIntersecting) {
                            en.target.classList.add('is-in');
                            io.unobserve(en.target);
                        }
                    });
                }, {
                    threshold: .12,
                    rootMargin: '0px 0px -6% 0px'
                });
                reveals.forEach(function(el) {
                    io.observe(el);
                });
            } else {
                reveals.forEach(function(el) {
                    el.classList.add('is-in');
                });
            }

            /* ----- Spot lumineux qui suit le curseur sur les surfaces .dg-glass ----- */
            document.addEventListener('pointermove', function(e) {
                var t = e.target.closest && e.target.closest('.dg-glass');
                if (!t) return;
                var r = t.getBoundingClientRect();
                t.style.setProperty('--mx', (e.clientX - r.left) + 'px');
                t.style.setProperty('--my', (e.clientY - r.top) + 'px');
            }, {
                passive: true
            });

            /* ----- Inclinaison 3D des éléments [data-tilt] (souris uniquement) ----- */
            if (!reduce && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
                document.querySelectorAll('[data-tilt]').forEach(function(el) {
                    var max = 7;
                    el.addEventListener('pointermove', function(e) {
                        var r = el.getBoundingClientRect();
                        var px = (e.clientX - r.left) / r.width - .5;
                        var py = (e.clientY - r.top) / r.height - .5;
                        el.style.transform = 'perspective(900px) rotateX(' + (-py * max) + 'deg) rotateY(' + (px * max) + 'deg) translateY(-4px)';
                    });
                    el.addEventListener('pointerleave', function() {
                        el.style.transform = '';
                    });
                });
            }
        })();
    </script>
    @stack('scripts')
</body>

</html>