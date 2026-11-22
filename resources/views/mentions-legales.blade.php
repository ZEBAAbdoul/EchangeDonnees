@extends('layouts.site')

@section('title', 'Mentions légales — DGTI')
@section('description', 'Mentions légales du site de la Direction Générale des Transmissions et de l’Informatique : éditeur, hébergement, propriété intellectuelle, données personnelles et cookies.')

{{--
    Page de mentions légales. Les informations propres à la structure sont dans le tableau $legal ci-dessous.
    Toute valeur laissée à null s'affiche « À compléter » (classe .dgl-todo) : renseignez-les avant la mise en ligne.
    Adresse, téléphone et courriel viennent de $param_site (adresse, telephone, email_contact).
    Les textes sont un modèle général : faites-les valider par le service juridique du ministère avant publication.
--}}

@php
    $contactUrl = url('/faq');

    $legal = [
        'structure'   => 'Direction Générale des Transmissions et de l’Informatique (DGTI)',
        'tutelle'     => 'Ministère de la Sécurité, Burkina Faso',
        'adresse'     => $param_site->adresse ?? null,
        'telephone'   => $param_site->telephone ?? null,
        'email'       => $param_site->email_contact ?? null,
        'directeur'   => null, // Directeur de la publication (en principe le Directeur général)
        'redaction'   => null, // Responsable de la rédaction, si différent
        'hebergeur'   => null, // Nom et adresse de l'hébergeur
        'conception'  => 'DGTI/DSI/SID', // Société ou personne ayant conçu et réalisé le site
        'maj'         => 'octobre 2026',
    ];

    $val = function ($v) {
        return $v ? e($v) : '<span class="dgl-todo">À compléter</span>';
    };

    $sections = [
        'editeur' => 'Éditeur du site',
        'publication' => 'Directeur de la publication',
        'hebergement' => 'Hébergement',
        'conception' => 'Conception et réalisation',
        'propriete' => 'Propriété intellectuelle',
        'donnees' => 'Données personnelles',
        'cookies' => 'Cookies',
        'liens' => 'Liens externes',
        'responsabilite' => 'Responsabilité',
        'droit' => 'Droit applicable',
        'contact' => 'Contact',
    ];
@endphp

