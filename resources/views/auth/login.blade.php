<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SED - Système d'Échange d'Informations | Portail sécurisé</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Charte SED : modifiez ces variables pour changer toute la palette */
        :root {
            --sed-accent: #00d4ff;
            --sed-accent-rgb: 0, 212, 255;
            --sed-accent-2: #007bff;
            --sed-bg-1: #0a192f;
            --sed-bg-2: #020c1b;
            --sed-text: #e6f1ff;
            --sed-muted: #7a9cc0;
            --sed-dim: #4a6a99;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: radial-gradient(circle at 20% 30%, var(--sed-bg-1), var(--sed-bg-2));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', 'Rajdhani', sans-serif;
            color: var(--sed-text);
            position: relative;
            overflow-x: hidden;
        }

        /* Fond animé */
        .bg-animation { position: fixed; inset: 0; z-index: 0; overflow: hidden; }

        .hex-grid {
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='100'%3E%3Cpath d='M30 0 L60 16 L60 50 L30 66 L0 50 L0 16 Z' fill='none' stroke='rgba(0,212,255,0.03)' stroke-width='1'/%3E%3Cpath d='M30 66 L60 82 L60 100' fill='none' stroke='rgba(0,212,255,0.03)' stroke-width='1'/%3E%3Cpath d='M30 66 L0 82 L0 100' fill='none' stroke='rgba(0,212,255,0.03)' stroke-width='1'/%3E%3C/svg%3E");
        }

        .scan-line {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 2px;
            background: linear-gradient(90deg, transparent, var(--sed-accent), var(--sed-accent), transparent);
            animation: scanMove 6s linear infinite;
            opacity: .6;
        }

        @keyframes scanMove {
            0% { top: 0; opacity: 0; }
            10% { opacity: .8; }
            90% { opacity: .6; }
            100% { top: 100%; opacity: 0; }
        }

        .glow-ball {
            position: absolute;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(var(--sed-accent-rgb), .08) 0%, transparent 70%);
            bottom: -200px; right: -150px;
            animation: floatGlow 12s ease-in-out infinite alternate;
        }

        @keyframes floatGlow {
            from { transform: translate(0, 0) scale(1); opacity: .5; }
            to { transform: translate(-50px, -50px) scale(1.2); opacity: .8; }
        }

        /* Carte */
        .login-card {
            width: 450px;
            background: rgba(10, 25, 47, .92);
            border: 1px solid rgba(var(--sed-accent-rgb), .3);
            border-radius: 16px;
            padding: 35px 32px;
            backdrop-filter: blur(12px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, .5), 0 0 40px rgba(var(--sed-accent-rgb), .1);
            position: relative;
            z-index: 10;
            animation: fadeInUp .6s ease-out;
            transition: transform .3s ease, box-shadow .3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, .6), 0 0 50px rgba(var(--sed-accent-rgb), .15);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .corner { position: absolute; width: 30px; height: 30px; border: 0 solid var(--sed-accent); opacity: .4; }
        .corner-tl { top: 12px; left: 12px; border-width: 2px 0 0 2px; }
        .corner-tr { top: 12px; right: 12px; border-width: 2px 2px 0 0; }
        .corner-bl { bottom: 12px; left: 12px; border-width: 0 0 2px 2px; }
        .corner-br { bottom: 12px; right: 12px; border-width: 0 2px 2px 0; }

        .logo { text-align: center; margin-bottom: 20px; }
        .logo img {
            height: 85px;
            filter: drop-shadow(0 0 12px rgba(var(--sed-accent-rgb), .6));
            transition: filter .3s;
        }
        .logo img:hover { filter: drop-shadow(0 0 20px rgba(var(--sed-accent-rgb), .9)); }

        .title {
            text-align: center;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: .3em;
            color: var(--sed-accent);
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .subtitle {
            text-align: center;
            font-size: 10px;
            letter-spacing: .25em;
            color: var(--sed-muted);
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .status-wrap { text-align: center; margin-bottom: 25px; }
        .status {
            font-size: 10px;
            color: #00e676;
            padding: 6px 12px;
            background: rgba(0, 230, 118, .08);
            border: 1px solid rgba(0, 230, 118, .2);
            border-radius: 30px;
            display: inline-block;
            letter-spacing: .1em;
        }
        .status i { font-size: 8px; margin-right: 5px; }

        .form-group { margin-bottom: 20px; position: relative; transition: transform .2s; }

        .input-icon {
            position: absolute;
            left: 14px; top: 50%;
            transform: translateY(-50%);
            color: var(--sed-dim);
            font-size: 14px;
            z-index: 2;
        }

        .form-control {
            background: rgba(15, 31, 61, .8);
            border: 1px solid rgba(var(--sed-accent-rgb), .2);
            color: var(--sed-text);
            border-radius: 10px;
            padding: 12px 16px 12px 42px;
            font-size: 14px;
            transition: all .3s;
        }

        .form-control:focus {
            background: rgba(20, 40, 70, .9);
            border-color: var(--sed-accent);
            box-shadow: 0 0 0 3px rgba(var(--sed-accent-rgb), .1), 0 0 15px rgba(var(--sed-accent-rgb), .2);
            color: #fff;
        }

        .form-control::placeholder { color: rgba(122, 156, 192, .4); font-size: 12px; }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 12px;
        }

        .checkbox-custom { display: flex; align-items: center; gap: 8px; cursor: pointer; }
        .checkbox-custom input { width: 16px; height: 16px; cursor: pointer; accent-color: var(--sed-accent); }
        .checkbox-custom span { color: var(--sed-muted); letter-spacing: .05em; }

        .forgot-link { color: var(--sed-accent); text-decoration: none; font-size: 11px; transition: opacity .2s; }
        .forgot-link:hover { opacity: .7; text-decoration: underline; }

        .btn-login {
            background: linear-gradient(135deg, var(--sed-accent), var(--sed-accent-2));
            border: none;
            border-radius: 50px;
            padding: 12px;
            font-weight: 700;
            letter-spacing: .15em;
            text-transform: uppercase;
            font-size: 13px;
            color: #fff;
            transition: all .3s;
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .2), transparent);
            transition: left .5s;
        }

        .btn-login:hover::before { left: 100%; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(var(--sed-accent-rgb), .4); color: #fff; }
        .btn-login:active { transform: translateY(0); }

        .footer { text-align: center; font-size: 9px; color: var(--sed-dim); margin-top: 25px; letter-spacing: .1em; }
        .footer a { color: var(--sed-accent); text-decoration: none; margin: 0 8px; }
        .footer a:hover { text-decoration: underline; }

        .alert-custom {
            background: rgba(220, 38, 38, .1);
            border: 1px solid rgba(220, 38, 38, .3);
            border-left: 3px solid #dc2626;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 20px;
            font-size: 12px;
            color: #f87171;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: opacity .5s;
        }

        .loader-overlay {
            position: fixed; inset: 0;
            background: rgba(2, 12, 27, .95);
            z-index: 1000;
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            gap: 20px;
        }

        .spinner {
            width: 50px; height: 50px;
            border: 3px solid rgba(var(--sed-accent-rgb), .2);
            border-top-color: var(--sed-accent);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .loader-text { color: var(--sed-accent); font-size: 12px; letter-spacing: .2em; text-transform: uppercase; }

        @media (max-width: 520px) {
            .login-card { width: 92%; padding: 25px 20px; margin: 20px; }
            .title { font-size: 20px; }
        }

        @media (prefers-reduced-motion: reduce) {
            .scan-line, .glow-ball, .login-card { animation: none; }
        }
    </style>
</head>

<body>

    <div class="bg-animation">
        <div class="hex-grid"></div>
        <div class="scan-line"></div>
        <div class="glow-ball"></div>
    </div>

    <div id="loader" class="loader-overlay">
        <div class="spinner"></div>
        <div class="loader-text">AUTHENTIFICATION EN COURS...</div>
        <div style="font-size: 10px; color: var(--sed-dim);">VÉRIFICATION DES ACCÈS</div>
    </div>

    <div class="login-card">
        <div class="corner corner-tl"></div>
        <div class="corner corner-tr"></div>
        <div class="corner corner-bl"></div>
        <div class="corner corner-br"></div>

        <div class="logo">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SED"
                onerror="this.src='https://placehold.co/75x75/0a192f/00d4ff?text=SED'">
        </div>

        <div class="title">SED</div>
        <div class="subtitle">Système d'Échange d'Informations</div>

        <div class="status-wrap">
            <div class="status"><i class="fas fa-circle"></i> ACCÈS SÉCURISÉ — AUTHENTIFICATION REQUISE</div>
        </div>

        @if ($errors->any())
            <div class="alert-custom">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Identifiants invalides — Vérifiez votre email et mot de passe</span>
            </div>
        @endif
        <div id="errorMessage" class="alert-custom" style="display: none;">
            <i class="fas fa-exclamation-triangle"></i>
            <span id="errorText"></span>
        </div>

        <form id="loginForm" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" name="email" class="form-control" placeholder="Email professionnel"
                    value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-group">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" name="password" class="form-control" placeholder="Mot de passe" required>
            </div>

            <div class="form-options">
                <label class="checkbox-custom">
                    <input type="checkbox" name="remember" id="remember">
                    <span>SE SOUVENIR DE MOI</span>
                </label>
                <a href="#" class="forgot-link" onclick="showForgotPassword(event)">MOT DE PASSE OUBLIÉ ?</a>
            </div>

            <button type="submit" class="btn btn-login w-100">
                <i class="fas fa-right-to-bracket" style="margin-right: 8px;"></i>
                ACCÉDER AU SYSTÈME
            </button>
        </form>

        <div class="footer">
            <i class="fas fa-shield-alt"></i> PORTAIL D'ACCÈS SÉCURISÉ — SED<br>
            <a href="#">Mentions légales</a> • <a href="#">Aide</a> • <a href="#">Contact</a>
            <div style="margin-top: 8px; font-size: 8px;">SED v1.0.0 — Connexion chiffrée TLS 1.3</div>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = this.querySelector('input[name="email"]').value;
            const password = this.querySelector('input[name="password"]').value;
            if (!email || !password) {
                e.preventDefault();
                showError('Veuillez remplir tous les champs');
                return;
            }
            document.getElementById('loader').style.display = 'flex';
        });

        function showError(message) {
            const box = document.getElementById('errorMessage');
            document.getElementById('errorText').innerText = message;
            box.style.display = 'flex';
            box.style.opacity = '1';
            setTimeout(() => {
                box.style.opacity = '0';
                setTimeout(() => { box.style.display = 'none'; }, 500);
            }, 5000);
        }

        function showForgotPassword(event) {
            event.preventDefault();
            showError('Contactez votre administrateur système pour réinitialiser votre mot de passe');
        }

        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', () => input.parentElement.style.transform = 'translateX(3px)');
            input.addEventListener('blur', () => input.parentElement.style.transform = 'translateX(0)');
        });
    </script>

    @if (session('status'))
        <script>
            showError(@json(session('status')));
        </script>
    @endif

</body>

</html>