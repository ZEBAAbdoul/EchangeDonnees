@extends('layouts.site')

@section('title', 'DGTI — Accueil')

{{--
    Variables attendues (toutes optionnelles, la vue ne plante pas si elles manquent) :
      $param_site  : hero_image, meta_description, mot_dg_nom, mot_dg_titre, mot_dg_photo_url, mot_dg_contenu,
                     helpdesk_tel, helpdesk_url
      $stats       : (facultatif) objets avec ->valeur (entier), ->libelle, ->suffixe (ex. « + »)
                     Rien n'est affiché si vous n'en fournissez pas : aucun chiffre n'est inventé.
      $actualites  : titre, resume, slug, created_at
      $projets     : titre, image, url
      $partenaires : nom, logo_url, site_web
      $explications: (facultatif) titre, resume, image, url

    Barre du haut, en-tête, menu et pied de page : layouts/site.blade.php.
--}}

@php
    $stats = $stats ?? collect();
    $actualites = $actualites ?? collect();
    $projets = $projets ?? collect();
    $partenaires = $partenaires ?? collect();
    $explications = $explications ?? collect();

    $nomDg = $param_site->mot_dg_nom ?? 'Zakaria HÉBIÉ';
    $titreDg = $param_site->mot_dg_titre ?? 'Directeur Général des Transmissions et de l’Informatique';
    $initialesDg = collect(explode(' ', trim($nomDg)))->filter()->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('');

    // Valeurs de repli : à remplacer par vos données ou à confirmer avec la DGTI.
    $projetsRepli = [
        ['titre' => 'Fiche de voyage', 'image' => null, 'url' => 'https://fichedevoyage.gov.bf'],
        ['titre' => 'e-Contravention', 'image' => null, 'url' => null],
        ['titre' => 'Vidéo-verbalisation', 'image' => null, 'url' => null],
    ];

    $listeProjets = $projets->isNotEmpty()
        ? $projets->map(fn($p) => ['titre' => $p->titre, 'image' => $p->image ?? null, 'url' => $p->url ?? null])
        : collect($projetsRepli);

    $missions = [
        ['Schéma directeur informatique', 'Élaborer et mettre en œuvre le schéma directeur informatique du ministère.'],
        ['Radiocommunication', 'Assurer la liaison radio entre le cabinet, les états-majors et les forces de sécurité intérieure.'],
        ['Équipement des services', 'Définir la stratégie d’équipement en matériel de communication, de surveillance et d’informatique.'],
        ['Réseaux et exploitation', 'Suivre l’exploitation et la maintenance des installations de télécommunication et d’informatique.'],
    ];

    // Mots qui défilent sous le titre et dans le bandeau
    $domaines = ['transmissions', 'radiocommunication', 'systèmes d’information', 'réseaux', 'plateformes numériques', 'équipement des services'];
    $bandeau = collect($domaines)->merge($listeProjets->pluck('titre'))->values();
@endphp

