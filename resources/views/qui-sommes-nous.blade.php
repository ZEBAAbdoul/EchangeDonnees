@extends('layouts.site')

@section('title', 'Qui sommes-nous ? — DGTI')
@section('description', 'Présentation de la Direction Générale des Transmissions et de l’Informatique du Ministère de la Sécurité : missions, domaines d’intervention et organisation.')

{{--
    Variables attendues (toutes optionnelles) :
      $param_site : meta_description, mot_dg_nom, mot_dg_titre, mot_dg_photo_url, helpdesk_tel, helpdesk_url
    Barre du haut, bandeau, menu et pied de page : layouts/site.blade.php.
--}}

@php
    $nomDg = $param_site->mot_dg_nom ?? 'Zakaria HÉBIÉ';
    $titreDg = $param_site->mot_dg_titre ?? 'Directeur Général des Transmissions et de l’Informatique';
    $initialesDg = collect(explode(' ', trim($nomDg)))
        ->filter()
        ->map(fn($p) => mb_substr($p, 0, 1))
        ->take(2)
        ->implode('');

    // Photo facultative : si le fichier existe, elle remplace le panneau bleu
    $imgAbout = 'images/qui-sommes-nous.jpg';
    $aboutImgOk = file_exists(public_path($imgAbout));

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

    $domaines = [
        'Transmissions',
        'Radiocommunication',
        'Systèmes d’information',
        'Réseaux',
        'Plateformes numériques',
        'Équipement des services',
    ];

    $liens = [
        ['Organigramme', 'Découvrir l’organisation de la direction.', url('/organigramme')],
        ['Mot du Directeur Général', 'Lire le message du Directeur Général.', url('/mot-du-directeur')],
        ['Nos grands projets', 'Les plateformes et chantiers numériques de la DGTI.', url('/') . '#projets'],
    ];
@endphp

