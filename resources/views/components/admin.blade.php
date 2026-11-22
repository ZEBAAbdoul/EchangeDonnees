{{--
    Layout d'administration SED (AdminLTE) — Système d'Échange d'Informations.
    - Logo SED (paramètre « logo_url » si défini, sinon sigle animé)
    - Charte : bleu nuit, cyan, ambre (modifiable via les variables --sed-*)
--}}

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> @yield('title', 'Administration') | {{ config('app.name') }}</title>

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('admin/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('admin/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('admin/favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('admin/favicon/site.webmanifest') }}">
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="{{ asset('admin/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet"
        href="{{ asset('admin/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/jqvmap/jqvmap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/daterangepicker/daterangepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/summernote/summernote-bs4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/dist/css/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/5.5.2/collection/components/icon/icon.min.css">

    <script src="{{ asset('admin/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('admin/plugins/jquery-ui/jquery-ui.min.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        /* ================================
           IDENTITÉ SED
        ================================= */
        :root {
            --sed-night: #061a42;
            --sed-blue: #0a3a8a;
            --sed-deep: #072554;
            --sed-sky: #1e88e5;
            --sed-cyan: #00d4ff;
            --sed-amber: #f2b705;
        }

        .sed-logo { position: relative; text-align: center; }

        .sed-logo img {
            display: block;
            max-width: 190px;
            max-height: 64px;
            object-fit: contain;
        }

        /* Sigle animé (si aucun logo défini dans les paramètres) */
        .sed-title {
            display: flex;
            justify-content: center;
            gap: 6px;
            font-family: 'Poppins', 'Source Sans Pro', sans-serif;
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: 4px;
            animation: sedAppear 1.2s ease-out both;
        }

        .sed-title span {
            display: inline-block;
            color: #fff;
            text-shadow: 0 0 5px rgba(255, 255, 255, .4), 0 0 15px rgba(0, 212, 255, .55);
            animation: sedLetter 2.6s ease-in-out infinite;
        }

        .sed-title span:nth-child(2) { animation-delay: .15s; }
        .sed-title span:nth-child(3) { animation-delay: .30s; }

        .sed-subtitle {
            margin-top: -2px;
            color: rgba(255, 255, 255, .85);
            font-size: 7.5px;
            font-weight: 600;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            opacity: 0;
            animation: sedAppear 1.2s ease .8s forwards;
        }

        /* Filet cyan → ambre sous le logo */
        .sed-logo::after {
            content: "";
            position: absolute;
            left: 10%;
            bottom: -9px;
            width: 0;
            height: 3px;
            border-radius: 3px;
            background: linear-gradient(90deg, var(--sed-cyan), var(--sed-sky) 50%, var(--sed-amber));
            animation: sedLine 1.2s ease 1s forwards;
        }

        .brand-link:hover .sed-title span { color: var(--sed-cyan); transition: .3s; }

        @keyframes sedAppear {
            from { opacity: 0; transform: translateY(-12px) scale(.9); }
            to { opacity: 1; transform: none; }
        }

        @keyframes sedLetter {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-3px); text-shadow: 0 0 8px #fff, 0 0 20px var(--sed-cyan); }
        }

        @keyframes sedLine { to { width: 80%; } }

        /* ================================
           SIDEBAR
        ================================= */
        .main-sidebar[class*="sidebar-dark"] {
            background: linear-gradient(180deg, var(--sed-night) 0%, var(--sed-blue) 55%, var(--sed-deep) 100%);
            box-shadow: 4px 0 24px rgba(6, 26, 66, .35);
        }

        .main-sidebar .brand-link {
            background: rgba(0, 0, 0, .18);
            border-bottom: 1px solid rgba(255, 255, 255, .12) !important;
        }

        /* ================================
           PROFIL UTILISATEUR
        ================================= */
        .main-sidebar .sed-user-panel {
            position: relative;
            gap: 12px;
            margin: 12px 10px !important;
            padding: 12px !important;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .14) !important;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(255, 255, 255, .10), rgba(255, 255, 255, .03));
            backdrop-filter: blur(4px);
            transition: border-color .25s, box-shadow .25s;
        }

        .main-sidebar .sed-user-panel:hover {
            border-color: rgba(242, 183, 5, .55) !important;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .25);
        }

        .main-sidebar .sed-user-panel::after {
            content: "";
            position: absolute;
            top: -30px; right: -30px;
            width: 90px; height: 90px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0, 212, 255, .30), transparent 70%);
            pointer-events: none;
        }

        .sed-avatar-ring {
            position: relative;
            flex: 0 0 auto;
            border-radius: 50%;
            line-height: 0;
            transition: transform .3s;
        }

        .sed-avatar-ring:hover { transform: scale(1.05); }

        .main-sidebar .sed-user-panel .sed-user-avatar {
            width: 48px !important;
            height: 48px !important;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, .35);
            border-radius: 50%;
            background: #fff;
        }

        .sed-status {
            position: absolute;
            right: 0; bottom: 0;
            width: 12px; height: 12px;
            border-radius: 50%;
            background: #28c76f;
            border: 2px solid var(--sed-blue);
            box-shadow: 0 0 0 0 rgba(40, 199, 111, .6);
            animation: sedPulse 2s infinite;
        }

        @keyframes sedPulse {
            70% { box-shadow: 0 0 0 7px rgba(40, 199, 111, 0); }
            100% { box-shadow: 0 0 0 0 rgba(40, 199, 111, 0); }
        }

        .main-sidebar .sed-user-panel .info {
            flex: 1 1 auto;
            min-width: 0;
            padding: 0 !important;
        }

        .sed-user-name {
            display: block;
            color: #fff;
            font-size: .95rem;
            font-weight: 700;
            line-height: 1.25;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .sed-user-name:hover { color: var(--sed-amber); }

        .sed-role-badge {
            display: inline-block;
            margin-top: 5px;
            padding: 2px 10px;
            font-size: .66rem;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
            border-radius: 20px;
            color: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .25);
        }

        .sed-role-badge.role-super { background: linear-gradient(90deg, #dc3545, #b02a37); }
        .sed-role-badge.role-admin { background: linear-gradient(90deg, #fd7e14, #e8590c); }
        .sed-role-badge.role-direction {
            background: linear-gradient(90deg, #1e88e5, #0a3a8a);
            border: 1px solid rgba(255, 255, 255, .25);
        }
        .sed-role-badge.role-user { background: linear-gradient(90deg, #6c757d, #545b62); }

        /* ================================
           RECHERCHE DANS LE MENU
        ================================= */
        .main-sidebar .form-control-sidebar,
        .main-sidebar .btn-sidebar {
            background: rgba(255, 255, 255, .08) !important;
            border: 1px solid rgba(255, 255, 255, .12) !important;
            color: #fff !important;
        }

        .main-sidebar .form-control-sidebar { border-radius: 10px 0 0 10px; }
        .main-sidebar .btn-sidebar { border-radius: 0 10px 10px 0; }
        .main-sidebar .form-control-sidebar::placeholder { color: rgba(255, 255, 255, .55); }

        .main-sidebar .form-control-sidebar:focus {
            border-color: var(--sed-sky) !important;
            box-shadow: 0 0 0 .15rem rgba(30, 136, 229, .3);
        }

        .main-sidebar .sidebar-search-results .list-group-item {
            background: #0b2c6b;
            color: #fff;
            border-color: rgba(255, 255, 255, .1);
        }

        /* ================================
           MENU
        ================================= */
        .main-sidebar .nav-header {
            padding: 16px 20px 6px;
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: 1.6px;
            text-transform: uppercase;
            color: var(--sed-amber);
            opacity: .9;
        }

        .main-sidebar .nav-sidebar .nav-link {
            position: relative;
            margin: 2px 10px;
            padding: 9px 12px;
            border-radius: 10px;
            color: rgba(255, 255, 255, .78);
            font-weight: 500;
            transition: background .25s, color .25s, transform .25s, box-shadow .25s;
        }

        .main-sidebar .nav-sidebar .nav-link .nav-icon {
            width: 1.7rem;
            color: rgba(144, 202, 249, .9);
            transition: color .25s, transform .25s;
        }

        .main-sidebar .nav-sidebar .nav-link:hover {
            background: rgba(255, 255, 255, .09);
            color: #fff;
            transform: translateX(3px);
        }

        .main-sidebar .nav-sidebar .nav-link:hover .nav-icon { color: #fff; transform: scale(1.1); }

        .main-sidebar .nav-sidebar>.nav-item>.nav-link.active,
        .main-sidebar .nav-sidebar>.nav-item.menu-open>.nav-link.active {
            background: linear-gradient(90deg, var(--sed-sky), rgba(30, 136, 229, .55));
            color: #fff;
            box-shadow: 0 4px 14px rgba(30, 136, 229, .4);
        }

        .main-sidebar .nav-sidebar>.nav-item>.nav-link.active::before {
            content: "";
            position: absolute;
            left: -10px; top: 18%; bottom: 18%;
            width: 4px;
            border-radius: 0 4px 4px 0;
            background: var(--sed-amber);
        }

        .main-sidebar .nav-sidebar>.nav-item>.nav-link.active .nav-icon { color: #fff; }

        .main-sidebar .nav-sidebar>.nav-item.menu-open>.nav-link:not(.active) {
            background: rgba(255, 255, 255, .07);
            color: #fff;
        }

        .main-sidebar .nav-treeview {
            margin: 2px 10px 6px;
            padding: 4px 0;
            border-left: 2px solid rgba(255, 255, 255, .15);
            border-radius: 0 0 10px 10px;
            background: rgba(0, 0, 0, .16);
        }

        .main-sidebar .nav-treeview>.nav-item>.nav-link {
            margin: 2px 6px 2px 8px;
            padding: 7px 10px;
            font-size: .92rem;
        }

        .main-sidebar .nav-treeview>.nav-item>.nav-link .nav-icon { width: 1.4rem; font-size: .8rem; }

        .main-sidebar .nav-treeview>.nav-item>.nav-link.active {
            background: rgba(242, 183, 5, .14);
            color: var(--sed-amber);
            box-shadow: inset 3px 0 0 var(--sed-amber);
        }

        .main-sidebar .nav-treeview>.nav-item>.nav-link.active .nav-icon { color: var(--sed-amber); }

        .main-sidebar .nav-sidebar .nav-link>p>.right { transition: transform .3s; }
        .main-sidebar .nav-sidebar .menu-open>.nav-link>p>.right { transform: rotate(-90deg); }

        .main-sidebar .sidebar {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, .25) transparent;
        }

        .main-sidebar .sidebar::-webkit-scrollbar { width: 6px; }
        .main-sidebar .sidebar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, .25); border-radius: 6px; }

        /* ================================
           SIDEBAR RÉDUITE
        ================================= */
        .sidebar-collapse .main-sidebar:not(:hover) .sed-user-panel {
            margin: 8px 4px !important;
            padding: 6px !important;
            justify-content: center;
        }

        .sidebar-collapse .main-sidebar:not(:hover) .sed-user-panel .info { display: none; }
        .sidebar-collapse .main-sidebar:not(:hover) .nav-sidebar .nav-link { margin: 2px 4px; }
        .sidebar-collapse .main-sidebar:not(:hover) .nav-sidebar>.nav-item>.nav-link.active::before { left: -4px; }

        /* ================================
           FOND DES PAGES (identique à la page de connexion)
        ================================= */
        body {
            background: radial-gradient(circle at 20% 30%, #0a192f, #020c1b) fixed !important;
            color: #e6f1ff;
        }

        .sed-bg { position: fixed; inset: 0; z-index: -1; overflow: hidden; pointer-events: none; }

        .sed-bg .hex-grid {
            position: absolute; inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='100'%3E%3Cpath d='M30 0 L60 16 L60 50 L30 66 L0 50 L0 16 Z' fill='none' stroke='rgba(0,212,255,0.05)' stroke-width='1'/%3E%3Cpath d='M30 66 L60 82 L60 100' fill='none' stroke='rgba(0,212,255,0.05)' stroke-width='1'/%3E%3Cpath d='M30 66 L0 82 L0 100' fill='none' stroke='rgba(0,212,255,0.05)' stroke-width='1'/%3E%3C/svg%3E");
        }


        .sed-bg .glow-ball {
            position: absolute; width: 400px; height: 400px; border-radius: 50%;
            background: radial-gradient(circle, rgba(0, 212, 255, .08) 0%, transparent 70%);
            bottom: -200px; right: -150px; animation: sedGlow 12s ease-in-out infinite alternate;
        }

        .sed-bg .glow-ball-2 {
            position: absolute; width: 500px; height: 500px; border-radius: 50%;
            background: radial-gradient(circle, rgba(0, 123, 255, .10) 0%, transparent 70%);
            top: -220px; left: -180px; animation: sedGlow2 14s ease-in-out infinite alternate;
        }

        @keyframes sedGlow2 {
            from { transform: translate(0, 0) scale(1); opacity: .5; }
            to { transform: translate(60px, 40px) scale(1.15); opacity: .85; }
        }

        @keyframes sedGlow {
            from { transform: translate(0, 0) scale(1); opacity: .5; }
            to { transform: translate(-50px, -50px) scale(1.2); opacity: .8; }
        }

        .wrapper { background: transparent !important; }
        .content-wrapper { background: transparent !important; color: #e6f1ff; }
        .content-header h1, .content-wrapper .breadcrumb-item.active { color: #e6f1ff; }
        .content-wrapper .breadcrumb { background: transparent; }
        .content-wrapper .breadcrumb-item a { color: var(--sed-cyan); }
        .content-wrapper .breadcrumb-item + .breadcrumb-item::before { color: #4a6a99; }

        /* Barre du haut et pied de page en verre sombre */
        .main-header.navbar {
            background: rgba(10, 25, 47, .92) !important;
            border-bottom: 1px solid rgba(0, 212, 255, .25) !important;
            backdrop-filter: blur(10px);
        }
        .main-header .nav-link, .main-header .navbar-nav .nav-link { color: rgba(230, 241, 255, .85); }
        .main-footer {
            background: rgba(10, 25, 47, .92) !important;
            border-top: 1px solid rgba(0, 212, 255, .2) !important;
            color: #7a9cc0;
        }
        .main-footer a { color: var(--sed-cyan); }

        /* Cartes : verre sombre avec liseré cyan */
        .content-wrapper .card {
            background: rgba(10, 25, 47, .92);
            color: #e6f1ff;
            border: 1px solid rgba(0, 212, 255, .3);
            border-radius: 16px;
            backdrop-filter: blur(12px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, .5), 0 0 40px rgba(0, 212, 255, .1);
        }
        .content-wrapper .card-header {
            background: linear-gradient(135deg, rgba(0, 212, 255, .18), rgba(0, 123, 255, .18)) !important;
            color: #fff !important;
            border-bottom: 1px solid rgba(0, 212, 255, .3) !important;
        }
        .content-wrapper .card-footer { background: rgba(15, 31, 61, .6); border-top: 1px solid rgba(0, 212, 255, .2); }

        /* Liseré lumineux en haut des cartes */
        .content-wrapper .card { position: relative; }
        .content-wrapper .card::before {
            content: ""; position: absolute; top: 0; left: 12%; right: 12%; height: 2px; z-index: 1; pointer-events: none;
            background: linear-gradient(90deg, transparent, var(--sed-cyan), transparent); opacity: .7;
        }
        .content-wrapper .form-group:focus-within > label,
        .content-wrapper .form-label:has(+ .form-control:focus) { color: var(--sed-cyan); }

        /* Défilement et sélection assortis */
        html { scrollbar-color: rgba(0, 212, 255, .35) #020c1b; scrollbar-width: thin; }
        ::selection { background: rgba(0, 212, 255, .35); color: #fff; }

        /* Textes */
        .content-wrapper .text-muted { color: #7a9cc0 !important; }
        .content-wrapper .text-primary { color: var(--sed-cyan) !important; }
        .content-wrapper .border-bottom { border-bottom: 1px solid rgba(0, 212, 255, .25) !important; }
        .content-wrapper hr { border-color: rgba(0, 212, 255, .2); }
        .content-wrapper .form-label, .content-wrapper label { color: #cfe3ff; }
        .content-wrapper h1, .content-wrapper h2, .content-wrapper h3,
        .content-wrapper h4, .content-wrapper h5, .content-wrapper h6 { color: inherit; }

        /* Champs de formulaire */
        .content-wrapper .form-control, .content-wrapper .form-select, .content-wrapper .custom-select {
            background: rgba(15, 31, 61, .8);
            border: 1px solid rgba(0, 212, 255, .2);
            color: #e6f1ff;
            border-radius: 10px;
        }
        .content-wrapper .form-control:focus, .content-wrapper .form-select:focus {
            background: rgba(20, 40, 70, .9);
            border-color: var(--sed-cyan);
            color: #fff;
            box-shadow: 0 0 0 3px rgba(0, 212, 255, .1), 0 0 15px rgba(0, 212, 255, .2);
        }
        .content-wrapper .form-control::placeholder { color: rgba(122, 156, 192, .5); }
        .content-wrapper .form-control:disabled, .content-wrapper .form-control[readonly] { background: rgba(15, 31, 61, .5); }
        .content-wrapper input[type="file"]::file-selector-button {
            background: rgba(0, 212, 255, .15); color: #e6f1ff; border: 0; border-right: 1px solid rgba(0, 212, 255, .25);
        }
        .content-wrapper input[type="file"] { color: #cfe3ff; }
        .content-wrapper .border.rounded { border-color: rgba(0, 212, 255, .3) !important; background: rgba(15, 31, 61, .6); }

        /* Boutons */
        .content-wrapper .btn-primary {
            background: linear-gradient(135deg, var(--sed-cyan), #007bff) !important;
            border: 0 !important; color: #fff; font-weight: 700;
        }
        .content-wrapper .btn-primary:hover { box-shadow: 0 8px 25px rgba(0, 212, 255, .4); transform: translateY(-1px); }
        .content-wrapper .btn-secondary { background: rgba(122, 156, 192, .2); border: 1px solid rgba(122, 156, 192, .4); color: #e6f1ff; }
        .content-wrapper .btn-outline-primary { color: var(--sed-cyan); border-color: var(--sed-cyan); }
        .content-wrapper .btn-outline-primary:hover { background: var(--sed-cyan); color: #020c1b; }

        /* Alertes, modales, tableaux */
        .content-wrapper .alert-success { background: rgba(0, 230, 118, .1); border-color: rgba(0, 230, 118, .3); color: #69f0ae; }
        .content-wrapper .alert-danger { background: rgba(220, 38, 38, .1); border-color: rgba(220, 38, 38, .3); color: #f87171; }
        .content-wrapper .alert-info { background: rgba(0, 212, 255, .1); border-color: rgba(0, 212, 255, .3); color: #7fe7ff; }
        .content-wrapper .alert-warning { background: rgba(242, 183, 5, .1); border-color: rgba(242, 183, 5, .3); color: #ffd54f; }

        .modal-content { background: #0a192f; color: #e6f1ff; border: 1px solid rgba(0, 212, 255, .3); }
        .modal-header, .modal-footer { border-color: rgba(0, 212, 255, .2); }

        .content-wrapper .table { color: #e6f1ff; }
        .content-wrapper .table thead th { border-color: rgba(0, 212, 255, .25); color: var(--sed-cyan); }
        .content-wrapper .table td, .content-wrapper .table th { border-color: rgba(0, 212, 255, .12); }
        .content-wrapper .table-striped tbody tr:nth-of-type(odd) { background: rgba(255, 255, 255, .03); }
        .content-wrapper .table-hover tbody tr:hover { background: rgba(0, 212, 255, .07); color: #fff; }
        .content-wrapper .dataTables_wrapper, .content-wrapper .dataTables_info,
        .content-wrapper .dataTables_length, .content-wrapper .dataTables_filter { color: #9bb6d6; }
        .content-wrapper .page-link { background: rgba(15, 31, 61, .8); border-color: rgba(0, 212, 255, .2); color: #cfe3ff; }
        .content-wrapper .page-item.active .page-link { background: var(--sed-cyan); border-color: var(--sed-cyan); color: #020c1b; }


        /* Modales : croix de fermeture lisible sur fond sombre */
        .modal-content .btn-close { filter: invert(1) grayscale(100%) brightness(200%); opacity: .8; }
        .modal-header.bg-warning .btn-close { filter: none; }
        .modal-content .form-control, .modal-content .form-select {
            background: rgba(15, 31, 61, .8); border: 1px solid rgba(0, 212, 255, .2); color: #e6f1ff;
        }
        .modal-content .text-muted { color: #7a9cc0 !important; }
        .modal-content .bg-light { background: rgba(15, 31, 61, .8) !important; color: #e6f1ff; }

        /* DataTables (toutes les pages) */
        .content-wrapper table.dataTable, .content-wrapper table.dataTable tbody tr,
        .content-wrapper table.dataTable tbody td { background-color: transparent; color: #e6f1ff; }
        .content-wrapper table.dataTable thead th {
            background: rgba(0, 212, 255, .10); color: var(--sed-cyan);
            border-bottom: 1px solid rgba(0, 212, 255, .35);
        }
        .content-wrapper table.dataTable.no-footer { border-bottom: 1px solid rgba(0, 212, 255, .2); }
        .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_processing { color: #9bb6d6 !important; }
        .dataTables_wrapper .dataTables_processing {
            background: rgba(10, 25, 47, .95); border: 1px solid rgba(0, 212, 255, .3); border-radius: 10px;
        }
        .dataTables_wrapper .dataTables_filter input, .dataTables_wrapper .dataTables_length select {
            background: rgba(15, 31, 61, .8); border: 1px solid rgba(0, 212, 255, .25);
            border-radius: 8px; color: #e6f1ff; padding: 5px 10px;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            outline: none; border-color: var(--sed-cyan); box-shadow: 0 0 0 3px rgba(0, 212, 255, .12);
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #cfe3ff !important; border: 1px solid transparent; border-radius: 8px;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: rgba(0, 212, 255, .15) !important; border-color: rgba(0, 212, 255, .4) !important; color: #fff !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: linear-gradient(135deg, var(--sed-cyan), #007bff) !important; border-color: transparent !important; color: #fff !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover { color: #4a6a99 !important; background: transparent !important; }
        button.dt-button, div.dt-button, a.dt-button {
            background: rgba(15, 31, 61, .8) !important; border: 1px solid rgba(0, 212, 255, .3) !important;
            border-radius: 8px !important; color: #cfe3ff !important; box-shadow: none !important;
        }
        button.dt-button:hover:not(.disabled) { background: rgba(0, 212, 255, .15) !important; color: #fff !important; }
        div.dt-button-collection { background: #0a192f; border: 1px solid rgba(0, 212, 255, .3); border-radius: 10px; }

        /* Interrupteurs */
        .form-switch .form-check-input { background-color: rgba(122, 156, 192, .25); border-color: rgba(122, 156, 192, .5); cursor: pointer; }
        .form-switch .form-check-input:checked { background-color: var(--sed-cyan); border-color: var(--sed-cyan); }
        .form-check-input:focus { box-shadow: 0 0 0 3px rgba(0, 212, 255, .2); }

        @media (prefers-reduced-motion: reduce) { .sed-bg .glow-ball, .sed-bg .glow-ball-2 { animation: none; } }

        /* ================================
           ACCESSIBILITÉ
        ================================= */
        @media (prefers-reduced-motion: reduce) {
            .sed-title, .sed-title span, .sed-subtitle, .sed-logo::after {
                animation: none;
                opacity: 1;
                width: 80%;
            }

            .sed-status, .sed-avatar-ring,
            .main-sidebar .nav-sidebar .nav-link,
            .main-sidebar .nav-sidebar .nav-link .nav-icon {
                animation: none;
                transition: none;
                transform: none !important;
            }
        }
    </style>

    @yield('css')
</head>

<body class="hold-transition sidebar-mini layout-fixed {{ Auth::user()->mode }}-mode">

        @include('components.partials.sed-nasa')


    <!-- Fond animé (identique à la page de connexion) -->
    <div class="sed-bg" aria-hidden="true">
        <div class="hex-grid"></div>
        <div class="glow-ball"></div>
        <div class="glow-ball-2"></div>
    </div>

    @php
        // Logo du site (paramètre « logo_url »), mis en cache 10 minutes
        $logoAdmin = cache()->remember('admin_logo_url', 600, fn() => \App\Models\ParametresSite::value('logo_url'));

        $sidebarUser = Auth::user()->loadMissing(['roles', 'personne']);
        $sidebarNom = $sidebarUser->personne?->nom_complet ?: $sidebarUser->email;
        $sidebarAvatar = $sidebarUser->avatar ?: asset('admin/dist/img/user.png');

        $roleName = $sidebarUser->roles->first()?->name;

        // Intitulés des rôles : adaptez les clés aux rôles de votre table roles
        $rolesAffichage = [
            'super-admin' => ['label' => 'Super administrateur', 'class' => 'role-super'],
            'admin' => ['label' => 'Administrateur', 'class' => 'role-admin'],
            'direction' => ['label' => 'Direction', 'class' => 'role-direction'],
            'user' => ['label' => 'Utilisateur', 'class' => 'role-user'],
        ];

        $roleInfo = $rolesAffichage[$roleName] ?? [
            'label' => $roleName ? strtoupper($roleName) : null,
            'class' => 'role-user',
        ];
    @endphp

    <div class="wrapper">
        <x-navbar />

        <aside class="main-sidebar sidebar-{{ Auth::user()->mode }}-primary elevation-4">
            <div class="sidebar">

                <!-- Logo -->
                <a href="{{ route('admin.dashboard') }}"
                    class="brand-link d-flex align-items-center justify-content-center" style="height: 90px;"
                    aria-label="SED, tableau de bord">
                    <div class="sed-logo">
                        @if ($logoAdmin)
                            <img src="{{ asset($logoAdmin) }}" alt="Logo SED">
                        @else
                            <div class="sed-title" aria-label="SED">
                                <span>S</span><span>E</span><span>D</span>
                            </div>
                            <div class="sed-subtitle">Système d'Échange d'Informations</div>
                        @endif
                    </div>
                </a>

                <!-- Profil -->
                <div class="user-panel sed-user-panel mt-3 mb-3 d-flex align-items-center">
                    <a href="{{ route('admin.profile.edit') }}" class="sed-avatar-ring" title="Mon profil">
                        <img src="{{ $sidebarAvatar }}" class="sed-user-avatar" alt="Photo de {{ $sidebarNom }}">
                        <span class="sed-status" aria-hidden="true"></span>
                    </a>

                    <div class="info">
                        <a href="{{ route('admin.dashboard') }}" class="sed-user-name" title="{{ $sidebarNom }}">
                            {{ $sidebarNom }}
                        </a>
                        @if ($roleInfo['label'])
                            <span class="sed-role-badge {{ $roleInfo['class'] }}">{{ $roleInfo['label'] }}</span>
                        @endif
                    </div>
                </div>

                <!-- Recherche dans le menu -->
                <div class="form-inline">
                    <div class="input-group" data-widget="sidebar-search">
                        <input class="form-control form-control-sidebar bg-dark text-white" type="search"
                            placeholder="Rechercher" aria-label="Rechercher dans le menu">
                        <div class="input-group-append">
                            <button class="btn btn-sidebar bg-dark" type="button" aria-label="Rechercher">
                                <i class="fas fa-search fa-fw text-white"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <br>

                <x-sidebar />
            </div>
        </aside>

        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>@yield('title')</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Accueil</a></li>
                                <li class="breadcrumb-item active">@yield('title')</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </section>

            <section class="content">
                {{ $slot }}
            </section>
        </div>

        <footer class="main-footer">
            <strong>Copyright &copy; {{ date('Y') }} <a href="{{ url('/') }}" target="_blank"
                    rel="noopener">SED</a>.</strong>
            Système d'Échange d'Informations. Tous droits réservés.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0.0
            </div>
        </footer>
    </div>

    <script src="{{ asset('admin/plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    {{-- <script src="{{ asset('admin/plugins/chart.js/Chart.min.js') }}"></script> --}}
    <script src="{{ asset('admin/plugins/sparklines/sparkline.js') }}"></script>
    <script src="{{ asset('admin/plugins/jqvmap/jquery.vmap.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script>
    <script src="{{ asset('admin/plugins/jquery-knob/jquery.knob.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('admin/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <script src="{{ asset('admin/dist/js/adminlte.js') }}"></script>
    <script src="{{ asset('admin/dist/js/pages/dashboard.js') }}"></script>
    <script src="{{ asset('admin/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('admin/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('admin/dist/js/toastr.min.js') }}"
        integrity="sha512-VEd+nq25CkR676O+pLBnDW09R7VQX9Mdiij052gVCp5yVH3jGtH70Ho/UUv4mJDsEdTvqRCFZg0NKGiojGnUCw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        // Validation Bootstrap des formulaires .needs-validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        toastr.options = {
            "progressBar": true,
            "closeButton": true,
        }
    </script>
    <x-alert />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @yield('js')
</body>

</html>