@section('content')

    <!-- ===================== HERO ===================== -->
    <section class="dg-hero" id="top">
        <canvas class="dg-net" id="dgNet" aria-hidden="true"></canvas>
        <div class="dg-blob dg-blob-a" aria-hidden="true"></div>
        <div class="dg-blob dg-blob-b" aria-hidden="true"></div>

        <div class="dg-wrap dg-hero-grid">
            <div class="dg-hero-copy">
                <p class="dg-hello dg-reveal"><span class="dg-hello-dot" aria-hidden="true"></span> Bienvenue sur le site web de la</p>

                <h1 id="dgTitle">Direction Générale des Transmissions et de l’Informatique</h1>

                <p class="dg-lede dg-reveal" style="--d:.15s">
                    {{ $param_site->meta_description ?? 'Nous construisons et exploitons les réseaux, les systèmes d’information et les plateformes numériques qui soutiennent le travail des forces de sécurité.' }}
                </p>

                <p class="dg-typed dg-reveal" style="--d:.25s">
                    Nos domaines :
                    <span id="dgTyped" data-words="{{ json_encode($domaines, JSON_UNESCAPED_UNICODE) }}">{{ $domaines[0] }}</span><i class="dg-caret" aria-hidden="true"></i>
                </p>

                <div class="dg-hero-cta dg-reveal" style="--d:.35s">
                    <a href="#projets" class="dg-btn dg-btn-primary">Voir les plateformes</a>
                    <a href="#qui-sommes-nous" class="dg-btn dg-btn-light">En savoir plus</a>
                </div>
            </div>

            <div class="dg-radar dg-reveal" style="--d:.2s" aria-hidden="true">
                <span class="dg-ring" style="--i:0"></span>
                <span class="dg-ring" style="--i:14%"></span>
                <span class="dg-ring" style="--i:28%"></span>
                <span class="dg-ring" style="--i:42%"></span>
                <span class="dg-cross"></span>
                <span class="dg-sweep"></span>
                <span class="dg-blip" style="--x:68%;--y:24%;--c:var(--dg-green);--delay:.2s"></span>
                <span class="dg-blip" style="--x:24%;--y:34%;--c:var(--dg-amber);--delay:1.1s"></span>
                <span class="dg-blip" style="--x:78%;--y:66%;--c:var(--dg-cyan);--delay:1.9s"></span>
                <span class="dg-blip" style="--x:36%;--y:76%;--c:var(--dg-red);--delay:2.6s"></span>
                <div class="dg-radar-core">
                    @if ($param_site && !empty($param_site->hero_image))
                        <img src="{{ asset($param_site->hero_image) }}" alt="">
                    @else
                        <span>DGTI</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="dg-scroll" aria-hidden="true"><span></span></div>
    </section>

    <!-- ===================== ACCÈS RAPIDES ===================== -->
    <section id="acces" class="dg-quick">
        <div class="dg-wrap dg-quick-grid">
            <article class="dg-quick-card dg-glass dg-reveal">
                <span class="dg-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 14v-2a8 8 0 0 1 16 0v2" />
                        <rect x="3" y="14" width="4" height="6" rx="1.5" />
                        <rect x="17" y="14" width="4" height="6" rx="1.5" />
                        <path d="M19 20c0 1.5-2 2-5 2" />
                    </svg>
                </span>
                <h2>Contactez notre support</h2>
                <p>
                    Pour déclarer un incident sur le réseau ou un équipement
                    @if (!empty($param_site->helpdesk_tel))
                        : {{ $param_site->helpdesk_tel }}
                    @endif
                </p>
                <a href="{{ $param_site->helpdesk_url ?? '#contact' }}" class="dg-btn dg-btn-light">Accéder au support</a>
            </article>

            <article class="dg-quick-card dg-glass dg-reveal" style="--d:.1s">
                <span class="dg-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7" rx="1.5" />
                        <rect x="14" y="3" width="7" height="7" rx="1.5" />
                        <rect x="3" y="14" width="7" height="7" rx="1.5" />
                        <rect x="14" y="14" width="7" height="7" rx="1.5" />
                    </svg>
                </span>
                <h2>Plateformes en ligne</h2>
                <p>Retrouvez les services numériques coordonnés par la DGTI.</p>
                <a href="#projets" class="dg-btn dg-btn-light">Voir les plateformes</a>
            </article>

            <article class="dg-quick-card dg-glass dg-reveal" style="--d:.2s">
                <span class="dg-ico" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="14" rx="2" />
                        <path d="M3 7l9 6 9-6" />
                    </svg>
                </span>
                <h2>Demande de collaboration</h2>
                <p>Institution, partenaire ou prestataire : écrivez-nous.</p>
                <a href="#contact" class="dg-btn dg-btn-light">Nous écrire</a>
            </article>
        </div>
    </section>

    <!-- ===================== CHIFFRES (facultatif, uniquement si fournis) ===================== -->
    @if ($stats->isNotEmpty())
        <section class="dg-stats">
            <div class="dg-wrap dg-stats-grid">
                @foreach ($stats as $s)
                    <div class="dg-stat dg-reveal" style="--d:{{ $loop->index * .1 }}s">
                        <strong>
                            <span data-count="{{ (int) $s->valeur }}">{{ number_format((int) $s->valeur, 0, ',', ' ') }}</span>{{ $s->suffixe ?? '' }}
                        </strong>
                        <span>{{ $s->libelle }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- ===================== BANDEAU DÉFILANT ===================== -->
    <div class="dg-ticker" aria-hidden="true">
        <div class="dg-ticker-track">
            @for ($i = 0; $i < 2; $i++)
                <ul class="dg-ticker-group">
                    @foreach ($bandeau as $t)
                        <li>{{ $t }}</li>
                    @endforeach
                </ul>
            @endfor
        </div>
    </div>

    <!-- ===================== MOT DU DG ===================== -->
    <section id="dg" class="dg-section">
        <div class="dg-wrap dg-dg">
            <figure class="dg-portrait dg-reveal">
                <div class="dg-frame">
                    @if ($param_site && !empty($param_site->mot_dg_photo_url))
                        <img src="{{ asset($param_site->mot_dg_photo_url) }}" alt="Photo de {{ $nomDg }}">
                    @else
                        <span class="dg-initials" aria-hidden="true">{{ $initialesDg }}</span>
                    @endif
                    <span class="dg-scan" aria-hidden="true"></span>
                    <i class="c c1" aria-hidden="true"></i><i class="c c2" aria-hidden="true"></i>
                    <i class="c c3" aria-hidden="true"></i><i class="c c4" aria-hidden="true"></i>
                </div>
                <figcaption>
                    <strong>{{ $nomDg }}</strong>
                    <span>{{ $titreDg }}</span>
                </figcaption>
            </figure>

            <div class="dg-dg-text">
                <h2 class="dg-reveal">Mot du Directeur Général</h2>
                <blockquote class="dg-reveal" style="--d:.12s">
                    {{ \Illuminate\Support\Str::limit(trim($param_site->mot_dg_contenu ?? 'Le message du Directeur Général sera publié ici.'), 520, '…') }}
                </blockquote>
                <a href="{{ url('/mot-du-directeur-general') }}" class="dg-link dg-reveal" style="--d:.22s">Lire la suite <span class="dg-arrow" aria-hidden="true">→</span></a>
            </div>
        </div>
    </section>

    <!-- ===================== QUI SOMMES-NOUS + MISSIONS ===================== -->
    <section id="qui-sommes-nous" class="dg-section dg-section-alt">
        <div class="dg-wrap">
            <div class="dg-about dg-reveal">
                <h2>Qui sommes-nous ?</h2>
                <div>
                    <p class="dg-about-text">
                        La DGTI est la structure du Ministère de la Sécurité chargée des technologies de
                        l’information et de la communication. Elle pilote le schéma directeur informatique, les
                        liaisons de transmission et l’équipement numérique des services de sécurité.
                    </p>
                    <a href="{{ url('/qui-sommes-nous') }}" class="dg-link">En savoir plus <span class="dg-arrow" aria-hidden="true">→</span></a>
                </div>
            </div>

            <div id="missions" class="dg-hub dg-reveal">
                <svg class="dg-hub-lines" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                    <line x1="25" y1="25" x2="50" y2="50" />
                    <line x1="75" y1="25" x2="50" y2="50" />
                    <line x1="25" y1="75" x2="50" y2="50" />
                    <line x1="75" y1="75" x2="50" y2="50" />
                </svg>
                <div class="dg-hub-core" aria-hidden="true"><span>DGTI</span></div>

                <dl class="dg-missions">
                    @foreach ($missions as [$titre, $texte])
                        <div class="dg-mission dg-glass">
                            <dt>{{ $titre }}</dt>
                            <dd>{{ $texte }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </section>

    <!-- ===================== ACTUALITÉS ===================== -->
    <section id="actualites" class="dg-section">
        <div class="dg-wrap">
            <div class="dg-head dg-reveal">
                <div>
                    <h2>Actualités récentes</h2>
                    <p>Toute l’actualité de la DGTI à travers des articles.</p>
                </div>
                <a href="{{ url('/actualites') }}" class="dg-link">Voir toute l’actualité <span class="dg-arrow" aria-hidden="true">→</span></a>
            </div>

            <ul class="dg-news">
                @forelse ($actualites->take(6) as $a)
                    <li class="dg-reveal" style="--d:{{ $loop->index * .06 }}s">
                        <a class="dg-news-row" href="{{ url('/actualites/' . ($a->slug ?? $a->id)) }}">
                            <time datetime="{{ optional($a->created_at)->toDateString() }}">
                                {{ optional($a->created_at)->translatedFormat('d F Y') }}
                            </time>
                            <span class="dg-news-title">{{ $a->titre }}</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </a>
                    </li>
                @empty
                    <li class="dg-muted">Aucune actualité publiée pour le moment.</li>
                @endforelse
            </ul>
        </div>
    </section>

    <!-- ===================== PROJETS ===================== -->
    <section id="projets" class="dg-section dg-section-alt">
        <div class="dg-wrap">
            <div class="dg-head dg-reveal">
                <div>
                    <h2>Nos grands projets</h2>
                    <p>Les plateformes et chantiers numériques portés par la DGTI.</p>
                </div>
            </div>

            <div class="dg-projects">
                @foreach ($listeProjets as $p)
                    <article class="dg-project dg-glass dg-reveal" data-tilt style="--d:{{ $loop->index * .1 }}s">
                        <div class="dg-project-media">
                            @if (!empty($p['image']))
                                <img src="{{ asset($p['image']) }}" alt="" loading="lazy">
                            @else
                                <span aria-hidden="true">{{ mb_substr($p['titre'], 0, 1) }}</span>
                            @endif
                            <i class="dg-project-scan" aria-hidden="true"></i>
                        </div>
                        <div class="dg-project-body">
                            <h3>{{ $p['titre'] }}</h3>
                            @if (!empty($p['url']))
                                <a href="{{ $p['url'] }}" class="dg-link" target="_blank" rel="noopener">Découvrir le projet <span class="dg-arrow" aria-hidden="true">→</span></a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ===================== PARTENAIRES ===================== -->
    <section id="partenaires" class="dg-section">
        <div class="dg-wrap">
            <div class="dg-head dg-reveal">
                <h2>Ils nous ont fait confiance</h2>
            </div>

            <div class="dg-partners">
                @forelse ($partenaires as $p)
                    @if (!empty($p->site_web))
                        <a href="{{ $p->site_web }}" class="dg-partner dg-reveal" style="--d:{{ $loop->index * .05 }}s" target="_blank" rel="noopener" title="{{ $p->nom }}">
                            <img src="{{ asset($p->logo_url) }}" alt="{{ $p->nom }}" loading="lazy">
                        </a>
                    @else
                        <div class="dg-partner dg-reveal" style="--d:{{ $loop->index * .05 }}s" title="{{ $p->nom }}">
                            <img src="{{ asset($p->logo_url) }}" alt="{{ $p->nom }}" loading="lazy">
                        </div>
                    @endif
                @empty
                    <p class="dg-muted">Aucun partenaire référencé pour le moment.</p>
                @endforelse
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
                        <a href="{{ $e->url ?? '#' }}" class="dg-explain-item dg-glass dg-reveal" style="--d:{{ $loop->index * .1 }}s">
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

    @push('styles')
        <style>
            /* Styles propres à l'accueil. Les styles communs sont dans layouts/site.blade.php. */
            .dg-section {
                position: relative;
                padding: 96px 0;
            }

            .dg-section-alt {
                background: linear-gradient(180deg, transparent, rgba(75, 123, 255, .06) 20%, rgba(75, 123, 255, .06) 80%, transparent);
            }

            .dg-head {
                display: flex;
                justify-content: space-between;
                align-items: flex-end;
                gap: 20px;
                flex-wrap: wrap;
                margin-bottom: 40px;
            }

            .dg-head h2,
            .dg-dg-text h2,
            .dg-about h2 {
                font-size: clamp(28px, 3.6vw, 42px);
                font-weight: 700;
                margin-bottom: 8px;
            }

            .dg-head p,
            .dg-muted {
                color: var(--dg-muted);
            }

            /* ============ HERO ============ */
            .dg-hero {
                position: relative;
                display: grid;
                align-items: center;
                min-height: min(88vh, 800px);
                padding: 84px 0 130px;
                overflow: hidden;
            }

            .dg-net {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
            }

            .dg-blob {
                position: absolute;
                border-radius: 50%;
                filter: blur(90px);
                opacity: .5;
                pointer-events: none;
            }

            .dg-blob-a {
                width: 460px;
                height: 460px;
                background: var(--dg-blue);
                top: -120px;
                right: 6%;
                animation: dgDrift 16s ease-in-out infinite alternate;
            }

            .dg-blob-b {
                width: 380px;
                height: 380px;
                background: var(--dg-cyan);
                opacity: .28;
                bottom: -140px;
                left: -60px;
                animation: dgDrift 20s ease-in-out infinite alternate-reverse;
            }

            @keyframes dgDrift {
                to {
                    transform: translate(60px, 40px) scale(1.15);
                }
            }

            .dg-hero-grid {
                position: relative;
                z-index: 2;
                display: grid;
                grid-template-columns: 1.25fr 1fr;
                gap: 48px;
                align-items: center;
            }

            .dg-hello {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                color: var(--dg-cyan);
                font-size: 15px;
                font-weight: 500;
                margin-bottom: 18px;
            }

            .dg-hello-dot {
                width: 9px;
                height: 9px;
                border-radius: 50%;
                background: var(--dg-cyan);
                box-shadow: 0 0 0 0 rgba(47, 216, 255, .6);
                animation: dgPing2 2.2s ease-out infinite;
            }

            @keyframes dgPing2 {
                70% {
                    box-shadow: 0 0 0 10px rgba(47, 216, 255, 0);
                }

                100% {
                    box-shadow: 0 0 0 0 rgba(47, 216, 255, 0);
                }
            }

            .dg-hero h1 {
                font-size: clamp(32px, 4.8vw, 58px);
                font-weight: 800;
                line-height: 1.08;
                letter-spacing: -.025em;
                margin-bottom: 24px;
                background: linear-gradient(120deg, #fff 30%, var(--dg-cyan) 70%, var(--dg-blue));
                -webkit-background-clip: text;
                background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .dg-lede {
                color: #c4d3ea;
                font-size: 18px;
                font-weight: 300;
                max-width: 56ch;
                margin-bottom: 22px;
            }

            .dg-typed {
                font-family: "IBM Plex Mono", monospace;
                font-size: 15px;
                color: var(--dg-muted);
                margin-bottom: 30px;
                min-height: 1.6em;
            }

            .dg-typed span {
                color: var(--dg-green);
            }

            .dg-caret {
                display: inline-block;
                width: 8px;
                height: 1.05em;
                margin-left: 3px;
                vertical-align: -2px;
                background: var(--dg-green);
                animation: dgBlink 1s steps(1) infinite;
            }

            @keyframes dgBlink {
                50% {
                    opacity: 0;
                }
            }

            .dg-hero-cta {
                display: flex;
                flex-wrap: wrap;
                gap: 14px;
            }

            /* ----- Radar ----- */
            .dg-radar {
                position: relative;
                width: min(100%, 430px);
                aspect-ratio: 1;
                justify-self: center;
                border-radius: 50%;
            }

            .dg-ring {
                position: absolute;
                inset: var(--i);
                border-radius: 50%;
                border: 1px solid rgba(47, 216, 255, .28);
            }

            .dg-ring:first-child {
                border-color: var(--dg-line-strong);
                box-shadow: 0 0 60px -10px rgba(47, 216, 255, .45), inset 0 0 60px -20px rgba(47, 216, 255, .35);
            }

            .dg-cross {
                position: absolute;
                inset: 0;
                background:
                    linear-gradient(rgba(47, 216, 255, .2), rgba(47, 216, 255, .2)) center / 1px 100% no-repeat,
                    linear-gradient(rgba(47, 216, 255, .2), rgba(47, 216, 255, .2)) center / 100% 1px no-repeat;
            }

            .dg-sweep {
                position: absolute;
                inset: 0;
                border-radius: 50%;
                background: conic-gradient(from 0deg, rgba(47, 216, 255, .42), rgba(47, 216, 255, 0) 70deg, transparent 71deg);
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
                box-shadow: 0 0 14px var(--c);
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
                    opacity: .25;
                }

                8% {
                    opacity: 1;
                }

                60% {
                    opacity: .35;
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
                inset: 36%;
                display: grid;
                place-items: center;
                border-radius: 50%;
                background: radial-gradient(circle, #0d2a52, #07142b);
                border: 1px solid var(--dg-line-strong);
                box-shadow: 0 0 40px rgba(47, 216, 255, .4);
                font-weight: 800;
                font-size: clamp(15px, 2.4vw, 22px);
                overflow: hidden;
            }

            .dg-radar-core img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .dg-scroll {
                position: absolute;
                left: 50%;
                bottom: 70px;
                width: 24px;
                height: 40px;
                margin-left: -12px;
                border: 2px solid var(--dg-line-strong);
                border-radius: 14px;
                z-index: 2;
            }

            .dg-scroll span {
                position: absolute;
                left: 50%;
                top: 7px;
                width: 4px;
                height: 8px;
                margin-left: -2px;
                border-radius: 2px;
                background: var(--dg-cyan);
                animation: dgWheel 1.8s ease-in-out infinite;
            }

            @keyframes dgWheel {
                0% {
                    opacity: 0;
                    transform: translateY(0);
                }

                30% {
                    opacity: 1;
                }

                100% {
                    opacity: 0;
                    transform: translateY(14px);
                }
            }

            /* ============ ACCÈS RAPIDES ============ */
            .dg-quick {
                position: relative;
                z-index: 3;
                margin-top: -70px;
            }

            .dg-quick-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 20px;
            }

            .dg-quick-card {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
                padding: 30px 28px;
                background: rgba(10, 22, 48, .72);
            }

            .dg-quick-card h2 {
                font-size: 20px;
                font-weight: 700;
            }

            .dg-quick-card p {
                color: var(--dg-muted);
                flex: 1;
                margin-bottom: 8px;
            }

            .dg-ico {
                display: grid;
                place-items: center;
                width: 52px;
                height: 52px;
                border-radius: 14px;
                color: var(--dg-cyan);
                background: rgba(47, 216, 255, .1);
                border: 1px solid var(--dg-line-strong);
                transition: transform .5s var(--dg-ease), box-shadow .4s;
            }

            .dg-ico svg {
                width: 26px;
                height: 26px;
            }

            .dg-quick-card:hover .dg-ico {
                transform: rotate(-8deg) scale(1.08);
                box-shadow: 0 0 26px rgba(47, 216, 255, .45);
            }

            /* ============ CHIFFRES ============ */
            .dg-stats {
                padding: 72px 0 0;
            }

            .dg-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
                gap: 28px;
                text-align: center;
            }

            .dg-stat strong {
                display: block;
                font-size: clamp(36px, 5vw, 58px);
                font-weight: 800;
                background: linear-gradient(120deg, var(--dg-cyan), var(--dg-blue));
                -webkit-background-clip: text;
                background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .dg-stat>span {
                color: var(--dg-muted);
                font-size: 14.5px;
            }

            /* ============ BANDEAU ============ */
            .dg-ticker {
                margin-top: 72px;
                overflow: hidden;
                border-block: 1px solid var(--dg-line);
                background: rgba(255, 255, 255, .02);
                -webkit-mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent);
                mask-image: linear-gradient(90deg, transparent, #000 8%, #000 92%, transparent);
            }

            .dg-ticker-track {
                display: flex;
                width: max-content;
                animation: dgTicker 38s linear infinite;
            }

            .dg-ticker:hover .dg-ticker-track {
                animation-play-state: paused;
            }

            .dg-ticker-group {
                display: flex;
                gap: 44px;
                padding: 18px 44px 18px 0;
            }

            .dg-ticker-group li {
                display: flex;
                align-items: center;
                gap: 44px;
                white-space: nowrap;
                font-size: 17px;
                font-weight: 500;
                color: var(--dg-muted);
            }

            .dg-ticker-group li::after {
                content: "";
                width: 9px;
                height: 9px;
                transform: rotate(45deg);
                background: var(--dg-red);
            }

            .dg-ticker-group li:nth-child(3n+2)::after {
                background: var(--dg-amber);
            }

            .dg-ticker-group li:nth-child(3n)::after {
                background: var(--dg-green);
            }

            @keyframes dgTicker {
                to {
                    transform: translateX(-50%);
                }
            }

            /* ============ MOT DU DG ============ */
            .dg-dg {
                display: grid;
                grid-template-columns: 280px 1fr;
                gap: 64px;
                align-items: center;
            }

            .dg-portrait {
                margin: 0;
            }

            .dg-frame {
                position: relative;
                aspect-ratio: 4 / 5;
                border-radius: 6px;
                overflow: hidden;
                background: #07142b;
            }

            .dg-frame img,
            .dg-initials {
                display: grid;
                place-items: center;
                width: 100%;
                height: 100%;
                object-fit: cover;
                font-size: 68px;
                font-weight: 800;
                color: var(--dg-cyan);
            }

            .dg-scan {
                position: absolute;
                left: 0;
                right: 0;
                top: 0;
                height: 36%;
                background: linear-gradient(180deg, transparent, rgba(47, 216, 255, .28));
                border-bottom: 1px solid var(--dg-cyan);
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
                border: 2px solid var(--dg-cyan);
                filter: drop-shadow(0 0 6px var(--dg-cyan));
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
                gap: 2px;
                margin-top: 16px;
            }

            .dg-portrait figcaption span {
                font-size: 13.5px;
                color: var(--dg-muted);
            }

            .dg-dg-text h2 {
                margin-bottom: 24px;
            }

            .dg-dg-text blockquote {
                position: relative;
                margin: 0 0 28px;
                padding-left: 30px;
                font-size: 20px;
                font-weight: 300;
                line-height: 1.8;
                max-width: 62ch;
                color: #d5e1f5;
            }

            .dg-dg-text blockquote::before {
                content: "";
                position: absolute;
                left: 0;
                top: 4px;
                bottom: 4px;
                width: 3px;
                border-radius: 3px;
                background: linear-gradient(180deg, var(--dg-cyan), var(--dg-blue), transparent);
            }

            /* ============ QUI SOMMES-NOUS + HUB ============ */
            .dg-about {
                display: grid;
                grid-template-columns: 1fr 1.2fr;
                gap: 56px;
                align-items: start;
            }

            .dg-about-text {
                font-size: 18px;
                font-weight: 300;
                color: #c4d3ea;
                margin-bottom: 20px;
                max-width: 54ch;
            }

            .dg-hub {
                position: relative;
                margin-top: 72px;
            }

            .dg-missions {
                position: relative;
                z-index: 2;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 100px 124px;
                margin: 0;
            }

            .dg-mission {
                padding: 28px 30px;
                background: rgba(10, 22, 48, .78);
            }

            .dg-mission dt {
                font-size: 19px;
                font-weight: 700;
                margin-bottom: 8px;
            }

            .dg-mission dd {
                margin: 0;
                color: var(--dg-muted);
                font-size: 15px;
            }

            .dg-hub-lines {
                position: absolute;
                inset: 0;
                width: 100%;
                height: 100%;
                z-index: 1;
            }

            .dg-hub-lines line {
                stroke: var(--dg-cyan);
                stroke-width: 1.6;
                vector-effect: non-scaling-stroke;
                stroke-dasharray: 4 9;
                opacity: .6;
                animation: dgDash 1.4s linear infinite;
            }

            @keyframes dgDash {
                to {
                    stroke-dashoffset: -26;
                }
            }

            .dg-hub-core {
                position: absolute;
                left: 50%;
                top: 50%;
                z-index: 3;
                display: grid;
                place-items: center;
                width: 118px;
                height: 118px;
                margin: -59px 0 0 -59px;
                border-radius: 50%;
                background: radial-gradient(circle, #0d2a52, #07142b);
                font-weight: 800;
                font-size: 20px;
                box-shadow: 0 0 46px rgba(47, 216, 255, .4);
            }

            .dg-hub-core::before {
                content: "";
                position: absolute;
                inset: -3px;
                border-radius: 50%;
                padding: 3px;
                background: conic-gradient(from var(--a), var(--dg-cyan), var(--dg-blue), var(--dg-green), var(--dg-cyan));
                -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
                -webkit-mask-composite: xor;
                mask: linear-gradient(#000 0 0) content-box exclude, linear-gradient(#000 0 0);
                animation: dgRing 5s linear infinite;
            }

            .dg-hub-core::after {
                content: "";
                position: absolute;
                inset: -16px;
                border-radius: 50%;
                border: 1px solid var(--dg-cyan);
                animation: dgHubPulse 3s ease-out infinite;
            }

            @keyframes dgHubPulse {
                0% {
                    transform: scale(.85);
                    opacity: .7;
                }

                100% {
                    transform: scale(1.45);
                    opacity: 0;
                }
            }

            /* ============ ACTUALITÉS ============ */
            .dg-news {
                border-top: 1px solid var(--dg-line);
            }

            .dg-news-row {
                position: relative;
                display: grid;
                grid-template-columns: 160px 1fr 26px;
                align-items: center;
                gap: 22px;
                padding: 22px 18px;
                border-bottom: 1px solid var(--dg-line);
                text-decoration: none;
                overflow: hidden;
            }

            .dg-news-row::before {
                content: "";
                position: absolute;
                inset: 0;
                background: linear-gradient(90deg, rgba(47, 216, 255, .13), transparent 70%);
                transform: translateX(-101%);
                transition: transform .55s var(--dg-ease);
            }

            .dg-news-row:hover::before {
                transform: none;
            }

            .dg-news-row>* {
                position: relative;
            }

            .dg-news-row time {
                font-family: "IBM Plex Mono", monospace;
                font-size: 13.5px;
                color: var(--dg-cyan);
            }

            .dg-news-title {
                font-size: 18px;
                font-weight: 500;
                transition: transform .4s var(--dg-ease);
            }

            .dg-news-row:hover .dg-news-title {
                transform: translateX(8px);
            }

            .dg-news-row svg {
                width: 22px;
                height: 22px;
                color: var(--dg-cyan);
                opacity: 0;
                transform: translateX(-10px);
                transition: opacity .3s, transform .4s var(--dg-ease);
            }

            .dg-news-row:hover svg {
                opacity: 1;
                transform: none;
            }

            /* ============ PROJETS ============ */
            .dg-projects {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
                gap: 26px;
            }

            .dg-project {
                display: flex;
                flex-direction: column;
                transform-style: preserve-3d;
            }

            /* L'inclinaison 3D remplace la transition lente de l'apparition une fois l'élément visible */
            .js .dg-reveal.is-in[data-tilt] {
                transition: transform .25s var(--dg-ease), opacity .9s var(--dg-ease), border-color .3s;
                transition-delay: 0s;
            }

            .dg-project-media {
                position: relative;
                display: grid;
                place-items: center;
                aspect-ratio: 16 / 10;
                overflow: hidden;
                background: linear-gradient(120deg, #0b1f45, #123a7a, #0a5f78, #0b1f45);
                background-size: 300% 300%;
                animation: dgMesh 12s ease-in-out infinite;
            }

            @keyframes dgMesh {

                0%,
                100% {
                    background-position: 0% 50%;
                }

                50% {
                    background-position: 100% 50%;
                }
            }

            .dg-project-media>span {
                font-size: 76px;
                font-weight: 800;
                background: linear-gradient(135deg, #fff, var(--dg-cyan));
                -webkit-background-clip: text;
                background-clip: text;
                -webkit-text-fill-color: transparent;
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
                background: var(--dg-cyan);
                box-shadow: 0 0 18px 3px var(--dg-cyan);
                opacity: 0;
            }

            .dg-project:hover .dg-project-scan {
                opacity: 1;
                animation: dgProbe 1.6s ease-in-out infinite;
            }

            @keyframes dgProbe {
                0% {
                    top: 0;
                }

                50% {
                    top: calc(100% - 2px);
                }

                100% {
                    top: 0;
                }
            }

            .dg-project-body {
                display: flex;
                flex-direction: column;
                gap: 10px;
                padding: 22px 24px 26px;
            }

            .dg-project h3 {
                font-size: 20px;
                font-weight: 700;
            }

            /* ============ PARTENAIRES ============ */
            .dg-partners {
                display: flex;
                flex-wrap: wrap;
                gap: 18px;
            }

            .dg-partner {
                display: grid;
                place-items: center;
                width: 170px;
                height: 86px;
                padding: 14px 18px;
                background: rgba(255, 255, 255, .94);
                border-radius: 14px;
                text-decoration: none;
                transition: transform .35s var(--dg-ease), box-shadow .35s;
            }

            .dg-partner img {
                max-height: 100%;
                max-width: 100%;
                object-fit: contain;
                filter: grayscale(1);
                opacity: .75;
                transition: filter .3s, opacity .3s;
            }

            .dg-partner:hover {
                transform: translateY(-5px);
                box-shadow: 0 14px 34px -10px rgba(47, 216, 255, .55);
            }

            .dg-partner:hover img {
                filter: none;
                opacity: 1;
            }

            /* ============ UNE MINUTE POUR COMPRENDRE ============ */
            .dg-explain {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 24px;
            }

            .dg-explain-item {
                display: flex;
                flex-direction: column;
                text-decoration: none;
                color: inherit;
            }

            .dg-explain-item img {
                width: 100%;
                aspect-ratio: 16 / 10;
                object-fit: cover;
            }

            .dg-explain-body {
                padding: 20px 22px 24px;
            }

            .dg-explain-body h3 {
                font-size: 18px;
                font-weight: 700;
                margin-bottom: 8px;
            }

            .dg-explain-body p {
                color: var(--dg-muted);
                font-size: 15px;
            }

            /* ============ RESPONSIVE ============ */
            @media (max-width: 980px) {

                .dg-hero-grid,
                .dg-dg,
                .dg-about {
                    grid-template-columns: 1fr;
                    gap: 40px;
                }

                .dg-hero {
                    padding-bottom: 120px;
                }

                .dg-radar {
                    width: min(100%, 320px);
                }

                .dg-scroll {
                    display: none;
                }

                .dg-quick-grid,
                .dg-explain {
                    grid-template-columns: 1fr;
                }

                .dg-portrait {
                    max-width: 240px;
                }
            }

            @media (max-width: 820px) {
                .dg-missions {
                    grid-template-columns: 1fr;
                    gap: 16px;
                }

                .dg-hub-lines,
                .dg-hub-core {
                    display: none;
                }
            }

            @media (max-width: 640px) {
                .dg-section {
                    padding: 64px 0;
                }

                .dg-news-row {
                    grid-template-columns: 1fr;
                    gap: 6px;
                }

                .dg-news-row svg {
                    display: none;
                }

                .dg-lede {
                    font-size: 16.5px;
                }

                .dg-partner {
                    width: calc(50% - 9px);
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function() {
                var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                /* ----- 1. Titre « décodé » : le texte réel reste dans la page, l'effet n'est qu'un habillage ----- */
                var title = document.getElementById('dgTitle');
                if (title && !reduce) {
                    var finalText = title.textContent.trim();
                    var glyphs = 'ABCDEFGHJKLMNPQRSTUVWXYZ0123456789<>/#%&';
                    var h = title.offsetHeight;
                    title.style.minHeight = h + 'px';
                    title.setAttribute('aria-label', finalText);
                    var t0 = performance.now(), dur = 1500;

                    (function frame(now) {
                        var p = Math.min(1, (now - t0) / dur);
                        var out = '';
                        for (var i = 0; i < finalText.length; i++) {
                            var ch = finalText.charAt(i);
                            if (/[\s’'\-]/.test(ch) || i / finalText.length < p) {
                                out += ch;
                            } else {
                                out += glyphs.charAt(Math.floor(Math.random() * glyphs.length));
                            }
                        }
                        title.textContent = out;
                        if (p < 1) {
                            requestAnimationFrame(frame);
                        } else {
                            title.textContent = finalText;
                            title.style.minHeight = '';
                        }
                    })(t0);
                }

                /* ----- 2. Texte qui se tape et s'efface ----- */
                var typed = document.getElementById('dgTyped');
                if (typed && !reduce) {
                    var words = [];
                    try {
                        words = JSON.parse(typed.getAttribute('data-words'));
                    } catch (e) {}
                    if (words.length) {
                        var wi = 0, ci = 0, deleting = false;
                        typed.textContent = '';
                        (function tick() {
                            var w = words[wi];
                            if (!deleting) {
                                ci++;
                                typed.textContent = w.slice(0, ci);
                                if (ci === w.length) {
                                    deleting = true;
                                    return setTimeout(tick, 1700);
                                }
                                return setTimeout(tick, 55);
                            }
                            ci--;
                            typed.textContent = w.slice(0, ci);
                            if (ci === 0) {
                                deleting = false;
                                wi = (wi + 1) % words.length;
                                return setTimeout(tick, 320);
                            }
                            setTimeout(tick, 28);
                        })();
                    }
                }

                /* ----- 3. Compteurs animés (si $stats est fourni) ----- */
                var counters = document.querySelectorAll('[data-count]');
                if (counters.length && 'IntersectionObserver' in window && !reduce) {
                    var co = new IntersectionObserver(function(entries) {
                        entries.forEach(function(en) {
                            if (!en.isIntersecting) return;
                            var el = en.target, target = +el.getAttribute('data-count'), s = performance.now(), d = 1700;
                            co.unobserve(el);
                            (function f(now) {
                                var p = Math.min(1, (now - s) / d), e = 1 - Math.pow(1 - p, 3);
                                el.textContent = Math.round(target * e).toLocaleString('fr-FR');
                                if (p < 1) requestAnimationFrame(f);
                            })(s);
                        });
                    }, {
                        threshold: .6
                    });
                    counters.forEach(function(el) {
                        el.textContent = '0';
                        co.observe(el);
                    });
                }

                /* ----- 4. Réseau animé en fond du hero : nœuds, liaisons, paquets, réaction au curseur ----- */
                var canvas = document.getElementById('dgNet');
                if (!canvas || !canvas.getContext) return;
                var ctx = canvas.getContext('2d');
                var hero = canvas.parentElement;
                var w = 0, h = 0, pts = [], packets = [], mouse = {
                    x: -9999,
                    y: -9999
                }, running = true, frameCount = 0, raf = 0;
                var LINK = 135;

                function resize() {
                    var dpr = Math.min(window.devicePixelRatio || 1, 2);
                    var r = canvas.getBoundingClientRect();
                    w = r.width;
                    h = r.height;
                    canvas.width = w * dpr;
                    canvas.height = h * dpr;
                    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
                    var n = Math.min(95, Math.round(w * h / 13000));
                    pts = [];
                    for (var i = 0; i < n; i++) {
                        pts.push({
                            x: Math.random() * w,
                            y: Math.random() * h,
                            vx: (Math.random() - .5) * .35,
                            vy: (Math.random() - .5) * .35,
                            r: 1 + Math.random() * 1.6
                        });
                    }
                    if (reduce) draw();
                }

                function draw() {
                    ctx.clearRect(0, 0, w, h);

                    for (var i = 0; i < pts.length; i++) {
                        var a = pts[i];
                        for (var j = i + 1; j < pts.length; j++) {
                            var b = pts[j], dx = a.x - b.x, dy = a.y - b.y, d = Math.sqrt(dx * dx + dy * dy);
                            if (d < LINK) {
                                ctx.strokeStyle = 'rgba(47,216,255,' + (0.22 * (1 - d / LINK)) + ')';
                                ctx.lineWidth = 1;
                                ctx.beginPath();
                                ctx.moveTo(a.x, a.y);
                                ctx.lineTo(b.x, b.y);
                                ctx.stroke();
                            }
                        }
                        var mx = a.x - mouse.x, my = a.y - mouse.y, md = Math.sqrt(mx * mx + my * my);
                        if (md < 170) {
                            ctx.strokeStyle = 'rgba(255,201,51,' + (0.55 * (1 - md / 170)) + ')';
                            ctx.beginPath();
                            ctx.moveTo(a.x, a.y);
                            ctx.lineTo(mouse.x, mouse.y);
                            ctx.stroke();
                        }
                    }

                    for (var k = 0; k < pts.length; k++) {
                        ctx.fillStyle = 'rgba(160,225,255,.85)';
                        ctx.beginPath();
                        ctx.arc(pts[k].x, pts[k].y, pts[k].r, 0, 6.2832);
                        ctx.fill();
                    }

                    for (var p = packets.length - 1; p >= 0; p--) {
                        var pk = packets[p];
                        var x = pk.a.x + (pk.b.x - pk.a.x) * pk.t, y = pk.a.y + (pk.b.y - pk.a.y) * pk.t;
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
                            var b = pts[t], dx = a.x - b.x, dy = a.y - b.y;
                            if (b !== a && Math.sqrt(dx * dx + dy * dy) < LINK) {
                                var colors = ['#19d98b', '#ffc933', '#2fd8ff'];
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

                hero.addEventListener('pointermove', function(e) {
                    var r = canvas.getBoundingClientRect();
                    mouse.x = e.clientX - r.left;
                    mouse.y = e.clientY - r.top;
                }, {
                    passive: true
                });
                hero.addEventListener('pointerleave', function() {
                    mouse.x = mouse.y = -9999;
                });

                var resizeTimer;
                window.addEventListener('resize', function() {
                    clearTimeout(resizeTimer);
                    resizeTimer = setTimeout(resize, 150);
                });

                function setRunning(on) {
                    if (reduce || on === running) return;
                    running = on;
                    if (on) raf = requestAnimationFrame(step);
                    else cancelAnimationFrame(raf);
                }

                // On ne dessine que si le hero est visible et l'onglet actif
                if ('IntersectionObserver' in window) {
                    new IntersectionObserver(function(entries) {
                        setRunning(entries[0].isIntersecting && !document.hidden);
                    }).observe(hero);
                }
                document.addEventListener('visibilitychange', function() {
                    setRunning(!document.hidden);
                });

                resize();
                if (!reduce) raf = requestAnimationFrame(step);
            })();
        </script>
    @endpush
@endsection