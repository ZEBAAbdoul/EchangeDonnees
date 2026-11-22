@extends('layouts.site')

@section('title', 'DGTI — Actualités')
@section('description', 'Suivez la vie de la DGTI : événements, formations, partenariats et communiqués.')

@section('content')

    <!-- ===================== EN-TÊTE ===================== -->
    <section class="dg-page-head">
        <div class="dg-wrap">
            <nav class="dg-crumbs" aria-label="Fil d’Ariane">
                <a href="{{ url('/') }}">Accueil</a>
                <span aria-hidden="true">›</span>
                <span>Actualités</span>
            </nav>
            <h1>Les actualités de la DGTI</h1>
            <p class="dg-page-lede">Suivez la vie de la direction : événements, formations, partenariats et communiqués.</p>
        </div>
    </section>

    <!-- ===================== LISTE ===================== -->
    <section id="actualites" class="dg-section dg-section-alt">
        <div class="dg-wrap">

            <p class="dg-count">{{ $actualites->total() }} actualité(s)</p>

            <div class="dg-ri-grid">
                @forelse ($actualites as $actualite)
                    <article class="dg-ri-card dg-reveal" style="--d:{{ ($loop->index % 3) * 0.08 }}s">
                        <a href="{{ route('actualites.show', $actualite->slug) }}" class="dg-ri-thumb"
                            aria-label="Lire « {{ $actualite->titre }} »">
                            <span class="dg-ri-initial" aria-hidden="true">{{ mb_substr($actualite->titre, 0, 1) }}</span>
                            @if (!empty($actualite->image_couverture_url))
                                <img src="{{ $actualite->image }}" alt="{{ $actualite->titre }}" loading="lazy"
                                    onerror="this.remove()">
                            @endif
                        </a>

                        <div class="dg-ri-body">
                            <div class="dg-ri-top">
                                <span class="dg-ri-type">{{ $actualite->type_libelle }}</span>
                                <time class="dg-ri-date" datetime="{{ $actualite->created_at->toDateString() }}">
                                    {{ $actualite->created_at->translatedFormat('d M Y') }}
                                </time>
                            </div>

                            <h3>
                                <a href="{{ route('actualites.show', $actualite->slug) }}">{{ $actualite->titre }}</a>
                            </h3>

                            <p>{{ \Illuminate\Support\Str::limit($actualite->chapo ?? $actualite->resume, 150) }}</p>

                            <div class="dg-ri-actions">
                                <a class="dg-link" href="{{ route('actualites.show', $actualite->slug) }}">Lire la suite
                                    <span class="dg-arrow" aria-hidden="true">→</span>
                                </a>

                                <button type="button" class="dg-ri-share" data-partage
                                    data-titre="{{ $actualite->titre }}"
                                    data-url="{{ route('actualites.show', $actualite->slug) }}"
                                    aria-label="Partager « {{ $actualite->titre }} »">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <line x1="12" y1="2" x2="12" y2="15" />
                                        <polyline points="16 6 12 2 8 6" />
                                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
                                    </svg>
                                    Partager
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="dg-muted">Aucune actualité publiée pour le moment.</p>
                @endforelse
            </div>

            <div class="dg-pagination-wrap">
                {{ $actualites->links('vendor.pagination.dgti') }}
            </div>
        </div>
    </section>

    @push('styles')
        <style>
            .dg-section { padding: 56px 0 80px; }
            .dg-section-alt { background: var(--dg-soft); }
            .dg-muted { color: var(--dg-muted); }

            .dg-page-head { position: relative; overflow: hidden; color: #fff; padding: 44px 0 40px; background: linear-gradient(110deg, #06307a 0%, #0a4aa6 55%, #0d62c9 100%); }
            .dg-page-head::after { content: ""; position: absolute; left: 0; right: 0; bottom: 0; height: 5px; background: linear-gradient(90deg, var(--dg-sky), #69c4ff); }
            .dg-crumbs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; font-size: 14px; color: #cfe0f7; }
            .dg-crumbs a { color: #fff; text-decoration: none; }
            .dg-crumbs a:hover { text-decoration: underline; }
            .dg-page-head h1 { color: #fff; font: 800 clamp(28px, 4vw, 44px)/1.1 var(--dg-sans); text-transform: uppercase; }
            .dg-page-lede { margin-top: 12px; max-width: 62ch; color: #eaf3ff; font-size: 17px; }

            .dg-count { margin-bottom: 22px; font-size: 14px; color: var(--dg-muted); }

            .dg-ri-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 24px; }

            .dg-ri-card { display: flex; flex-direction: column; overflow: hidden; background: #fff; border: 1px solid var(--dg-line); border-radius: 10px; transition: box-shadow .3s, border-color .3s, transform .3s var(--dg-ease); }
            .dg-ri-card:hover { border-color: var(--dg-sky); box-shadow: 0 18px 40px -24px rgba(11, 42, 85, .6); transform: translateY(-3px); }

            /* L'initiale reste visible tant qu'aucune photo ne la recouvre */
            .dg-ri-thumb { position: relative; display: grid; place-items: center; aspect-ratio: 16 / 10; overflow: hidden; background: linear-gradient(120deg, #06307a, #0a4aa6, #1e88e5, #06307a); background-size: 300% 300%; animation: dgMesh 12s ease-in-out infinite; }
            .dg-ri-initial { font: 700 64px/1 var(--dg-serif); color: #fff; }
            .dg-ri-thumb img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .6s var(--dg-ease); }
            .dg-ri-card:hover .dg-ri-thumb img { transform: scale(1.05); }
            @keyframes dgMesh { 0%, 100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }

            .dg-ri-body { display: flex; flex: 1; flex-direction: column; padding: 20px 22px 22px; }
            .dg-ri-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px; }
            .dg-ri-type { padding: 4px 11px; border-radius: 999px; font: 700 11px/1.3 var(--dg-sans); letter-spacing: .05em; text-transform: uppercase; color: #fff; background: var(--dg-brand); }
            .dg-ri-date { font-size: 13px; color: var(--dg-muted); }
            .dg-ri-body h3 { margin: 0 0 8px; font-size: 18px; line-height: 1.35; }
            .dg-ri-body h3 a { color: var(--dg-navy); text-decoration: none; }
            .dg-ri-body h3 a:hover { color: var(--dg-accent); text-decoration: underline; text-underline-offset: 4px; }
            .dg-ri-body p { flex: 1; margin: 0 0 14px; color: var(--dg-muted); font-size: 15px; }

            .dg-ri-actions { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; margin-top: 4px; }
            .dg-ri-share { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; font: 600 13px/1.2 var(--dg-sans); color: var(--dg-brand); background: #fff; border: 1.5px solid var(--dg-brand); border-radius: 999px; transition: background .2s, color .2s; }
            .dg-ri-share svg { width: 15px; height: 15px; }
            .dg-ri-share:hover { background: var(--dg-brand); color: #fff; }

            .dg-pagination-wrap { margin-top: 36px; }
        </style>
    @endpush

    @push('scripts')
        @include('partials.share-action')
    @endpush

@endsection