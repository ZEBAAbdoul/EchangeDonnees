@extends('layouts.site')

@section('title', ($type === 'videos' ? 'Vidéothèque' : 'Photothèque') . ' — DGTI')
@section('description', 'Photos et vidéos de la Direction Générale des Transmissions et de l’Informatique : activités, équipements, événements et plateformes.')

{{--
    Fichier : resources/views/galerie/index.blade.php
    Variables : $photos, $videos (collections), $type ('photos' | 'videos'), $param_site.

    Dépend de app/Support/VideoEmbed.php (liens de partage Facebook) et de « php artisan storage:link ».

    Les noms de colonnes de Photo et Video ne sont pas connus : les helpers ci-dessous essaient plusieurs noms courants.
    Adaptez les listes si les vôtres diffèrent :
      Photo : image_url / url / chemin / image / fichier ; titre / legende ; description
      Video : url / lien / video_url / fichier ; miniature / thumbnail / image ; titre ; description
    Vidéos acceptées : YouTube, Vimeo, Dailymotion, Facebook (liens de partage compris), Instagram, ou fichier direct (.mp4, .webm).
    Sous chaque lecteur intégré, un lien « site d'origine » permet de regarder la vidéo si l'intégration est refusée.
--}}

@php
    $photos = $photos ?? collect();
    $videos = $videos ?? collect();

    // Adresse publique d'un fichier (URL complète, chemin absolu ou chemin du disque « storage »)
    $fichier = function ($v) {
        if (!$v) {
            return null;
        }
        if (\Illuminate\Support\Str::startsWith($v, ['http://', 'https://', 'data:'])) {
            return $v;
        }
        return \Illuminate\Support\Str::startsWith($v, '/') ? asset($v) : asset('storage/' . $v);
    };

    $premier = function ($m, array $champs) {
        foreach ($champs as $c) {
            if (!empty($m->{$c})) {
                return $m->{$c};
            }
        }
        return null;
    };

    // Identifie une vidéo : YouTube, Vimeo, Dailymotion, Facebook, Instagram ou fichier direct
    // Retour : kind (embed|file), src (adresse du lecteur), thumb (aperçu), page (adresse d'origine, pour le lien de secours)
    $analyseVideo = function ($m) use ($fichier, $premier) {
        $src   = $premier($m, ['url', 'lien', 'video_url', 'fichier']);
        $thumb = $fichier($premier($m, ['miniature', 'thumbnail', 'image', 'image_url']));
        $out   = ['kind' => 'file', 'src' => $fichier($src), 'thumb' => $thumb, 'page' => $src];

        if (!$src) {
            return $out;
        }

        // YouTube (watch, youtu.be, embed, shorts, live)
        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([\w-]{11})~', $src, $y)) {
            $out['kind']  = 'embed';
            $out['src']   = 'https://www.youtube-nocookie.com/embed/' . $y[1] . '?autoplay=1&mute=1&rel=0';
            $out['thumb'] = $thumb ?: 'https://img.youtube.com/vi/' . $y[1] . '/hqdefault.jpg';
        }
        // Vimeo
        elseif (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $src, $v)) {
            $out['kind'] = 'embed';
            $out['src']  = 'https://player.vimeo.com/video/' . $v[1] . '?autoplay=1&muted=1';
        }
        // Dailymotion
        elseif (preg_match('~(?:dailymotion\.com/(?:video|embed/video)/|dai\.ly/)([\w]+)~i', $src, $d)) {
            $out['kind']  = 'embed';
            $out['src']   = 'https://www.dailymotion.com/embed/video/' . $d[1] . '?autoplay=1&mute=1';
            $out['thumb'] = $thumb ?: 'https://www.dailymotion.com/thumbnail/video/' . $d[1];
        }
        // Facebook (videos/, watch/?v=, reel/, share/v/, fb.watch, fb.me) : l'adresse réelle est résolue côté serveur
        elseif (preg_match('~(?:facebook\.com|fb\.watch|fb\.me)/~i', $src)) {
            $fb = \App\Support\VideoEmbed::facebook($src);
            $out['kind']  = 'embed';
            $out['src']   = $fb['embed'];
            $out['thumb'] = $thumb ?: $fb['thumb'];
            $out['page']  = $fb['canonical'];
        }
        // Instagram (posts, reels, tv)
        elseif (preg_match('~instagram\.com/(?:p|reel|reels|tv)/([\w-]+)~i', $src, $i)) {
            $out['kind'] = 'embed';
            $out['src']  = 'https://www.instagram.com/p/' . $i[1] . '/embed/';
        }

        return $out;
    };

    $pas = 12; // éléments affichés avant « Voir plus »
