{{-- resources/views/auth/partials/dgti-aside.blade.php --}}
{{-- Panneau institutionnel DGTI commun aux pages d'authentification --}}
{{-- ===================== PANNEAU INSTITUTIONNEL ===================== --}}
<aside class="dgti-aside">
    <i class="fas fa-network-wired dgti-tree" aria-hidden="true"></i>

    <div class="dgti-aside-content">
        <a href="{{ url('/') }}" class="dgti-brand" aria-label="Retour à l'accueil de la DGTI">
            <span class="dgti-logo">
                <img src="{{ isset($param_site) && $param_site->logo_url ? $param_site->logo_url : asset('images/dgti.png') }}"
                    alt="Logo de la DGTI">
            </span>
            <span class="dgti-brand-text">
                <span class="dgti-brand-name">DGTI</span>
                <span class="dgti-brand-sub">Direction Générale des Transmissions et de l'Informatique</span>
            </span>
        </a>

        <div class="dgti-aside-body">
            <p class="dgti-kicker">Ministère de la Sécurité · Burkina Faso</p>
            <h2 class="dgti-headline">Plateforme numérique de la Direction Générale des Transmissions et de l'Informatique</h2>

            <ul class="dgti-features">
                <li>
                    <i class="fas fa-laptop-code" aria-hidden="true"></i>
                    <span>Accédez aux services numériques et applications de la DGTI</span>
                </li>
                <li>
                    <i class="fas fa-file-alt" aria-hidden="true"></i>
                    <span>Soumettez et suivez vos demandes en ligne</span>
                </li>
                <li>
                    <i class="fas fa-shield-alt" aria-hidden="true"></i>
                    <span>Profitez d'un espace personnel sécurisé</span>
                </li>
            </ul>
        </div>
    </div>

    <p class="dgti-aside-footer">
        &copy; {{ date('Y') }} DGTI — Ministère de la Sécurité — Tous droits réservés
    </p>
</aside>