@extends('layouts.site')

@section('title', $actualite->titre . ' — DGTI')
@section('description', \Illuminate\Support\Str::limit(strip_tags($actualite->chapo ?? $actualite->contenu), 160))

@section('content')

    <!-- ===================== EN-TÊTE ===================== -->
    <section class="dg-page-head">
        <div class="dg-wrap dg-article-wrap">
            <nav class="dg-crumbs" aria-label="Fil d’Ariane">
                <a href="{{ url('/') }}">Accueil</a>
                <span aria-hidden="true">›</span>
                <a href="{{ route('actualites.index') }}">Actualités</a>
            </nav>

            @if (!empty($actualite->type_libelle))
                <span class="dg-type-chip">{{ $actualite->type_libelle }}</span>
            @endif
            <h1>{{ $actualite->titre }}</h1>
            <p class="dg-page-meta">Publié le {{ $actualite->created_at->translatedFormat('d F Y') }}</p>
        </div>
    </section>

    <!-- ===================== ARTICLE ===================== -->
    <article class="dg-section dg-article">
        <div class="dg-wrap dg-article-wrap">

            @if (!empty($actualite->image_couverture_url))
                <figure class="dg-cover">
                    <img src="{{ $actualite->image }}" alt="{{ $actualite->titre }}" onerror="this.closest('figure').remove()">
                </figure>
            @endif

            @if (!empty($actualite->chapo))
                <p class="dg-lead">{{ $actualite->chapo }}</p>
            @endif

            <div class="dg-prose">{{ $actualite->contenu }}</div>

            @if (!empty($actualite->lien_facebook))
                <div class="dg-article-fb">
                    <a href="{{ $actualite->lien_facebook }}" target="_blank" rel="noopener noreferrer" class="dg-btn dg-btn-light">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12z" /></svg>
                        Voir sur Facebook
                    </a>
                </div>
            @endif

            <div class="dg-article-actions">
                <button type="button" class="dg-btn dg-btn-light" data-partage
                    data-titre="{{ $actualite->titre }}"
                    data-url="{{ route('actualites.show', $actualite->slug) }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <line x1="12" y1="2" x2="12" y2="15" />
                        <polyline points="16 6 12 2 8 6" />
                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
                    </svg>
                    Partager cette actualité
                </button>
                {{-- L'id btn-retour est lu par le script plus bas ; sans JavaScript, le lien mène à la liste --}}
                <a id="btn-retour" href="{{ route('actualites.index') }}" class="dg-btn dg-btn-primary">Retour aux actualités</a>
            </div>
        </div>
    </article>

    @push('styles')
        <style>
            .dg-page-head { position: relative; overflow: hidden; color: #fff; padding: 44px 0 40px; background: linear-gradient(110deg, #06307a 0%, #0a4aa6 55%, #0d62c9 100%); }
            .dg-page-head::after { content: ""; position: absolute; left: 0; right: 0; bottom: 0; height: 5px; background: linear-gradient(90deg, var(--dg-sky), #69c4ff); }
            .dg-article-wrap { max-width: 820px; }
            .dg-crumbs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; font-size: 14px; color: #cfe0f7; }
            .dg-crumbs a { color: #fff; text-decoration: none; }
            .dg-crumbs a:hover { text-decoration: underline; }
            .dg-type-chip { display: inline-block; margin-bottom: 12px; padding: 5px 12px; border-radius: 999px; font: 700 12px/1.2 var(--dg-sans); letter-spacing: .05em; text-transform: uppercase; color: #fff; background: rgba(255, 255, 255, .16); border: 1px solid rgba(255, 255, 255, .35); }
            .dg-page-head h1 { color: #fff; font: 800 clamp(26px, 3.8vw, 42px)/1.15 var(--dg-sans); }
            .dg-page-meta { margin-top: 12px; color: #cfe0f7; font-size: 14.5px; }

            .dg-article { padding: 48px 0 80px; }
            .dg-cover { margin: 0 0 30px; border: 1px solid var(--dg-line); border-radius: 10px; overflow: hidden; background: var(--dg-soft); }
            .dg-cover img { width: 100%; max-height: 440px; object-fit: cover; }
            .dg-lead { margin-bottom: 22px; font: 600 clamp(18px, 2vw, 21px)/1.55 var(--dg-serif); color: var(--dg-brand); }
            .dg-prose { white-space: pre-line; font-size: 16.5px; line-height: 1.8; color: var(--dg-ink); }
            .dg-article-fb { margin-top: 28px; }
            .dg-article-fb .dg-btn svg, .dg-article-actions .dg-btn svg { width: 17px; height: 17px; }
            .dg-article-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-top: 40px; padding-top: 28px; border-top: 1px solid var(--dg-line); }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function() {
                var btn = document.getElementById('btn-retour');
                if (!btn) return;

                // On revient à la page précédente seulement si elle appartient à ce site ; sinon le lien normal s'applique
                btn.addEventListener('click', function(e) {
                    var ref = document.referrer;
                    if (ref && ref.indexOf(window.location.origin) === 0 && window.history.length > 1) {
                        e.preventDefault();
                        window.history.back();
                    }
                });
            })();
        </script>

        @include('partials.share-action')
    @endpush

@endsection