@endphp

@section('content')

    <!-- ===================== EN-TÊTE ===================== -->
    <section class="dgg-head">
        <div class="dg-wrap">
            <nav class="dgg-crumbs" aria-label="Fil d’Ariane">
                <a href="{{ url('/') }}">Accueil</a>
                <span aria-hidden="true">›</span>
                <span>Ressources</span>
                <span aria-hidden="true">›</span>
                <span id="dggCrumb">{{ $type === 'videos' ? 'Vidéothèque' : 'Photothèque' }}</span>
            </nav>
            <h1>Photos et vidéos</h1>
            <p class="dgg-lede">Les images et les films qui montrent le travail de la DGTI : équipes, équipements, événements et plateformes.</p>
        </div>
    </section>

    <!-- ===================== GALERIE ===================== -->
    <section class="dgg-section">
        <div class="dg-wrap">

            <div class="dgg-tabs" role="tablist" aria-label="Type de média">
                <a href="{{ url()->current() }}?type=photos" class="dgg-tab {{ $type === 'photos' ? 'is-on' : '' }}" role="tab"
                    id="tab-photos" aria-controls="panel-photos" aria-selected="{{ $type === 'photos' ? 'true' : 'false' }}"
                    data-tab="photos">Photos <b>{{ $photos->count() }}</b></a>
                <a href="{{ url()->current() }}?type=videos" class="dgg-tab {{ $type === 'videos' ? 'is-on' : '' }}" role="tab"
                    id="tab-videos" aria-controls="panel-videos" aria-selected="{{ $type === 'videos' ? 'true' : 'false' }}"
                    data-tab="videos">Vidéos <b>{{ $videos->count() }}</b></a>
            </div>

            <!-- Photos -->
            <div id="panel-photos" class="dgg-panel" role="tabpanel" aria-labelledby="tab-photos"
                @if ($type !== 'photos') hidden @endif>
                @if ($photos->isEmpty())
                    <p class="dgg-empty">Aucune photo publiée pour le moment.</p>
                @else
                    <div class="dgg-grid" data-more="{{ $pas }}">
                        @foreach ($photos as $p)
                            @php
                                $src = $fichier($premier($p, ['image_url', 'url', 'chemin', 'image', 'fichier']));
                                $titre = $premier($p, ['titre', 'legende']);
                                $desc = $premier($p, ['description', 'legende']);
                            @endphp
                            @continue(!$src)
                            <button type="button" class="dgg-card dgg-photo" data-src="{{ $src }}"
                                data-title="{{ $titre }}" data-desc="{{ $desc !== $titre ? $desc : '' }}"
                                aria-label="Agrandir la photo{{ $titre ? ' : ' . $titre : '' }}">
                                <img src="{{ $src }}" alt="{{ $titre ?? '' }}" loading="lazy">
                                @if ($titre)
                                    <span class="dgg-cap">{{ $titre }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                    <p class="dgg-morewrap"><button type="button" class="dg-btn dg-btn-light dgg-more" hidden>Voir plus de photos</button></p>
                @endif
            </div>

            <!-- Vidéos -->
            <div id="panel-videos" class="dgg-panel" role="tabpanel" aria-labelledby="tab-videos"
                @if ($type !== 'videos') hidden @endif>
                @if ($videos->isEmpty())
                    <p class="dgg-empty">Aucune vidéo publiée pour le moment.</p>
                @else
                    <div class="dgg-grid dgg-grid-v" data-more="{{ $pas }}">
                        @foreach ($videos as $v)
                            @php
                                $a = $analyseVideo($v);
                                $titre = $premier($v, ['titre', 'legende']) ?? 'Vidéo';
                            @endphp
                            @continue(!$a['src'])
                            <button type="button" class="dgg-card dgg-video" data-kind="{{ $a['kind'] }}"
                                data-src="{{ $a['src'] }}" data-page="{{ $a['page'] ?? '' }}" data-title="{{ $titre }}"
                                aria-label="Lire la vidéo : {{ $titre }}">
                                <span class="dgg-thumb">
                                    @if ($a['thumb'])
                                        <img src="{{ $a['thumb'] }}" alt="" loading="lazy" referrerpolicy="no-referrer">
                                    @endif
                                    <i class="dgg-play" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5l11 7-11 7z" /></svg>
                                    </i>
                                </span>
                                <span class="dgg-vtxt">
                                    <strong>{{ $titre }}</strong>
                                    @if ($d = $premier($v, ['description']))
                                        <span>{{ \Illuminate\Support\Str::limit($d, 110) }}</span>
                                    @endif
                                </span>
                            </button>
                        @endforeach
                    </div>
                    <p class="dgg-morewrap"><button type="button" class="dg-btn dg-btn-light dgg-more" hidden>Voir plus de vidéos</button></p>
                @endif
            </div>
        </div>
    </section>

    <!-- Visionneuse photo -->
    <dialog class="dgg-dlg" id="dggPhotoDlg" aria-label="Photo agrandie">
        <button type="button" class="dgg-x" data-close aria-label="Fermer">×</button>
        <button type="button" class="dgg-nav dgg-prev" aria-label="Photo précédente">‹</button>
        <figure>
            <img id="dggBig" src="" alt="">
            <figcaption id="dggBigCap"></figcaption>
        </figure>
        <button type="button" class="dgg-nav dgg-next" aria-label="Photo suivante">›</button>
    </dialog>

    <!-- Lecteur vidéo -->
    <dialog class="dgg-dlg dgg-dlg-v" id="dggVideoDlg" aria-label="Lecteur vidéo">
        <button type="button" class="dgg-x" data-close aria-label="Fermer">×</button>
        <div class="dgg-player" id="dggPlayer"></div>
        <p class="dgg-vtitle" id="dggVTitle"></p>
        <a class="dgg-ext" id="dggExt" href="#" target="_blank" rel="noopener" hidden>
            La vidéo ne s’affiche pas ? La regarder sur le site d’origine ↗
        </a>
    </dialog>

    @push('styles')
        <style>
            /* ---------- En-tête de page ---------- */
            .dgg-head { position: relative; overflow: hidden; color: #fff; padding: 44px 0 40px; background: linear-gradient(110deg, #06307a 0%, #0a4aa6 55%, #0d62c9 100%); }
            .dgg-head::after { content: ""; position: absolute; left: 0; right: 0; bottom: 0; height: 5px; background: linear-gradient(90deg, var(--dg-sky), #69c4ff); }
            .dgg-crumbs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; font-size: 14px; color: #cfe0f7; }
            .dgg-crumbs a { color: #fff; text-decoration: none; }
            .dgg-crumbs a:hover { text-decoration: underline; }
            .dgg-head h1 { color: #fff; font: 800 clamp(28px, 4vw, 44px)/1.1 var(--dg-sans); text-transform: uppercase; }
            .dgg-lede { margin-top: 12px; max-width: 64ch; color: #eaf3ff; font-size: 17px; }

            .dgg-section { padding: 44px 0 80px; }

            /* ---------- Onglets ---------- */
            .dgg-tabs { display: flex; gap: 6px; margin-bottom: 30px; border-bottom: 2px solid var(--dg-line); }
            .dgg-tab { display: inline-flex; align-items: center; gap: 10px; margin-bottom: -2px; padding: 12px 22px; font: 700 16px/1 var(--dg-sans); color: var(--dg-muted); text-decoration: none; border-bottom: 3px solid transparent; transition: color .2s, border-color .2s; }
            .dgg-tab b { padding: 3px 9px; font: 600 13px/1.2 var(--dg-sans); color: var(--dg-brand); background: var(--dg-soft); border-radius: 999px; }
            .dgg-tab:hover { color: var(--dg-brand); }
            .dgg-tab.is-on { color: var(--dg-brand); border-bottom-color: var(--dg-brand); }
            .dgg-tab.is-on b { color: #fff; background: var(--dg-brand); }
            .dgg-tab:focus-visible { outline: 3px solid var(--dg-sky); outline-offset: 2px; }
            .dgg-panel[hidden], .dgg-card[hidden] { display: none; }

            .dgg-empty { padding: 36px; text-align: center; color: var(--dg-muted); background: var(--dg-soft); border-radius: 10px; }

            /* ---------- Grilles ---------- */
            .dgg-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 18px; }
            .dgg-grid-v { grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); }
            .dgg-card { position: relative; display: block; padding: 0; overflow: hidden; text-align: left; color: var(--dg-ink); background: #fff; border: 1px solid var(--dg-line); border-radius: 10px; cursor: pointer; transition: border-color .25s, box-shadow .25s; }
            .dgg-card:hover { border-color: var(--dg-sky); box-shadow: 0 18px 38px -26px rgba(5, 20, 60, .7); }
            .dgg-card:focus-visible { outline: 3px solid var(--dg-sky); outline-offset: 3px; }

            .dgg-photo { aspect-ratio: 4 / 3; background: var(--dg-soft); }
            .dgg-photo img { width: 100%; height: 100%; object-fit: cover; transition: transform .6s var(--dg-ease); }
            .dgg-photo:hover img { transform: scale(1.05); }
            .dgg-cap { position: absolute; left: 0; right: 0; bottom: 0; padding: 28px 14px 12px; font: 600 14.5px/1.35 var(--dg-sans); color: #fff; background: linear-gradient(0deg, rgba(5, 20, 60, .88), transparent); opacity: 0; transform: translateY(6px); transition: opacity .25s, transform .25s; }
            .dgg-photo:hover .dgg-cap, .dgg-photo:focus-visible .dgg-cap { opacity: 1; transform: none; }

            .dgg-thumb { position: relative; display: grid; place-items: center; aspect-ratio: 16 / 9; overflow: hidden; background: linear-gradient(135deg, #06307a, #0a4aa6 60%, #1e88e5); }
            .dgg-thumb img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .6s var(--dg-ease); }
            .dgg-video:hover .dgg-thumb img { transform: scale(1.04); }
            .dgg-play { position: relative; display: grid; place-items: center; width: 58px; height: 58px; color: var(--dg-brand); background: #fff; border-radius: 50%; box-shadow: 0 10px 26px -8px rgba(5, 20, 60, .7); transition: transform .25s var(--dg-ease), background .25s, color .25s; }
            .dgg-play svg { width: 24px; height: 24px; margin-left: 3px; }
            .dgg-video:hover .dgg-play { transform: scale(1.1); background: var(--dg-amber); color: #1b1500; }
            .dgg-vtxt { display: flex; flex-direction: column; gap: 4px; padding: 14px 16px 18px; }
            .dgg-vtxt strong { font: 700 17px/1.35 var(--dg-serif); color: var(--dg-navy); }
            .dgg-vtxt span { font-size: 14.5px; line-height: 1.55; color: var(--dg-muted); }

            .dgg-morewrap { margin-top: 30px; text-align: center; }
            .dgg-more[hidden] { display: none; }

            /* ---------- Fenêtres (visionneuse et lecteur) ---------- */
            .dgg-dlg { width: min(1100px, 94vw); max-height: 94vh; padding: 0; color: #fff; background: transparent; border: 0; overflow: visible; }
            .dgg-dlg::backdrop { background: rgba(3, 12, 32, .92); }
            .dgg-dlg[open] { display: flex; align-items: center; justify-content: center; gap: 12px; animation: dggIn .25s var(--dg-ease); }
            @keyframes dggIn { from { opacity: 0; transform: scale(.97); } }
            .dgg-dlg figure { margin: 0; text-align: center; min-width: 0; }
            .dgg-dlg figure img { max-width: 100%; max-height: 78vh; border-radius: 8px; }
            .dgg-dlg figcaption { margin-top: 12px; font-size: 15px; line-height: 1.5; color: #dbe8fb; }
            .dgg-x { position: absolute; top: -46px; right: 0; width: 40px; height: 40px; padding: 0; font: 400 28px/1 var(--dg-sans); color: #fff; background: rgba(255, 255, 255, .14); border: 1px solid rgba(255, 255, 255, .4); border-radius: 50%; }
            .dgg-nav { flex-shrink: 0; width: 44px; height: 64px; padding: 0 0 4px; font: 400 34px/1 var(--dg-sans); color: #fff; background: rgba(255, 255, 255, .12); border: 0; border-radius: 8px; transition: background .2s; }
            .dgg-x:hover, .dgg-nav:hover { background: rgba(255, 255, 255, .28); }
            .dgg-x:focus-visible, .dgg-nav:focus-visible, .dgg-ext:focus-visible { outline: 3px solid var(--dg-amber); outline-offset: 2px; }
            .dgg-dlg-v[open] { flex-direction: column; align-items: stretch; }
            .dgg-player { position: relative; aspect-ratio: 16 / 9; background: #000; border-radius: 8px; overflow: hidden; }
            .dgg-player iframe, .dgg-player video { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }
            .dgg-vtitle { margin-top: 12px; font: 600 17px/1.4 var(--dg-sans); color: #fff; }
            .dgg-ext { align-self: flex-start; margin-top: 4px; font: 600 15px/1.4 var(--dg-sans); color: #9fd2ff; text-decoration: underline; text-underline-offset: 4px; }
            .dgg-ext[hidden] { display: none; }

            @media (max-width: 700px) {
                .dgg-section { padding: 32px 0 60px; }
                .dgg-tab { padding: 12px 14px; }
                .dgg-grid, .dgg-grid-v { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
                .dgg-grid-v { grid-template-columns: 1fr; }
                .dgg-nav { position: absolute; bottom: -62px; width: 56px; height: 48px; }
                .dgg-prev { left: calc(50% - 64px); }
                .dgg-next { right: calc(50% - 64px); }
                .dgg-cap { opacity: 1; transform: none; font-size: 13px; }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function() {
                var tabs = [].slice.call(document.querySelectorAll('.dgg-tab'));
                var crumb = document.getElementById('dggCrumb');
                var labels = { photos: 'Photothèque', videos: 'Vidéothèque' };

                /* ----- Onglets sans rechargement (le lien ?type= reste valable sans JavaScript) ----- */
                function activate(type, push) {
                    tabs.forEach(function(t) {
                        var on = t.getAttribute('data-tab') === type;
                        t.classList.toggle('is-on', on);
                        t.setAttribute('aria-selected', on ? 'true' : 'false');
                        document.getElementById('panel-' + t.getAttribute('data-tab')).hidden = !on;
                    });
                    if (crumb) crumb.textContent = labels[type];
                    document.title = labels[type] + ' — DGTI';
                    if (push && history.replaceState) history.replaceState(null, '', '?type=' + type);
                }
                tabs.forEach(function(t) {
                    t.addEventListener('click', function(e) { e.preventDefault(); activate(t.getAttribute('data-tab'), true); });
                });

                /* ----- « Voir plus » : affichage progressif ----- */
                document.querySelectorAll('.dgg-grid').forEach(function(grid) {
                    var step = +grid.getAttribute('data-more') || 12;
                    var cards = [].slice.call(grid.children);
                    var btn = grid.parentElement.querySelector('.dgg-more');
                    var shown = step;
                    function render() {
                        cards.forEach(function(c, i) { c.hidden = i >= shown; });
                        if (btn) btn.hidden = shown >= cards.length;
                    }
                    if (btn) btn.addEventListener('click', function() {
                        var firstNew = cards[shown];
                        shown += step;
                        render();
                        if (firstNew) firstNew.focus();
                    });
                    render();
                });

                /* ----- Visionneuse photo ----- */
                var pDlg = document.getElementById('dggPhotoDlg');
                var big = document.getElementById('dggBig');
                var bigCap = document.getElementById('dggBigCap');
                var photos = [].slice.call(document.querySelectorAll('.dgg-photo'));
                var cur = 0, opener = null;

                function showPhoto(i) {
                    cur = (i + photos.length) % photos.length;
                    var p = photos[cur], t = p.getAttribute('data-title') || '', d = p.getAttribute('data-desc') || '';
                    big.src = p.getAttribute('data-src');
                    big.alt = t;
                    bigCap.textContent = [t, d].filter(Boolean).join(' — ');
                }
                photos.forEach(function(p, i) {
                    p.addEventListener('click', function() {
                        opener = p;
                        showPhoto(i);
                        pDlg.showModal();
                    });
                });
                pDlg.querySelector('.dgg-prev').addEventListener('click', function() { showPhoto(cur - 1); });
                pDlg.querySelector('.dgg-next').addEventListener('click', function() { showPhoto(cur + 1); });
                pDlg.addEventListener('keydown', function(e) {
                    if (e.key === 'ArrowLeft') showPhoto(cur - 1);
                    if (e.key === 'ArrowRight') showPhoto(cur + 1);
                });

                /* ----- Lecteur vidéo ----- */
                var vDlg = document.getElementById('dggVideoDlg');
                var player = document.getElementById('dggPlayer');
                var vTitle = document.getElementById('dggVTitle');
                var ext = document.getElementById('dggExt');

                document.querySelectorAll('.dgg-video').forEach(function(v) {
                    v.addEventListener('click', function() {
                        opener = v;
                        var src = v.getAttribute('data-src'), page = v.getAttribute('data-page'), el;
                        if (v.getAttribute('data-kind') === 'embed') {
                            el = document.createElement('iframe');
                            el.src = src;
                            el.allow = 'autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media';
                            el.allowFullscreen = true;
                            el.title = v.getAttribute('data-title');
                        } else {
                            el = document.createElement('video');
                            el.src = src;
                            el.controls = true;
                            el.autoplay = true;
                            el.playsInline = true;
                        }
                        player.replaceChildren(el);
                        vTitle.textContent = v.getAttribute('data-title');

                        /* Lien de secours : le lecteur d'une plateforme peut refuser l'intégration */
                        if (v.getAttribute('data-kind') === 'embed' && page) { ext.href = page; ext.hidden = false; }
                        else { ext.hidden = true; }

                        vDlg.showModal();
                    });
                });
                /* Arrête la lecture à la fermeture */
                vDlg.addEventListener('close', function() { player.replaceChildren(); });

                /* ----- Fermeture : bouton ×, clic sur le fond, retour du focus ----- */
                [pDlg, vDlg].forEach(function(d) {
                    d.querySelector('[data-close]').addEventListener('click', function() { d.close(); });
                    d.addEventListener('click', function(e) { if (e.target === d) d.close(); });
                    d.addEventListener('close', function() { if (opener) opener.focus(); });
                });
            })();
        </script>
    @endpush

@endsection