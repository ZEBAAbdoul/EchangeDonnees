@extends('layouts.site')

@section('title', 'Contacts — DGTI')
@section('description', 'Contactez la Direction Générale des Transmissions et de l’Informatique : adresse, téléphone, e-mail, horaires et formulaire de contact.')

{{--
    Variables attendues (toutes optionnelles) :
      $param_site : adresse, telephone, telephone2, email_contact (ou email), horaires, maps_url,
                    facebook_url, linkedin_url, youtube_url, helpdesk_tel, helpdesk_url
    Le formulaire envoie un POST vers /contacts (route contact.send, voir ContactController).
    Barre du haut, bandeau, menu et pied de page : layouts/site.blade.php.
    Protections anti-robots : honeypot, horodatage chiffré, Cloudflare Turnstile.
--}}

@php
    $adresse = $param_site->adresse ?? '01 BP 6466 Ouagadougou 01, Burkina Faso';
    $tel1 = $param_site->telephone ?? null;
    $tel2 = $param_site->telephone2 ?? null;
    $mail = trim($param_site->email_contact ?? ($param_site->email ?? '')) ?: null;
    $horaires = $param_site->horaires ?? null;
    $maps = $param_site->maps_url ?? null;
    $helpTel = $param_site->helpdesk_tel ?? null;
    $helpUrl = $param_site->helpdesk_url ?? null;

    // Si le champ contient plusieurs numéros séparés par une virgule, le lien tel: utilise le premier
    $telHref = fn($t) => preg_replace('/[^\d+]/', '', explode(',', $t)[0]);

    $reseaux = collect([
        [
            'Facebook',
            $param_site->facebook_url ?? null,
            '<path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12z"/>',
        ],
        [
            'LinkedIn',
            $param_site->linkedin_url ?? null,
            '<path d="M20.45 20.45h-3.56v-5.58c0-1.33-.02-3.04-1.85-3.04-1.86 0-2.14 1.45-2.14 2.94v5.68H9.34V9h3.42v1.56h.05c.48-.9 1.64-1.85 3.38-1.85 3.6 0 4.27 2.37 4.27 5.46v6.28zM5.34 7.43a2.07 2.07 0 1 1 0-4.13 2.07 2.07 0 0 1 0 4.13zM7.12 20.45H3.56V9h3.56v11.45z"/>',
        ],
        [
            'YouTube',
            $param_site->youtube_url ?? null,
            '<path d="M23 12s0-3.6-.46-5.3a2.9 2.9 0 0 0-2-2C18.9 4.2 12 4.2 12 4.2s-6.9 0-8.54.5a2.9 2.9 0 0 0-2 2C1 8.4 1 12 1 12s0 3.6.46 5.3a2.9 2.9 0 0 0 2 2c1.64.5 8.54.5 8.54.5s6.9 0 8.54-.5a2.9 2.9 0 0 0 2-2C23 15.6 23 12 23 12z"/><path d="M9.75 15.5v-7l6 3.5-6 3.5z" fill="#fff"/>',
        ],
    ])->filter(fn($r) => !empty($r[1]));

    // Même liste que dans ContactController::OBJETS
    $objets = [
        'Question générale',
        'Incident ou support technique',
        'Collaboration ou partenariat',
        'Presse et communication',
        'Autre demande',
    ];
@endphp

