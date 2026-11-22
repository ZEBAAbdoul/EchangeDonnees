{{--
    Thème « Centre de contrôle de mission » pour l'administration SED.
    Fichier : resources/views/partials/sed-nasa.blade.php
    Utilisation : dans le layout, juste après <body ...> :   @include('partials.sed-nasa')
    - Champ d'étoiles (canvas) avec parallaxe souris, étoiles filantes, nébuleuses et horizon terrestre
    - Écran de démarrage « initialisation des systèmes » (une fois par session)
    - Orbite animée autour du logo, balayage radar dans la sidebar, balises lumineuses
    - Cartes à repères HUD, apparition en cascade, compteurs animés, horloge de mission (T+) dans le pied de page
    - Respecte prefers-reduced-motion
--}}

<script>
    try { if (sessionStorage.getItem('sedBoot')) document.documentElement.classList.add('sed-booted'); } catch (e) {}
</script>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700;900&display=swap">

<style>
    :root {
        --nasa-red: #fc3d21;
        --hud-line: rgba(0, 212, 255, .55);
        --ease-out: cubic-bezier(.2, .8, .2, 1);
    }

    /* ---------- Fond : étoiles + nébuleuses + horizon ---------- */
    .sed-bg { display: none !important; }

    #sed-stars { position: fixed; inset: 0; z-index: -3; pointer-events: none; }

    .sed-space { position: fixed; inset: 0; z-index: -2; pointer-events: none; overflow: hidden; }

    .sed-space .nebula {
        position: absolute; width: 70vmax; height: 70vmax; border-radius: 50%; filter: blur(60px); opacity: .55;
        mix-blend-mode: screen; will-change: transform;
    }
    .sed-space .n1 { top: -30vmax; left: -20vmax; background: radial-gradient(circle, rgba(0, 110, 255, .25), transparent 65%); animation: nebDrift 40s ease-in-out infinite alternate; }
    .sed-space .n2 { bottom: -35vmax; right: -25vmax; background: radial-gradient(circle, rgba(120, 60, 255, .20), transparent 65%); animation: nebDrift 52s ease-in-out infinite alternate-reverse; }
    .sed-space .n3 { top: 25%; left: 40%; width: 40vmax; height: 40vmax; background: radial-gradient(circle, rgba(0, 212, 255, .10), transparent 65%); animation: nebDrift 36s ease-in-out infinite alternate; }

    /* Horizon terrestre lumineux en bas de l'écran */
    .sed-space .horizon {
        position: absolute; left: -20%; right: -20%; bottom: -62vh; height: 70vh; border-radius: 50%;
        background: radial-gradient(ellipse at 50% 0%, rgba(0, 212, 255, .28), rgba(0, 90, 200, .12) 30%, transparent 62%);
        box-shadow: 0 -2px 40px rgba(0, 212, 255, .25);
        border-top: 1px solid rgba(120, 220, 255, .35);
        animation: horizonPulse 8s ease-in-out infinite alternate;
    }

    /* Grille de perspective très discrète */
    .sed-space .grid {
        position: absolute; left: -50%; right: -50%; bottom: 0; height: 45vh; opacity: .35;
        background-image: linear-gradient(rgba(0, 212, 255, .10) 1px, transparent 1px), linear-gradient(90deg, rgba(0, 212, 255, .10) 1px, transparent 1px);
        background-size: 60px 60px;
        transform: perspective(500px) rotateX(62deg); transform-origin: bottom;
        -webkit-mask-image: linear-gradient(to top, #000, transparent 85%); mask-image: linear-gradient(to top, #000, transparent 85%);
        animation: gridFlow 6s linear infinite;
    }

    @keyframes nebDrift { to { transform: translate(6vmax, 4vmax) scale(1.15); } }
    @keyframes horizonPulse { from { opacity: .65; } to { opacity: 1; } }
    @keyframes gridFlow { to { background-position: 0 60px; } }

    /* ---------- Écran de démarrage ---------- */
    #sed-boot {
        position: fixed; inset: 0; z-index: 99999; display: flex; flex-direction: column; align-items: center; justify-content: center;
        gap: 22px; background: #020c1b; color: #cfe3ff; font-family: 'Orbitron', sans-serif;
        transition: opacity .7s ease, visibility .7s;
    }
    #sed-boot.done { opacity: 0; visibility: hidden; }
    .sed-booted #sed-boot { display: none; }

    #sed-boot .orb { position: relative; width: 120px; height: 120px; }
    #sed-boot .orb i { position: absolute; inset: 0; border-radius: 50%; border: 1px solid rgba(0, 212, 255, .5); }
    #sed-boot .orb i:nth-child(1) { animation: spin 3s linear infinite; border-top-color: var(--sed-amber); }
    #sed-boot .orb i:nth-child(2) { inset: 14px; animation: spin 2.2s linear infinite reverse; border-right-color: var(--sed-cyan); }
    #sed-boot .orb i:nth-child(3) { inset: 30px; animation: spin 1.5s linear infinite; border-bottom-color: #fff; }
    #sed-boot .orb b { position: absolute; inset: 50px; border-radius: 50%; background: var(--sed-cyan); box-shadow: 0 0 25px var(--sed-cyan); animation: sedPulseCore 1.4s ease-in-out infinite; }
    #sed-boot .txt { font-size: .8rem; letter-spacing: 6px; text-transform: uppercase; }
    #sed-boot .bar { width: 240px; height: 3px; background: rgba(255, 255, 255, .1); border-radius: 3px; overflow: hidden; }
    #sed-boot .bar span { display: block; height: 100%; width: 0; background: linear-gradient(90deg, var(--sed-cyan), var(--sed-amber)); animation: bootBar 1.4s var(--ease-out) forwards; }
    #sed-boot .log { font: .68rem/1.6 monospace; color: #7a9cc0; min-height: 3.2em; text-align: center; letter-spacing: 1px; }

    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes bootBar { to { width: 100%; } }
    @keyframes sedPulseCore { 50% { transform: scale(.55); opacity: .6; } }

    /* ---------- Typographie « mission control » ---------- */
    .content-header h1 {
        font-family: 'Orbitron', 'Source Sans Pro', sans-serif; font-weight: 700; font-size: 1.55rem; letter-spacing: 2px; text-transform: uppercase;
        background: linear-gradient(100deg, #fff 20%, var(--sed-cyan) 45%, #fff 60%, var(--sed-amber) 80%); background-size: 250% 100%;
        -webkit-background-clip: text; background-clip: text; color: transparent !important; animation: titleShine 7s linear infinite;
    }
    @keyframes titleShine { to { background-position: -250% 0; } }

    .sed-title { font-family: 'Orbitron', sans-serif !important; font-weight: 900; }
    .main-sidebar .nav-header { font-family: 'Orbitron', sans-serif; letter-spacing: 3px; font-size: .6rem; }

    /* ---------- Logo : orbite + satellite ---------- */
    .main-sidebar .brand-link { position: relative; overflow: hidden; }
    .main-sidebar .brand-link .sed-logo { position: relative; z-index: 2; }
    .main-sidebar .brand-link::before {
        content: ""; position: absolute; left: 50%; top: 50%; width: 210px; height: 210px; margin: -105px 0 0 -105px; border-radius: 50%; z-index: 1;
        border: 1px solid rgba(0, 212, 255, .28);
        background: radial-gradient(circle at 50% 0, var(--sed-amber) 0 3px, transparent 4px), radial-gradient(circle at 50% 100%, var(--sed-cyan) 0 2px, transparent 3px);
        transform: perspective(300px) rotateX(74deg) rotateZ(0deg);
        animation: orbit 9s linear infinite; pointer-events: none;
    }
    @keyframes orbit { to { transform: perspective(300px) rotateX(74deg) rotateZ(360deg); } }

    /* ---------- Sidebar : radar + cascade ---------- */
    .main-sidebar { overflow: hidden; border-right: 1px solid rgba(0, 212, 255, .22); }
    .main-sidebar::after {
        content: ""; position: absolute; left: 0; right: 0; top: -140px; height: 140px; z-index: 5; pointer-events: none;
        background: linear-gradient(180deg, transparent, rgba(0, 212, 255, .08), rgba(0, 212, 255, .02) 85%, transparent);
        animation: sedScan 8s linear infinite;
    }
    @keyframes sedScan { to { transform: translateY(120vh); } }

    .main-sidebar .nav-sidebar > .nav-item { animation: slideIn .6s var(--ease-out) both; }
    .main-sidebar .nav-sidebar > .nav-item:nth-child(1) { animation-delay: .10s; }
    .main-sidebar .nav-sidebar > .nav-item:nth-child(2) { animation-delay: .18s; }
    .main-sidebar .nav-sidebar > .nav-item:nth-child(3) { animation-delay: .26s; }
    .main-sidebar .nav-sidebar > .nav-item:nth-child(4) { animation-delay: .34s; }
    .main-sidebar .nav-sidebar > .nav-item:nth-child(5) { animation-delay: .42s; }
    .main-sidebar .nav-sidebar > .nav-item:nth-child(n+6) { animation-delay: .5s; }
    @keyframes slideIn { from { opacity: 0; transform: translateX(-22px); } to { opacity: 1; transform: none; } }

    /* Balise clignotante sur l'entrée active */
    .main-sidebar .nav-sidebar > .nav-item > .nav-link.active:not(:has(.right))::after {
        content: ""; position: absolute; right: 14px; top: 50%; width: 7px; height: 7px; margin-top: -3.5px; border-radius: 50%;
        background: var(--sed-amber); animation: beacon 1.8s infinite;
    }
    @keyframes beacon {
        0% { box-shadow: 0 0 0 0 rgba(242, 183, 5, .7); }
        70%, 100% { box-shadow: 0 0 0 10px rgba(242, 183, 5, 0); }
    }

    /* Avatar : anneau tournant */
    .sed-avatar-ring::before {
        content: ""; position: absolute; inset: -4px; border-radius: 50%;
        background: conic-gradient(from 0deg, var(--sed-cyan), transparent 40%, var(--sed-amber) 70%, transparent);
        -webkit-mask: radial-gradient(farthest-side, transparent calc(100% - 2px), #000 calc(100% - 1.5px));
        mask: radial-gradient(farthest-side, transparent calc(100% - 2px), #000 calc(100% - 1.5px));
        animation: spin 4s linear infinite;
    }

    /* ---------- Barre du haut : filet de données ---------- */
    .main-header.navbar { position: relative; }
    .main-header.navbar::after {
        content: ""; position: absolute; left: 0; right: 0; bottom: -1px; height: 2px;
        background: linear-gradient(90deg, transparent, var(--sed-cyan), var(--sed-amber), var(--sed-cyan), transparent); background-size: 200% 100%;
        animation: dataLine 5s linear infinite; opacity: .9;
    }
    @keyframes dataLine { to { background-position: -200% 0; } }

    /* ---------- Cartes : repères HUD + apparition ---------- */
    .content-wrapper .card {
        animation: rise .8s var(--ease-out) both;
        transition: transform .35s var(--ease-out), box-shadow .35s, border-color .35s;
    }
    .content-wrapper .card:nth-of-type(2) { animation-delay: .08s; }
    .content-wrapper .card:nth-of-type(3) { animation-delay: .16s; }
    .content-wrapper .card:nth-of-type(n+4) { animation-delay: .24s; }
    @keyframes rise { from { opacity: 0; transform: translateY(26px) scale(.985); filter: blur(4px); } to { opacity: 1; transform: none; filter: none; } }

    .content-wrapper .card:hover {
        transform: translateY(-3px); border-color: rgba(0, 212, 255, .6);
        box-shadow: 0 30px 60px rgba(0, 0, 0, .55), 0 0 55px rgba(0, 212, 255, .2);
    }
    .content-wrapper .card::after {
        content: ""; position: absolute; inset: 6px; pointer-events: none; z-index: 1;
        --c: var(--hud-line);
        background:
            linear-gradient(var(--c), var(--c)) top left / 16px 2px no-repeat,
            linear-gradient(var(--c), var(--c)) top left / 2px 16px no-repeat,
            linear-gradient(var(--c), var(--c)) top right / 16px 2px no-repeat,
            linear-gradient(var(--c), var(--c)) top right / 2px 16px no-repeat,
            linear-gradient(var(--c), var(--c)) bottom left / 16px 2px no-repeat,
            linear-gradient(var(--c), var(--c)) bottom left / 2px 16px no-repeat,
            linear-gradient(var(--c), var(--c)) bottom right / 16px 2px no-repeat,
            linear-gradient(var(--c), var(--c)) bottom right / 2px 16px no-repeat;
        opacity: .7; transition: opacity .3s, inset .3s;
    }
    .content-wrapper .card:hover::after { opacity: 1; inset: 3px; }

    /* Liseré supérieur qui « respire » */
    .content-wrapper .card::before { animation: edge 4s ease-in-out infinite alternate; }
    @keyframes edge { from { left: 25%; right: 25%; opacity: .35; } to { left: 5%; right: 5%; opacity: .95; } }

    /* Boîtes de stats */
    .content-wrapper .small-box, .content-wrapper .info-box {
        background: rgba(10, 25, 47, .9) !important; border: 1px solid rgba(0, 212, 255, .3); color: #e6f1ff; border-radius: 14px;
        backdrop-filter: blur(10px); animation: rise .8s var(--ease-out) both; overflow: hidden; position: relative;
    }
    .content-wrapper .small-box h3 { font-family: 'Orbitron', sans-serif; font-weight: 700; color: var(--sed-cyan); text-shadow: 0 0 14px rgba(0, 212, 255, .6); }
    .content-wrapper .small-box .icon { color: rgba(0, 212, 255, .15); }
    .content-wrapper .small-box::after {
        content: ""; position: absolute; top: 0; bottom: 0; width: 60px; left: -80px; transform: skewX(-20deg);
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .12), transparent); animation: shine 6s ease-in-out infinite;
    }
    @keyframes shine { 0%, 60% { left: -80px; } 100% { left: 120%; } }

    /* ---------- Boutons ---------- */
    .content-wrapper .btn { transition: transform .25s var(--ease-out), box-shadow .25s; }
    .content-wrapper .btn:active { transform: scale(.96); }
    .content-wrapper .btn-primary { position: relative; overflow: hidden; }
    .content-wrapper .btn-primary::after {
        content: ""; position: absolute; top: 0; bottom: 0; left: -60%; width: 40%; transform: skewX(-22deg);
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .45), transparent); transition: left .6s;
    }
    .content-wrapper .btn-primary:hover::after { left: 130%; }
    .content-wrapper .btn-danger { background: linear-gradient(135deg, var(--nasa-red), #b3200b); border: 0; }

    /* Lignes de tableau : balayage au survol */
    .content-wrapper .table-hover tbody tr { transition: background .25s, box-shadow .25s; }
    .content-wrapper .table-hover tbody tr:hover { box-shadow: inset 3px 0 0 var(--sed-cyan); }

    /* Modales : entrée « sas » */
    .modal.fade .modal-dialog { transform: scale(.9) translateY(-20px); transition: transform .35s var(--ease-out); }
    .modal.show .modal-dialog { transform: none; }
    .modal-backdrop.show { backdrop-filter: blur(4px); opacity: .7; }

    /* Pied de page : horloge de mission */
    #sed-met { font-family: 'Orbitron', monospace; color: var(--sed-amber); letter-spacing: 1px; }
    .sed-live { display: inline-block; width: 7px; height: 7px; margin-right: 6px; border-radius: 50%; background: var(--nasa-red); animation: beacon2 1.6s infinite; }
    @keyframes beacon2 { 0% { box-shadow: 0 0 0 0 rgba(252, 61, 33, .7); } 70%, 100% { box-shadow: 0 0 0 8px rgba(252, 61, 33, 0); } }

    /* Contenu : transition d'entrée de page */
    .content-wrapper > .content-header, .content-wrapper > .content { animation: rise .7s var(--ease-out) both; }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after { animation: none !important; transition: none !important; }
        #sed-boot { display: none !important; }
    }
</style>

<canvas id="sed-stars" aria-hidden="true"></canvas>
<div class="sed-space" aria-hidden="true">
    <div class="nebula n1"></div>
    <div class="nebula n2"></div>
    <div class="nebula n3"></div>
    <div class="grid"></div>
    <div class="horizon"></div>
</div>

<div id="sed-boot" aria-hidden="true">
    <div class="orb"><i></i><i></i><i></i><b></b></div>
    <div class="txt">SED · Mission Control</div>
    <div class="bar"><span></span></div>
    <div class="log" id="sed-boot-log">Initialisation des systèmes…</div>
</div>

<script>
    (function () {
        'use strict';
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        /* ---------- Écran de démarrage ---------- */
        var boot = document.getElementById('sed-boot');
        if (boot && !document.documentElement.classList.contains('sed-booted') && !reduce) {
            var steps = ['Initialisation des systèmes…', 'Liaison satellite établie', 'Chiffrement E2E : actif', 'Séquence de lancement : GO'];
            var log = document.getElementById('sed-boot-log'), i = 0;
            var iv = setInterval(function () { log.textContent = steps[Math.min(++i, steps.length - 1)]; }, 350);
            setTimeout(function () {
                clearInterval(iv); boot.classList.add('done');
                setTimeout(function () { boot.remove(); }, 800);
                try { sessionStorage.setItem('sedBoot', '1'); } catch (e) {}
            }, 1600);
        } else if (boot) { boot.remove(); }

        /* ---------- Champ d'étoiles ---------- */
        var c = document.getElementById('sed-stars');
        if (c) {
            var ctx = c.getContext('2d'), w, h, dpr = Math.min(window.devicePixelRatio || 1, 2);
            var stars = [], shoots = [], mx = 0, my = 0, tx = 0, ty = 0, nextShoot = 2500;

            function resize() {
                w = innerWidth; h = innerHeight;
                c.width = w * dpr; c.height = h * dpr; c.style.width = w + 'px'; c.style.height = h + 'px';
                ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
                var n = Math.min(280, Math.floor(w * h / 6500));
                stars = [];
                for (var k = 0; k < n; k++) {
                    var z = Math.random();
                    stars.push({ x: Math.random() * w, y: Math.random() * h, z: z, r: .3 + z * 1.2, p: Math.random() * 6.28, s: .6 + Math.random() * 1.6,
                        col: Math.random() < .18 ? '255,214,120' : (Math.random() < .5 ? '170,225,255' : '255,255,255') });
                }
            }

            function spawnShoot() {
                var a = (.15 + Math.random() * .3) * Math.PI;
                shoots.push({ x: Math.random() * w * .8, y: Math.random() * h * .4, vx: Math.cos(a) * 9, vy: Math.sin(a) * 9, life: 1 });
            }

            function draw(t) {
                ctx.clearRect(0, 0, w, h);
                tx += (mx - tx) * .05; ty += (my - ty) * .05;
                for (var k = 0; k < stars.length; k++) {
                    var s = stars[k];
                    var tw = .5 + .5 * Math.sin(t / 1000 * s.s + s.p);
                    var x = (((s.x - tx * s.z * 28 + t * .004 * s.z) % w) + w) % w;
                    var y = (((s.y - ty * s.z * 28) % h) + h) % h;
                    ctx.globalAlpha = .25 + tw * .75 * (.4 + s.z * .6);
                    ctx.fillStyle = 'rgb(' + s.col + ')';
                    ctx.beginPath(); ctx.arc(x, y, s.r, 0, 6.283); ctx.fill();
                    if (s.r > 1.2) { ctx.globalAlpha *= .25; ctx.beginPath(); ctx.arc(x, y, s.r * 3, 0, 6.283); ctx.fill(); }
                }
                ctx.globalAlpha = 1;
                if (t > nextShoot) { spawnShoot(); nextShoot = t + 3500 + Math.random() * 5500; }
                for (var j = shoots.length - 1; j >= 0; j--) {
                    var m = shoots[j];
                    m.x += m.vx; m.y += m.vy; m.life -= .014;
                    var g = ctx.createLinearGradient(m.x, m.y, m.x - m.vx * 14, m.y - m.vy * 14);
                    g.addColorStop(0, 'rgba(255,255,255,' + m.life + ')'); g.addColorStop(1, 'rgba(0,212,255,0)');
                    ctx.strokeStyle = g; ctx.lineWidth = 1.6;
                    ctx.beginPath(); ctx.moveTo(m.x, m.y); ctx.lineTo(m.x - m.vx * 14, m.y - m.vy * 14); ctx.stroke();
                    if (m.life <= 0) shoots.splice(j, 1);
                }
            }

            resize();
            addEventListener('resize', resize);
            if (reduce) { draw(0); }
            else {
                addEventListener('mousemove', function (e) { mx = e.clientX / w - .5; my = e.clientY / h - .5; }, { passive: true });
                (function loop(t) { if (!document.hidden) draw(t); requestAnimationFrame(loop); })(0);
            }
        }

        /* ---------- Compteurs animés + horloge de mission ---------- */
        document.addEventListener('DOMContentLoaded', function () {
            if (!reduce) {
                document.querySelectorAll('.small-box .inner h3, .sed-count').forEach(function (el) {
                    if (el.childNodes.length !== 1 || el.firstChild.nodeType !== 3) return;
                    var raw = el.textContent.trim();
                    if (!/^\d+$/.test(raw)) return;
                    var end = parseInt(raw, 10), t0 = null;
                    (function step(ts) {
                        if (t0 === null) t0 = ts;
                        var p = Math.min((ts - t0) / 1400, 1), e = 1 - Math.pow(1 - p, 3);
                        el.textContent = Math.round(end * e);
                        if (p < 1) requestAnimationFrame(step);
                    })(performance.now());
                });
            }

            var f = document.querySelector('.main-footer .float-right');
            if (f) {
                var span = document.createElement('span');
                span.className = 'mr-3';
                span.innerHTML = '<i class="sed-live"></i><b>T+</b> <span id="sed-met">00:00:00</span>';
                f.insertBefore(span, f.firstChild);
                var start = Date.now(), el = document.getElementById('sed-met');
                setInterval(function () {
                    var s = Math.floor((Date.now() - start) / 1000), p = function (n) { return String(n).padStart(2, '0'); };
                    el.textContent = p(Math.floor(s / 3600)) + ':' + p(Math.floor(s % 3600 / 60)) + ':' + p(s % 60);
                }, 1000);
            }
        });
    })();
</script>