@section('content')

    <!-- ===================== EN-TÊTE DE PAGE ===================== -->
    <section class="qs-hero">
        <div class="dg-wrap">
            <nav class="qs-crumbs" aria-label="Fil d’Ariane">
                <a href="{{ url('/') }}">Accueil</a>
                <span aria-hidden="true">/</span>
                <span>Direction</span>
                <span aria-hidden="true">/</span>
                <span aria-current="page">Qui sommes-nous ?</span>
            </nav>
            <p class="qs-kicker">À propos</p>
            <h1>Qui sommes-nous ?</h1>
            <p class="qs-hero-text">
                La Direction Générale des Transmissions et de l’Informatique, au service du Ministère de la Sécurité
                et des forces de sécurité.
            </p>
        </div>
    </section>

    <!-- ===================== PRÉSENTATION ===================== -->
    <section class="qs-section">
        <div class="dg-wrap qs-intro">
            <div class="qs-intro-copy dg-reveal">
                <p class="dg-eyebrow">Présentation</p>
                <h2>Les technologies au service de la sécurité</h2>
                <p class="qs-lead">
                    La DGTI est la structure du Ministère de la Sécurité chargée des technologies de l’information et
                    de la communication.
                </p>
                <p class="qs-text">
                    Elle pilote le schéma directeur informatique, les liaisons de transmission et l’équipement
                    numérique des services de sécurité. Elle construit et exploite les réseaux, les systèmes
                    d’information et les plateformes numériques qui soutiennent le travail des forces de sécurité.
                </p>

                <ul class="qs-points">
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
            </div>

            <figure class="qs-media dg-reveal" style="--d:.1s">
                @if ($aboutImgOk)
                    <img src="{{ asset($imgAbout) }}" alt="Les équipes de la DGTI" loading="lazy">
                @else
                    <div class="qs-card" aria-hidden="true">
                        <span class="qs-mono">DGTI</span>
                    </div>
                @endif
                <figcaption class="qs-badge">
                    <strong>Ministère de la Sécurité</strong>
                    <span>Transmissions et informatique</span>
                </figcaption>
            </figure>
        </div>
    </section>

    <!-- ===================== MISSIONS ===================== -->
    <section id="missions" class="qs-section qs-alt">
        <div class="dg-wrap">
            <div class="qs-head dg-reveal">
                <p class="dg-eyebrow">Missions</p>
                <h2>Nos missions</h2>
                <p>Ce que fait la DGTI au quotidien pour les forces de sécurité.</p>
            </div>

            <dl class="qs-missions">
                @foreach ($missions as [$titre, $texte, $ico])
                    <div class="qs-mission dg-reveal" style="--d:{{ $loop->index * 0.08 }}s">
                        <span class="qs-mission-num" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span>
                        <span class="qs-mi" aria-hidden="true">
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

    <!-- ===================== DOMAINES D'INTERVENTION ===================== -->
    <section class="qs-section">
        <div class="dg-wrap">
            <div class="qs-head dg-reveal">
                <p class="dg-eyebrow">Expertise</p>
                <h2>Nos domaines d’intervention</h2>
            </div>

            <ul class="qs-chips dg-reveal">
                @foreach ($domaines as $d)
                    <li>{{ $d }}</li>
                @endforeach
            </ul>
        </div>
    </section>

    <!-- ===================== DIRECTEUR GÉNÉRAL ===================== -->
    <section class="qs-section qs-alt">
        <div class="dg-wrap qs-dg">
            <figure class="qs-portrait dg-reveal">
                <div class="qs-frame">
                    @if ($param_site && !empty($param_site->mot_dg_photo_url))
                        <img src="{{ asset($param_site->mot_dg_photo_url) }}" alt="Photo de {{ $nomDg }}"
                            loading="lazy">
                    @else
                        <span class="qs-initials" aria-hidden="true">{{ $initialesDg }}</span>
                    @endif
                </div>
            </figure>

            <div class="qs-dg-text dg-reveal" style="--d:.1s">
                <p class="dg-eyebrow">Direction</p>
                <h2>{{ $nomDg }}</h2>
                <p class="qs-dg-title">{{ $titreDg }}</p>
                <a href="{{ url('/mot-du-directeur') }}" class="dg-btn dg-btn-primary">Lire le mot du Directeur Général</a>
            </div>
        </div>
    </section>

    <!-- ===================== POUR ALLER PLUS LOIN ===================== -->
    <section class="qs-section">
        <div class="dg-wrap">
            <div class="qs-head dg-reveal">
                <p class="dg-eyebrow">Explorer</p>
                <h2>Pour aller plus loin</h2>
            </div>

            <div class="qs-links">
                @foreach ($liens as [$t, $d, $href])
                    <a href="{{ $href }}" class="qs-link dg-reveal" style="--d:{{ $loop->index * 0.08 }}s">
                        <span class="qs-link-t">{{ $t }}</span>
                        <span class="qs-link-d">{{ $d }}</span>
                        <span class="qs-link-go" aria-hidden="true">→</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ===================== CONTACT ===================== -->
    <section class="qs-cta" aria-labelledby="qsCtaTitle">
        <div class="dg-wrap qs-cta-in">
            <div>
                <h2 id="qsCtaTitle">Une question ? Une collaboration à proposer ?</h2>
                <p>Institution, partenaire ou prestataire : contactez la DGTI.</p>
            </div>
            <a href="{{ url('/contacts') }}" class="dg-btn qs-cta-btn">Nous contacter</a>
        </div>
    </section>

    @push('styles')
        <style>
            .qs-section { padding: 80px 0; }
            .qs-alt { background: var(--dg-soft); }

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

            .qs-section h2 { font-size: clamp(27px, 3.4vw, 38px); font-weight: 700; margin-bottom: 8px; }
            .qs-head { margin-bottom: 36px; }
            .qs-head p:not(.dg-eyebrow) { color: var(--dg-muted); }

            /* ================= EN-TÊTE ================= */
            .qs-hero {
                position: relative;
                overflow: hidden;
                padding: 64px 0 72px;
                color: #fff;
                background-color: var(--dg-brand);
                background-image:
                    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='104' viewBox='0 0 120 104'%3E%3Cg fill='none' stroke='%23ffffff' stroke-opacity='.1' stroke-width='1.5'%3E%3Cpath d='M30 0l30 17v35L30 69 0 52V17zM90 35l30 17v35l-30 17-30-17V52z'/%3E%3C/g%3E%3C/svg%3E"),
                    linear-gradient(110deg, #06307a 0%, #0a4aa6 55%, #0d62c9 100%);
            }
            .qs-hero::after {
                content: "";
                position: absolute;
                left: 0;
                right: 0;
                bottom: 0;
                height: 6px;
                background: linear-gradient(90deg, var(--dg-sky), #69c4ff);
            }
            .qs-hero .dg-wrap { position: relative; z-index: 1; }

            .qs-crumbs {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 26px;
                font-size: 14px;
                color: #cfe0f7;
            }
            .qs-crumbs a { color: #fff; text-decoration: none; }
            .qs-crumbs a:hover { text-decoration: underline; }
            .qs-crumbs [aria-current] { color: #9fe8ff; }

            .qs-kicker {
                margin-bottom: 10px;
                font: 700 15px/1 var(--dg-sans);
                letter-spacing: .14em;
                text-transform: uppercase;
                color: #9fe8ff;
            }
            .qs-hero h1 {
                margin-bottom: 16px;
                color: #fff;
                font: 800 clamp(34px, 5vw, 56px)/1.08 var(--dg-sans);
                text-transform: uppercase;
                letter-spacing: -.01em;
            }
            .qs-hero-text {
                max-width: 58ch;
                padding-left: 16px;
                border-left: 3px solid #fff;
                font: 500 clamp(16px, 1.6vw, 20px)/1.55 var(--dg-sans);
                color: #eaf3ff;
            }

            /* ================= PRÉSENTATION ================= */
            .qs-intro {
                display: grid;
                grid-template-columns: 1.05fr 1fr;
                gap: 64px;
                align-items: center;
            }
            .qs-lead {
                margin: 6px 0 14px;
                font: 600 clamp(19px, 2vw, 22px)/1.5 var(--dg-serif);
                color: var(--dg-navy);
                max-width: 54ch;
            }
            .qs-text { font-size: 17px; color: var(--dg-muted); max-width: 58ch; margin-bottom: 22px; }

            .qs-points { display: grid; gap: 10px; }
            .qs-points li { display: flex; align-items: center; gap: 12px; font-weight: 600; color: var(--dg-ink); }
            .qs-points svg {
                flex-shrink: 0;
                width: 26px;
                height: 26px;
                padding: 5px;
                border-radius: 50%;
                color: #fff;
                background: var(--dg-green);
            }

            .qs-media { position: relative; margin: 0; padding: 0 0 26px 26px; }
            .qs-media::before {
                content: "";
                position: absolute;
                left: 0;
                bottom: 0;
                width: 70%;
                height: 70%;
                border: 3px solid var(--dg-amber);
                border-radius: 14px;
            }
            .qs-media > img,
            .qs-card {
                position: relative;
                display: block;
                width: 100%;
                aspect-ratio: 4 / 3;
                border-radius: 14px;
                object-fit: cover;
                box-shadow: 0 28px 50px -28px rgba(5, 20, 60, .65);
            }
            .qs-card {
                display: grid;
                place-items: center;
                overflow: hidden;
                background-color: var(--dg-brand);
                background-image:
                    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='104' viewBox='0 0 120 104'%3E%3Cg fill='none' stroke='%23ffffff' stroke-opacity='.12' stroke-width='1.5'%3E%3Cpath d='M30 0l30 17v35L30 69 0 52V17zM90 35l30 17v35l-30 17-30-17V52z'/%3E%3C/g%3E%3C/svg%3E"),
                    linear-gradient(135deg, #06307a, #0a4aa6 60%, #1e88e5);
            }
            .qs-mono {
                font: 800 clamp(54px, 8vw, 92px)/1 var(--dg-sans);
                letter-spacing: .04em;
                color: #fff;
                text-shadow: 0 10px 30px rgba(5, 20, 60, .45);
            }
            .qs-badge {
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
            .qs-badge strong { color: var(--dg-brand); font-size: 15px; }
            .qs-badge span { color: var(--dg-muted); font-size: 13.5px; }

            /* ================= MISSIONS ================= */
            .qs-missions { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 22px; margin: 0; }
            .qs-mission {
                position: relative;
                padding: 26px 24px 28px;
                background: #fff;
                border: 1px solid var(--dg-line);
                border-top: 3px solid var(--dg-brand);
                border-radius: 12px;
                transition: transform .3s var(--dg-ease), box-shadow .3s, border-color .3s;
            }
            .qs-mission:hover {
                transform: translateY(-6px);
                border-top-color: var(--dg-sky);
                box-shadow: 0 24px 44px -28px rgba(11, 42, 85, .55);
            }
            .qs-mission-num {
                position: absolute;
                top: 18px;
                right: 22px;
                font: 700 30px/1 var(--dg-serif);
                color: var(--dg-line);
                transition: color .3s;
            }
            .qs-mission:hover .qs-mission-num { color: #b9d4f5; }
            .qs-mi {
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
            .qs-mi svg { width: 26px; height: 26px; }
            .qs-mission:hover .qs-mi { transform: rotate(-6deg) scale(1.06); background: var(--dg-sky); }
            .qs-mission dt { font: 700 18px/1.3 var(--dg-serif); color: var(--dg-navy); margin-bottom: 8px; }
            .qs-mission dd { margin: 0; color: var(--dg-muted); font-size: 15px; }

            /* ================= DOMAINES ================= */
            .qs-chips { display: flex; flex-wrap: wrap; gap: 12px; }
            .qs-chips li {
                padding: 12px 22px;
                font: 600 15.5px/1 var(--dg-sans);
                color: var(--dg-brand);
                background: #fff;
                border: 1px solid var(--dg-line);
                border-radius: 999px;
                transition: background .25s, color .25s, border-color .25s, transform .25s var(--dg-ease);
            }
            .qs-chips li:hover {
                color: #fff;
                background: var(--dg-brand);
                border-color: var(--dg-brand);
                transform: translateY(-3px);
            }

            /* ================= DIRECTEUR GÉNÉRAL ================= */
            .qs-dg { display: grid; grid-template-columns: 260px 1fr; gap: 56px; align-items: center; }
            .qs-portrait { margin: 0; }
            .qs-frame { position: relative; overflow: hidden; border-radius: 6px; background: var(--dg-brand); }
            .qs-frame img,
            .qs-initials { display: block; width: 100%; aspect-ratio: 4 / 5; object-fit: cover; }
            .qs-initials {
                display: grid;
                place-items: center;
                background: var(--dg-brand);
                color: #fff;
                font: 700 64px/1 var(--dg-serif);
            }
            .qs-dg-title { margin-bottom: 24px; font: 400 19px/1.6 var(--dg-serif); color: var(--dg-muted); max-width: 52ch; }

            /* ================= LIENS ================= */
            .qs-links { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; }
            .qs-link {
                position: relative;
                display: flex;
                flex-direction: column;
                gap: 6px;
                padding: 24px 56px 26px 24px;
                background: #fff;
                border: 1px solid var(--dg-line);
                border-top: 4px solid var(--dg-brand);
                border-radius: 10px;
                text-decoration: none;
                transition: transform .25s var(--dg-ease), border-top-color .25s, box-shadow .25s;
            }
            .qs-link:hover {
                transform: translateY(-4px);
                border-top-color: var(--dg-sky);
                box-shadow: 0 22px 40px -26px rgba(11, 42, 85, .6);
            }
            .qs-link-t { font: 700 18px/1.3 var(--dg-serif); color: var(--dg-navy); }
            .qs-link-d { font-size: 14.5px; color: var(--dg-muted); }
            .qs-link-go {
                position: absolute;
                right: 22px;
                top: 50%;
                margin-top: -12px;
                font-size: 22px;
                font-weight: 700;
                color: var(--dg-sky);
                transition: transform .25s var(--dg-ease);
            }
            .qs-link:hover .qs-link-go { transform: translateX(5px); }

            /* ================= CONTACT ================= */
            .qs-cta {
                position: relative;
                overflow: hidden;
                padding: 64px 0;
                color: #fff;
                background: linear-gradient(110deg, #06307a, #0a4aa6 60%, #0d62c9);
            }
            .qs-cta::before {
                content: "";
                position: absolute;
                inset: 0;
                background-image: linear-gradient(rgba(255, 255, 255, .08) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, .08) 1px, transparent 1px);
                background-size: 48px 48px;
                -webkit-mask-image: linear-gradient(90deg, transparent, #000);
                mask-image: linear-gradient(90deg, transparent, #000);
            }
            .qs-cta-in {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 28px;
                flex-wrap: wrap;
            }
            .qs-cta h2 { color: #fff; font-size: clamp(24px, 3vw, 34px); margin-bottom: 8px; }
            .qs-cta p { color: #dbe8fb; }
            .qs-cta-btn {
                padding: 14px 28px;
                color: var(--dg-brand);
                background: #fff;
                border-radius: 999px;
                font-weight: 700;
                text-transform: uppercase;
                font-size: 14px;
            }
            .qs-cta-btn:hover { background: var(--dg-amber); color: #1b1500; box-shadow: 0 0 0 5px rgba(242, 183, 5, .28); }

            /* ================= RESPONSIVE ================= */
            @media (max-width: 980px) {
                .qs-intro, .qs-dg { grid-template-columns: 1fr; gap: 40px; }
                .qs-media { max-width: 520px; }
                .qs-missions { grid-template-columns: repeat(2, minmax(0, 1fr)); }
                .qs-links { grid-template-columns: 1fr; }
                .qs-portrait { max-width: 240px; }
            }
            @media (max-width: 700px) {
                .qs-section { padding: 56px 0; }
                .qs-hero { padding: 44px 0 52px; }
                .qs-missions { grid-template-columns: 1fr; }
                .qs-badge { right: 0; max-width: 220px; }
            }
        </style>
    @endpush
@endsection