@section('content')

    <!-- ===================== EN-TÊTE DE PAGE ===================== -->
    <section class="ct-hero">
        <div class="dg-wrap">
            <nav class="ct-crumbs" aria-label="Fil d’Ariane">
                <a href="{{ url('/') }}">Accueil</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">Contacts</span>
            </nav>
            <p class="ct-kicker">Nous écrire</p>
            <h1>Contacts</h1>
            <p class="ct-hero-text">
                Une question, une demande d’information ou une proposition de collaboration ? Écrivez à la DGTI,
                nous vous répondrons dans les meilleurs délais.
            </p>
        </div>
    </section>

    <!-- ===================== COORDONNÉES + FORMULAIRE ===================== -->
    <section class="ct-section">
        <div class="dg-wrap ct-grid">

            <!-- ---------- Coordonnées ---------- -->
            <div class="ct-info">
                <div class="ct-head dg-reveal">
                    <p class="dg-eyebrow">Coordonnées</p>
                    <h2>Où et comment nous joindre</h2>
                </div>

                <ul class="ct-cards">
                    <li class="ct-card dg-reveal">
                        <span class="ct-ico" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22s7-6.2 7-12a7 7 0 1 0-14 0c0 5.8 7 12 7 12z" />
                                <circle cx="12" cy="10" r="2.5" />
                            </svg>
                        </span>
                        <div>
                            <h3>Adresse</h3>
                            <p>{{ $adresse }}</p>
                            @if ($maps)
                                <a href="{{ $maps }}" class="dg-link" target="_blank" rel="noopener">Voir sur la carte</a>
                            @endif
                        </div>
                    </li>

                    @if ($tel1 || $tel2)
                        <li class="ct-card dg-reveal" style="--d:.06s">
                            <span class="ct-ico" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path
                                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.34 1.79.66 2.65a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.43-1.27a2 2 0 0 1 2.11-.45c.86.32 1.75.54 2.65.66A2 2 0 0 1 22 16.92z" />
                                </svg>
                            </span>
                            <div>
                                <h3>Téléphone</h3>
                                @if ($tel1)
                                    <p><a href="tel:{{ $telHref($tel1) }}">{{ $tel1 }}</a></p>
                                @endif
                                @if ($tel2)
                                    <p><a href="tel:{{ $telHref($tel2) }}">{{ $tel2 }}</a></p>
                                @endif
                            </div>
                        </li>
                    @endif

                    @if ($mail)
                        <li class="ct-card dg-reveal" style="--d:.12s">
                            <span class="ct-ico" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="2" y="5" width="20" height="14" rx="2" />
                                    <path d="M2 7l10 7 10-7" />
                                </svg>
                            </span>
                            <div>
                                <h3>E-mail</h3>
                                <p><a href="mailto:{{ $mail }}">{{ $mail }}</a></p>
                            </div>
                        </li>
                    @endif

                    @if ($horaires)
                        <li class="ct-card dg-reveal" style="--d:.18s">
                            <span class="ct-ico" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 7v5l3 2" />
                                </svg>
                            </span>
                            <div>
                                <h3>Horaires</h3>
                                <p>{{ $horaires }}</p>
                            </div>
                        </li>
                    @endif
                </ul>

                @if ($helpTel || $helpUrl)
                    <aside class="ct-support dg-reveal" aria-labelledby="ctSupTitle">
                        <h3 id="ctSupTitle">Un incident sur le réseau ou un équipement ?</h3>
                        <p>Le support de la DGTI prend votre demande en charge{{ $helpTel ? ' : ' . $helpTel : '.' }}</p>
                        @if ($helpUrl)
                            <a href="{{ $helpUrl }}" class="dg-btn ct-support-btn">Contacter le support</a>
                        @elseif ($helpTel)
                            <a href="tel:{{ $telHref($helpTel) }}" class="dg-btn ct-support-btn">Appeler le support</a>
                        @endif
                    </aside>
                @endif

                @if ($reseaux->isNotEmpty())
                    <div class="ct-social dg-reveal">
                        <p>Suivez-nous</p>
                        <div>
                            @foreach ($reseaux as [$nom, $href, $svg])
                                <a href="{{ $href }}" target="_blank" rel="noopener"
                                    aria-label="{{ $nom }}">
                                    <svg viewBox="0 0 24 24" fill="currentColor"
                                        aria-hidden="true">{!! $svg !!}</svg>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- ---------- Formulaire ---------- -->
            <div class="ct-formwrap dg-reveal" style="--d:.1s">
                <h2>Envoyez-nous un message</h2>
                <p class="ct-formintro">Les champs marqués d’un <span aria-hidden="true">*</span><span
                        class="dg-sr">astérisque</span> sont obligatoires.</p>

                @if (session('success'))
                    <div class="ct-alert is-ok" role="status">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="ct-alert is-err" role="alert">
                        <strong>Le message n’a pas pu être envoyé.</strong>
                        <ul>
                            @foreach ($errors->all() as $erreur)
                                <li>{{ $erreur }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="ct-form" action="{{ route('contact.send') }}" method="POST" novalidate>
                    @csrf

                    {{-- Piège à robots : doit rester vide --}}
                    <div class="ct-hp" aria-hidden="true">
                        <label for="ct-site">Ne pas remplir</label>
                        <input id="ct-site" type="text" name="site_web" tabindex="-1" autocomplete="off">
                    </div>

                    {{-- Horodatage chiffré : le contrôleur vérifie le temps passé sur la page --}}
                    <input type="hidden" name="ts" value="{{ encrypt(now()->timestamp) }}">

                    <div class="ct-row">
                        <div class="ct-field">
                            <label for="ct-nom">Nom et prénom <span aria-hidden="true">*</span></label>
                            <input id="ct-nom" type="text" name="nom" value="{{ old('nom') }}" required
                                autocomplete="name" @error('nom') aria-invalid="true" @enderror>
                        </div>
                        <div class="ct-field">
                            <label for="ct-email">Adresse e-mail <span aria-hidden="true">*</span></label>
                            <input id="ct-email" type="email" name="email" value="{{ old('email') }}" required
                                autocomplete="email" @error('email') aria-invalid="true" @enderror>
                        </div>
                    </div>

                    <div class="ct-row">
                        <div class="ct-field">
                            <label for="ct-tel">Téléphone</label>
                            <input id="ct-tel" type="tel" name="telephone" value="{{ old('telephone') }}"
                                autocomplete="tel" @error('telephone') aria-invalid="true" @enderror>
                        </div>
                        <div class="ct-field">
                            <label for="ct-objet">Objet <span aria-hidden="true">*</span></label>
                            <select id="ct-objet" name="objet" required @error('objet') aria-invalid="true" @enderror>
                                <option value="" disabled {{ old('objet') ? '' : 'selected' }}>Choisir un objet
                                </option>
                                @foreach ($objets as $o)
                                    <option value="{{ $o }}" {{ old('objet') === $o ? 'selected' : '' }}>
                                        {{ $o }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="ct-field">
                        <label for="ct-message">Message <span aria-hidden="true">*</span></label>
                        <textarea id="ct-message" name="message" rows="6" required @error('message') aria-invalid="true" @enderror>{{ old('message') }}</textarea>
                    </div>

                    {{-- Cloudflare Turnstile (affiché seulement si la clé publique est configurée) --}}
                    @if (config('services.turnstile.site_key'))
                        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"
                            data-language="fr"></div>
                    @endif

                    <button type="submit" class="dg-btn dg-btn-primary ct-submit">
                        Envoyer le message
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M22 2L11 13M22 2l-7 20-4-9-9-4z" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </section>

    @push('styles')
        <style>
            .ct-section {
                padding: 72px 0 88px;
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

            /* ================= EN-TÊTE ================= */
            .ct-hero {
                position: relative;
                overflow: hidden;
                padding: 64px 0 72px;
                color: #fff;
                background-color: var(--dg-brand);
                background-image:
                    url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='120' height='104' viewBox='0 0 120 104'%3E%3Cg fill='none' stroke='%23ffffff' stroke-opacity='.1' stroke-width='1.5'%3E%3Cpath d='M30 0l30 17v35L30 69 0 52V17zM90 35l30 17v35l-30 17-30-17V52z'/%3E%3C/g%3E%3C/svg%3E"),
                    linear-gradient(110deg, #06307a 0%, #0a4aa6 55%, #0d62c9 100%);
            }

            .ct-hero::after {
                content: "";
                position: absolute;
                left: 0;
                right: 0;
                bottom: 0;
                height: 6px;
                background: linear-gradient(90deg, var(--dg-sky), #69c4ff);
            }

            .ct-hero .dg-wrap {
                position: relative;
                z-index: 1;
            }

            .ct-crumbs {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-bottom: 26px;
                font-size: 14px;
                color: #cfe0f7;
            }

            .ct-crumbs a {
                color: #fff;
                text-decoration: none;
            }

            .ct-crumbs a:hover {
                text-decoration: underline;
            }

            .ct-crumbs [aria-current] {
                color: #9fe8ff;
            }

            .ct-kicker {
                margin-bottom: 10px;
                font: 700 15px/1 var(--dg-sans);
                letter-spacing: .14em;
                text-transform: uppercase;
                color: #9fe8ff;
            }

            .ct-hero h1 {
                margin-bottom: 16px;
                color: #fff;
                font: 800 clamp(34px, 5vw, 56px)/1.08 var(--dg-sans);
                text-transform: uppercase;
                letter-spacing: -.01em;
            }

            .ct-hero-text {
                max-width: 58ch;
                padding-left: 16px;
                border-left: 3px solid #fff;
                font: 500 clamp(16px, 1.6vw, 20px)/1.55 var(--dg-sans);
                color: #eaf3ff;
            }

            /* ================= MISE EN PAGE ================= */
            .ct-grid {
                display: grid;
                grid-template-columns: 1fr 1.1fr;
                gap: 56px;
                align-items: start;
            }

            .ct-head {
                margin-bottom: 24px;
            }

            .ct-head h2,
            .ct-formwrap h2 {
                font-size: clamp(25px, 3vw, 34px);
                font-weight: 700;
            }

            /* ================= COORDONNÉES ================= */
            .ct-cards {
                display: grid;
                gap: 14px;
            }

            .ct-card {
                display: flex;
                gap: 18px;
                align-items: flex-start;
                padding: 20px 22px;
                background: #fff;
                border: 1px solid var(--dg-line);
                border-left: 4px solid var(--dg-brand);
                border-radius: 12px;
                transition: transform .3s var(--dg-ease), box-shadow .3s, border-left-color .3s;
            }

            .ct-card:hover {
                transform: translateX(5px);
                border-left-color: var(--dg-sky);
                box-shadow: 0 20px 38px -28px rgba(11, 42, 85, .6);
            }

            .ct-ico {
                flex-shrink: 0;
                display: grid;
                place-items: center;
                width: 48px;
                height: 48px;
                border-radius: 12px;
                color: #fff;
                background: var(--dg-brand);
                transition: background .3s, transform .4s var(--dg-ease);
            }

            .ct-ico svg {
                width: 24px;
                height: 24px;
            }

            .ct-card:hover .ct-ico {
                background: var(--dg-sky);
                transform: rotate(-6deg) scale(1.06);
            }

            .ct-card h3 {
                margin-bottom: 4px;
                font: 700 17px/1.3 var(--dg-serif);
                color: var(--dg-navy);
            }

            .ct-card p {
                color: var(--dg-ink);
                overflow-wrap: anywhere;
            }

            .ct-card a:not(.dg-link) {
                color: var(--dg-accent);
                text-decoration: none;
                font-weight: 600;
            }

            .ct-card a:not(.dg-link):hover {
                text-decoration: underline;
            }

            .ct-support {
                margin-top: 22px;
                padding: 24px 26px;
                color: #fff;
                border-radius: 12px;
                background: linear-gradient(120deg, #06307a, #0a4aa6 60%, #0d62c9);
            }

            .ct-support h3 {
                margin-bottom: 6px;
                color: #fff;
                font: 700 19px/1.3 var(--dg-serif);
            }

            .ct-support p {
                margin-bottom: 16px;
                color: #dbe8fb;
            }

            .ct-support-btn {
                padding: 12px 22px;
                color: var(--dg-brand);
                background: #fff;
                border-radius: 999px;
                font-weight: 700;
                text-transform: uppercase;
                font-size: 13.5px;
            }

            .ct-support-btn:hover {
                background: var(--dg-amber);
                color: #1b1500;
                box-shadow: 0 0 0 5px rgba(242, 183, 5, .28);
            }

            .ct-social {
                margin-top: 24px;
                display: flex;
                align-items: center;
                gap: 16px;
                flex-wrap: wrap;
            }

            .ct-social p {
                font-weight: 600;
                color: var(--dg-navy);
            }

            .ct-social div {
                display: flex;
                gap: 10px;
            }

            .ct-social a {
                display: grid;
                place-items: center;
                width: 42px;
                height: 42px;
                color: #fff;
                background: var(--dg-brand);
                border-radius: 50%;
                transition: transform .3s var(--dg-ease), background .2s;
            }

            .ct-social a:hover {
                background: var(--dg-sky);
                transform: translateY(-3px);
            }

            .ct-social svg {
                width: 20px;
                height: 20px;
            }

            /* ================= FORMULAIRE ================= */
            .ct-formwrap {
                padding: 34px 34px 38px;
                background: #fff;
                border: 1px solid var(--dg-line);
                border-top: 4px solid var(--dg-brand);
                border-radius: 14px;
                box-shadow: 0 28px 50px -34px rgba(5, 20, 60, .55);
            }

            .ct-formintro {
                margin: 6px 0 22px;
                color: var(--dg-muted);
                font-size: 14.5px;
            }

            .ct-formintro span[aria-hidden] {
                color: var(--dg-red);
                font-weight: 700;
            }

            .ct-form {
                display: grid;
                gap: 18px;
            }

            .ct-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 18px;
            }

            .ct-field {
                display: flex;
                flex-direction: column;
                gap: 7px;
            }

            .ct-field label {
                font: 600 14.5px/1.3 var(--dg-sans);
                color: var(--dg-navy);
            }

            .ct-field label span {
                color: var(--dg-red);
            }

            .ct-field input,
            .ct-field select,
            .ct-field textarea {
                width: 100%;
                padding: 13px 15px;
                font: inherit;
                font-size: 15.5px;
                color: var(--dg-ink);
                background: var(--dg-soft);
                border: 1px solid var(--dg-line);
                border-radius: 8px;
                transition: border-color .2s, background .2s, box-shadow .2s;
            }

            .ct-field textarea {
                resize: vertical;
                min-height: 140px;
            }

            .ct-field input:focus,
            .ct-field select:focus,
            .ct-field textarea:focus {
                outline: none;
                background: #fff;
                border-color: var(--dg-sky);
                box-shadow: 0 0 0 4px rgba(30, 136, 229, .18);
            }

            .ct-field [aria-invalid="true"] {
                border-color: var(--dg-red);
                background: #fff6f6;
            }

            /* Piège à robots : hors écran, invisible pour les humains */
            .ct-hp {
                position: absolute;
                left: -9999px;
                width: 1px;
                height: 1px;
                overflow: hidden;
            }

            .cf-turnstile {
                min-height: 65px;
            }

            .ct-submit {
                justify-self: start;
                padding: 14px 28px;
                border-radius: 999px;
            }

            .ct-submit svg {
                width: 18px;
                height: 18px;
                transition: transform .25s var(--dg-ease);
            }

            .ct-submit:hover svg {
                transform: translate(3px, -3px);
            }

            .ct-alert {
                margin-bottom: 20px;
                padding: 14px 18px;
                border-radius: 10px;
                font-size: 15px;
            }

            .ct-alert.is-ok {
                color: #07553a;
                background: #e5f7ee;
                border: 1px solid #9fdcc0;
            }

            .ct-alert.is-err {
                color: #7d1418;
                background: #fdeced;
                border: 1px solid #f0b3b6;
            }

            .ct-alert ul {
                margin-top: 6px;
                padding-left: 18px;
                list-style: disc;
            }

            /* ================= RESPONSIVE ================= */
            @media (max-width: 980px) {
                .ct-grid {
                    grid-template-columns: 1fr;
                    gap: 44px;
                }
            }

            @media (max-width: 700px) {
                .ct-section {
                    padding: 48px 0 64px;
                }

                .ct-hero {
                    padding: 44px 0 52px;
                }

                .ct-row {
                    grid-template-columns: 1fr;
                }

                .ct-formwrap {
                    padding: 26px 20px 30px;
                }

                .ct-submit {
                    justify-self: stretch;
                }
            }
        </style>
    @endpush

    {{-- Script Turnstile : chargé uniquement sur cette page (à retirer du <head> du layout) --}}
    @push('scripts')
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endpush
@endsection