@section('content')

    <!-- ===================== EN-TÊTE ===================== -->
    <section class="dgl-head">
        <div class="dg-wrap">
            <nav class="dgl-crumbs" aria-label="Fil d’Ariane">
                <a href="{{ url('/') }}">Accueil</a>
                <span aria-hidden="true">›</span>
                <span>Mentions légales</span>
            </nav>
            <h1>Mentions légales</h1>
            <p class="dgl-lede">
                Les informations légales relatives à l’édition, à l’hébergement et à l’utilisation de ce site.
            </p>
        </div>
    </section>

    <!-- ===================== CONTENU ===================== -->
    <section class="dgl-section">
        <div class="dg-wrap dgl-grid">

            <nav class="dgl-toc" aria-label="Sommaire">
                <p class="dgl-toc-title">Sommaire</p>
                <ul>
                    @foreach ($sections as $id => $titre)
                        <li><a href="#{{ $id }}">{{ $titre }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <article class="dgl-content">

                <section id="editeur">
                    <h2>{{ $sections['editeur'] }}</h2>
                    <p>
                        Ce site est édité par la <strong>{{ $legal['structure'] }}</strong>, structure du {{ $legal['tutelle'] }}.
                    </p>
                    <dl class="dgl-dl">
                        <div><dt>Adresse</dt><dd>{!! $val($legal['adresse']) !!}</dd></div>
                        <div><dt>Téléphone</dt><dd>{!! $val($legal['telephone']) !!}</dd></div>
                        <div>
                            <dt>Courriel</dt>
                            <dd>
                                @if ($legal['email'])
                                    <a href="mailto:{{ $legal['email'] }}">{{ $legal['email'] }}</a>
                                @else
                                    {!! $val(null) !!}
                                @endif
                            </dd>
                        </div>
                    </dl>
                </section>

                <section id="publication">
                    <h2>{{ $sections['publication'] }}</h2>
                    <dl class="dgl-dl">
                        <div><dt>Directeur de la publication</dt><dd>{!! $val($legal['directeur']) !!}</dd></div>
                        <div><dt>Responsable de la rédaction</dt><dd>{!! $val($legal['redaction']) !!}</dd></div>
                    </dl>
                </section>

                <section id="hebergement">
                    <h2>{{ $sections['hebergement'] }}</h2>
                    <dl class="dgl-dl">
                        <div><dt>Hébergeur</dt><dd>{!! $val($legal['hebergeur']) !!}</dd></div>
                    </dl>
                </section>

                <section id="conception">
                    <h2>{{ $sections['conception'] }}</h2>
                    <dl class="dgl-dl">
                        <div><dt>Conception et réalisation</dt><dd>{!! $val($legal['conception']) !!}</dd></div>
                    </dl>
                </section>

                <section id="propriete">
                    <h2>{{ $sections['propriete'] }}</h2>
                    <p>
                        L’ensemble des contenus de ce site (textes, images, logos, documents, graphismes, structure) est la propriété de la DGTI
                        ou de ses partenaires, sauf mention contraire. Toute reproduction, représentation, modification ou diffusion, totale ou
                        partielle, par quelque procédé que ce soit, est interdite sans autorisation écrite préalable de la DGTI.
                    </p>
                    <p>
                        Les armoiries, emblèmes et logos des institutions de l’État sont protégés. Leur usage est réservé aux cas prévus par la réglementation en vigueur.
                    </p>
                </section>

                <section id="donnees">
                    <h2>{{ $sections['donnees'] }}</h2>
                    <p>
                        La DGTI traite les données personnelles dans le respect de la législation burkinabè en vigueur relative à la protection
                        des personnes à l’égard du traitement des données à caractère personnel.
                    </p>
                    <p>
                        Les informations que vous transmettez par courriel ou par formulaire (nom, adresse électronique, contenu du message)
                        sont utilisées uniquement pour traiter votre demande. Elles ne sont ni vendues ni cédées à des tiers, et elles sont
                        conservées le temps nécessaire à ce traitement.
                    </p>
                    <p>
                        Vous disposez d’un droit d’accès, de rectification et d’opposition sur les données vous concernant.
                        Pour l’exercer, écrivez
                        @if ($legal['email'])
                            à <a href="mailto:{{ $legal['email'] }}">{{ $legal['email'] }}</a>
                        @else
                            à l’adresse de contact indiquée ci-dessous
                        @endif
                        en précisant l’objet de votre demande.
                    </p>
                </section>

                <section id="cookies">
                    <h2>{{ $sections['cookies'] }}</h2>
                    <p>
                        Ce site peut utiliser des cookies techniques, nécessaires à son bon fonctionnement (session, sécurité, préférences d’affichage).
                        Si des outils de mesure d’audience ou des services tiers sont ajoutés, ils seront signalés ici et vous pourrez les refuser.
                    </p>
                    <p>
                        Vous pouvez à tout moment supprimer ou bloquer les cookies depuis les réglages de votre navigateur. Le blocage des cookies techniques peut toutefois limiter certaines fonctions du site.
                    </p>
                </section>

                <section id="liens">
                    <h2>{{ $sections['liens'] }}</h2>
                    <p>
                        Ce site peut contenir des liens vers d’autres sites. La DGTI n’exerce aucun contrôle sur leur contenu et décline toute responsabilité quant aux informations qui y figurent ou à l’usage qui en est fait.
                    </p>
                </section>

                <section id="responsabilite">
                    <h2>{{ $sections['responsabilite'] }}</h2>
                    <p>
                        La DGTI s’efforce de publier des informations exactes et à jour, mais ne peut garantir l’absence d’erreur ou d’omission.
                        Elle peut modifier le contenu du site à tout moment et sans préavis, et ne saurait être tenue responsable d’une interruption,
                        d’un dysfonctionnement ou d’un dommage résultant de l’accès au site ou de son utilisation.
                    </p>
                    <p>
                        Si vous constatez une erreur ou un contenu inapproprié, merci de le signaler via la rubrique <a href="#contact">Contact</a>.
                    </p>
                </section>

                <section id="droit">
                    <h2>{{ $sections['droit'] }}</h2>
                    <p>
                        Les présentes mentions légales sont régies par le droit burkinabè. En cas de litige, et à défaut de règlement amiable,
                        les juridictions burkinabè compétentes seront seules saisies.
                    </p>
                </section>

                <section id="contact">
                    <h2>{{ $sections['contact'] }}</h2>
                    <p>
                        Pour toute question relative à ce site ou à ces mentions légales,
                        @if ($legal['email'])
                            écrivez à <a href="mailto:{{ $legal['email'] }}">{{ $legal['email'] }}</a>
                        @else
                            adressez votre demande au Secrétariat particulier du Directeur général
                        @endif
                        ou consultez les <a href="{{ $contactUrl }}">questions fréquentes</a>.
                    </p>
                </section>

                <p class="dgl-maj">Dernière mise à jour : {{ $legal['maj'] }}.</p>
            </article>
        </div>
    </section>

    @push('styles')
        <style>
            /* ---------- En-tête de page ---------- */
            .dgl-head { position: relative; overflow: hidden; color: #fff; padding: 44px 0 40px; background: linear-gradient(110deg, #06307a 0%, #0a4aa6 55%, #0d62c9 100%); }
            .dgl-head::after { content: ""; position: absolute; left: 0; right: 0; bottom: 0; height: 5px; background: linear-gradient(90deg, var(--dg-sky), #69c4ff); }
            .dgl-crumbs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; font-size: 14px; color: #cfe0f7; }
            .dgl-crumbs a { color: #fff; text-decoration: none; }
            .dgl-crumbs a:hover { text-decoration: underline; }
            .dgl-head h1 { color: #fff; font: 800 clamp(28px, 4vw, 44px)/1.1 var(--dg-sans); text-transform: uppercase; }
            .dgl-lede { margin-top: 12px; max-width: 64ch; color: #eaf3ff; font-size: 17px; }

            /* ---------- Mise en page ---------- */
            .dgl-section { padding: 52px 0 80px; }
            .dgl-grid { display: grid; grid-template-columns: 230px minmax(0, 1fr); gap: 56px; align-items: start; max-width: 1020px; }

            .dgl-toc { position: sticky; top: 96px; }
            .dgl-toc-title { margin-bottom: 10px; font: 700 14px/1.3 var(--dg-sans); color: var(--dg-brand); }
            .dgl-toc ul { display: flex; flex-direction: column; border-left: 2px solid var(--dg-line); }
            .dgl-toc a { display: block; margin-left: -2px; padding: 7px 14px; font-size: 14.5px; line-height: 1.4; color: var(--dg-muted); text-decoration: none; border-left: 2px solid transparent; transition: color .2s, border-color .2s; }
            .dgl-toc a:hover, .dgl-toc a:focus-visible, .dgl-toc a.is-current { color: var(--dg-brand); border-left-color: var(--dg-brand); }
            .dgl-toc a:focus-visible { outline: 3px solid var(--dg-sky); outline-offset: 2px; }

            /* ---------- Texte ---------- */
            .dgl-content section { padding: 0 0 30px; margin-bottom: 30px; border-bottom: 1px solid var(--dg-line); scroll-margin-top: 90px; }
            .dgl-content section:last-of-type { border-bottom: 0; margin-bottom: 8px; }
            .dgl-content h2 { margin-bottom: 14px; font: 700 clamp(21px, 2.4vw, 26px)/1.3 var(--dg-serif); color: var(--dg-navy); }
            .dgl-content p { max-width: 68ch; margin-bottom: 12px; font-size: 16.5px; line-height: 1.75; color: var(--dg-ink); }
            .dgl-content a { font-weight: 700; color: var(--dg-accent); }

            .dgl-dl { display: flex; flex-direction: column; max-width: 68ch; border: 1px solid var(--dg-line); border-radius: 10px; overflow: hidden; }
            .dgl-dl > div { display: grid; grid-template-columns: 210px minmax(0, 1fr); gap: 16px; padding: 12px 16px; font-size: 15.5px; line-height: 1.55; }
            .dgl-dl > div + div { border-top: 1px solid var(--dg-line); }
            .dgl-dl dt { font-weight: 700; color: var(--dg-navy); }
            .dgl-dl dd { margin: 0; color: var(--dg-ink); }

            .dgl-todo { display: inline-block; padding: 1px 9px; font-size: 13.5px; color: #7a5600; background: #fff3cf; border-radius: 999px; }

            .dgl-maj { margin-top: 8px; font-size: 14px !important; color: var(--dg-muted) !important; }

            /* ---------- Responsive ---------- */
            @media (max-width: 900px) {
                .dgl-grid { grid-template-columns: 1fr; gap: 28px; }
                .dgl-toc { position: static; padding: 16px 18px; background: var(--dg-soft); border-radius: 10px; }
                .dgl-toc ul { border-left: 0; display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); }
                .dgl-toc a { margin-left: 0; padding: 6px 0; border-left: 0; }
            }

            @media (max-width: 600px) {
                .dgl-section { padding: 40px 0 60px; }
                .dgl-dl > div { grid-template-columns: 1fr; gap: 2px; }
            }

            @media print {
                .dgl-toc { display: none; }
                .dgl-grid { grid-template-columns: 1fr; }
                .dgl-content section { break-inside: avoid; }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function() {
                /* Met en évidence dans le sommaire la rubrique en cours de lecture */
                var links = document.querySelectorAll('.dgl-toc a');
                if (!links.length || !('IntersectionObserver' in window)) return;

                var byId = {};
                links.forEach(function(a) { byId[a.getAttribute('href').slice(1)] = a; });

                var io = new IntersectionObserver(function(entries) {
                    entries.forEach(function(e) {
                        if (!e.isIntersecting) return;
                        links.forEach(function(a) { a.classList.remove('is-current'); });
                        var a = byId[e.target.id];
                        if (a) a.classList.add('is-current');
                    });
                }, { rootMargin: '-20% 0px -70% 0px' });

                Object.keys(byId).forEach(function(id) {
                    var el = document.getElementById(id);
                    if (el) io.observe(el);
                });
            })();
        </script>
    @endpush

@endsection