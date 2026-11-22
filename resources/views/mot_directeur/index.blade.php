@extends('layouts.site')

@section('title', 'Mot du Directeur Général — DGTI')
@section('description', 'Le message du Directeur Général de la Direction Générale des Transmissions et de
    l’Informatique.')

    @php
        $dgNom = $param_site->mot_dg_nom ?? 'Le Directeur Général';
        // Le champ mot_dg_titre contient l'intitulé de la section : la fonction est donc fixe ici
$dgFonction = $param_site->mot_dg_titre ?? 'Directeur Général des Transmissions et de l’Informatique';

$dgInitiales = collect(explode(' ', trim($dgNom)))
    ->filter()
    ->map(fn($p) => mb_substr($p, 0, 1))
    ->take(2)
    ->implode('');

// Découpage du message en paragraphes (lignes vides ignorées).
// La mention « Le Directeur Général » en fin de texte est retirée : la signature l'affiche déjà.
        $paragraphes = collect(preg_split('/\R/u', trim($param_site->mot_dg_contenu ?? '')))
            ->map(fn($ligne) => trim($ligne))
            ->filter(fn($ligne) => $ligne !== '' && mb_strtolower($ligne) !== 'le directeur général')
            ->values();
    @endphp

@section('content')

    <!-- ===================== EN-TÊTE ===================== -->
    <section class="dg-page-head">
        <div class="dg-wrap">
            <nav class="dg-crumbs" aria-label="Fil d’Ariane">
                <a href="{{ url('/') }}">Accueil</a>
                <span aria-hidden="true">›</span>
                <span>Direction</span>
                <span aria-hidden="true">›</span>
                <span>Mot du Directeur Général</span>
            </nav>
            <h1>Mot du Directeur Général</h1>
        </div>
    </section>

    <!-- ===================== MESSAGE ===================== -->
    <section class="dg-section dg-section-alt">
        <div class="dg-wrap dg-mot">

            {{-- PHOTO DU DIRECTEUR GÉNÉRAL --}}
            <figure class="dg-portrait">
                <div class="dg-frame">
                    @if ($param_site && !empty($param_site->mot_dg_photo_url))
                        <img src="{{ asset($param_site->mot_dg_photo_url) }}" alt="Photo de {{ $dgNom }}"
                            onerror="this.remove()">
                    @endif
                    <span class="dg-initials" aria-hidden="true">{{ $dgInitiales }}</span>
                    <span class="dg-scan" aria-hidden="true"></span>
                    <i class="c c1" aria-hidden="true"></i><i class="c c2" aria-hidden="true"></i><i class="c c3"
                        aria-hidden="true"></i><i class="c c4" aria-hidden="true"></i>
                </div>
                <figcaption>
                    <strong>{{ $dgNom }}</strong>
                    <span>{{ $dgFonction }}</span>
                </figcaption>
            </figure>

            {{-- MESSAGE --}}
            <div class="dg-mot-text">
                <div class="dg-message">
                    @forelse ($paragraphes as $paragraphe)
                        <p>{{ $paragraphe }}</p>
                    @empty
                        <p class="dg-empty">Le message du Directeur Général sera bientôt disponible.</p>
                    @endforelse
                </div>

                @if ($paragraphes->isNotEmpty())
                    <div class="dg-signature">
                        <span class="dg-signature__name">{{ $dgNom }}</span>
                        <span class="dg-signature__role">Directeur Général de la DGTI</span>
                    </div>
                @endif

                <div class="dg-back">
                    <a href="{{ url('/') }}#dg" class="dg-link back-link">
                        <span aria-hidden="true">&larr;</span>
                        Retour à l’accueil
                    </a>
                </div>
            </div>

        </div>
    </section>

    @push('styles')
        <style>
            .dg-section {
                padding: 56px 0 90px;
            }

            .dg-section-alt {
                background: var(--dg-soft);
            }

            /* ---------- En-tête de page ---------- */
            .dg-page-head {
                position: relative;
                overflow: hidden;
                color: #fff;
                padding: 44px 0 40px;
                background: linear-gradient(110deg, #06307a 0%, #0a4aa6 55%, #0d62c9 100%);
            }

            .dg-page-head::after {
                content: "";
                position: absolute;
                left: 0;
                right: 0;
                bottom: 0;
                height: 5px;
                background: linear-gradient(90deg, var(--dg-sky), #69c4ff);
            }

            .dg-crumbs {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 16px;
                font-size: 14px;
                color: #cfe0f7;
            }

            .dg-crumbs a {
                color: #fff;
                text-decoration: none;
            }

            .dg-crumbs a:hover {
                text-decoration: underline;
            }

            .dg-page-head h1 {
                color: #fff;
                font: 800 clamp(28px, 4vw, 44px)/1.1 var(--dg-sans);
                text-transform: uppercase;
            }

            /* ---------- Mise en page ---------- */
            .dg-mot {
                display: grid;
                grid-template-columns: 300px 1fr;
                gap: 56px;
                align-items: start;
            }

            .dg-portrait {
                position: sticky;
                top: 96px;
                margin: 0;
            }

            .dg-frame {
                position: relative;
                aspect-ratio: 4 / 5;
                overflow: hidden;
                border-radius: 6px;
                background: var(--dg-brand);
            }

            .dg-frame img {
                position: absolute;
                inset: 0;
                z-index: 1;
                width: 100%;
                height: 100%;
                object-fit: cover;
                object-position: top center;
            }

            .dg-initials {
                position: absolute;
                inset: 0;
                display: grid;
                place-items: center;
                color: #fff;
                font: 700 72px/1 var(--dg-serif);
            }

            .dg-scan {
                position: absolute;
                left: 0;
                right: 0;
                top: 0;
                z-index: 2;
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
                z-index: 3;
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

            .dg-portrait figcaption {
                display: flex;
                flex-direction: column;
                margin-top: 14px;
            }

            .dg-portrait figcaption span {
                font-size: 14px;
                color: var(--dg-muted);
            }

            /* ---------- Message ---------- */
            .dg-message {
                position: relative;
                max-width: 70ch;
                padding-left: 24px;
                border-left: 4px solid var(--dg-sky);
                font-family: var(--dg-serif);
                font-size: 19px;
                line-height: 1.8;
                color: var(--dg-ink);
                text-align: justify;
                text-justify: inter-word;
                hyphens: auto;
                -webkit-hyphens: auto;
                overflow-wrap: break-word;
                text-wrap: pretty;
            }

            /* Guillemet décoratif en filigrane */
            .dg-message::before {
                content: "\201C";
                position: absolute;
                top: -34px;
                right: 6px;
                font: 700 150px/1 var(--dg-serif);
                color: var(--dg-brand);
                opacity: .07;
                pointer-events: none;
                user-select: none;
            }

            .dg-message p {
                position: relative;
                margin: 0 0 1.15em;
                text-indent: 1.8em;
                opacity: 0;
                transform: translateY(8px);
                animation: dgFade .6s ease forwards;
            }

            .dg-message p:nth-child(2) {
                animation-delay: .08s;
            }

            .dg-message p:nth-child(3) {
                animation-delay: .16s;
            }

            .dg-message p:nth-child(4) {
                animation-delay: .24s;
            }

            .dg-message p:nth-child(n+5) {
                animation-delay: .32s;
            }

            @keyframes dgFade {
                to {
                    opacity: 1;
                    transform: none;
                }
            }

            /* Chapeau : premier paragraphe */
            .dg-message p:first-child {
                text-indent: 0;
                margin-bottom: 1.3em;
                font-size: 1.14em;
                line-height: 1.7;
                color: var(--dg-brand);
            }

            .dg-message p:first-child::first-line {
                font-variant: small-caps;
                letter-spacing: .04em;
            }

            /* Lettrine — repli universel */
            .dg-message p:first-child::first-letter {
                float: left;
                margin: .06em .12em 0 0;
                font-size: 3.6em;
                line-height: .8;
                font-weight: 700;
                color: var(--dg-sky);
            }

            @supports (initial-letter: 2) {
                .dg-message p:first-child::first-letter {
                    float: none;
                    margin: 0 .12em 0 0;
                    font-size: inherit;
                    line-height: inherit;
                    initial-letter: 2;
                }
            }

            /* Message de secours */
            .dg-message .dg-empty {
                text-indent: 0;
                font-size: 1em;
                font-style: italic;
                color: inherit;
                opacity: .7;
                animation: none;
            }

            .dg-message .dg-empty::first-line {
                font-variant: normal;
                letter-spacing: 0;
            }

            .dg-message .dg-empty::first-letter {
                all: unset;
            }

            /* ---------- Signature ---------- */
            .dg-signature {
                display: flex;
                flex-direction: column;
                gap: 3px;
                margin: 28px 0 0 28px;
            }

            .dg-signature::before {
                content: "";
                width: 56px;
                height: 3px;
                margin-bottom: 12px;
                background: linear-gradient(90deg, var(--dg-red) 0 33%, var(--dg-amber) 33% 66%, var(--dg-green) 66%);
                border-radius: 2px;
            }

            .dg-signature__name {
                font: italic 600 22px/1.3 var(--dg-serif);
                color: var(--dg-brand);
            }

            .dg-signature__role {
                font: 700 11.5px/1.4 var(--dg-sans);
                letter-spacing: .09em;
                text-transform: uppercase;
                color: var(--dg-muted);
            }

            /* ---------- Retour ---------- */
            .dg-back {
                margin: 28px 0 0 28px;
            }

            .back-link {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                font-size: 13px;
                text-transform: uppercase;
                letter-spacing: .03em;
                text-decoration: none;
                transition: gap .2s ease, color .2s ease;
            }

            .back-link:hover,
            .back-link:focus-visible {
                gap: 11px;
            }

            /* ---------- Responsive ---------- */
            @media (max-width: 900px) {
                .dg-mot {
                    grid-template-columns: 1fr;
                    gap: 32px;
                }

                .dg-portrait {
                    position: static;
                    max-width: 240px;
                }
            }

            @media (max-width: 768px) {
                .dg-message {
                    padding-left: 16px;
                    font-size: 17px;
                    line-height: 1.75;
                    text-align: left;
                }

                .dg-message::before {
                    font-size: 110px;
                    top: -24px;
                }

                .dg-message p {
                    text-indent: 1.2em;
                }

                .dg-signature,
                .dg-back {
                    margin-left: 20px;
                }
            }

            @media (max-width: 480px) {
                .dg-message {
                    font-size: 16px;
                    line-height: 1.7;
                }

                .dg-signature__name {
                    font-size: 19px;
                }
            }

            /* ---------- Accessibilité / impression ---------- */
            @media (prefers-reduced-motion: reduce) {
                .dg-message p {
                    animation: none;
                    opacity: 1;
                    transform: none;
                }

                .back-link {
                    transition: none;
                }
            }

            @media print {
                .dg-message p {
                    opacity: 1;
                    transform: none;
                    animation: none;
                }

                .dg-message::before,
                .dg-back,
                .dg-scan {
                    display: none;
                }
            }
        </style>
    @endpush

@endsection
