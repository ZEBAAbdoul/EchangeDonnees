@extends('layouts.site')

@section('title', 'Organigramme — DGTI')
@section('description', 'L’organisation de la Direction Générale des Transmissions et de l’Informatique : services d’appui, directions techniques et antennes régionales.')

{{--
    Contenu établi d'après l'arrêté portant organisation de la DGTI (3 février 2025) et son organigramme.
    Les attributions sont reprises de façon résumée. Les données sont dans le tableau $units ci-dessous :
    elles peuvent être déplacées plus tard dans la base de données ou un fichier de configuration.
    Variable facultative : $param_site->organigramme_pdf (chemin d'un PDF à proposer au téléchargement).
--}}

@php
    $dirDefaut = 'Directeur de service, nommé par décret en Conseil des ministres sur proposition du Ministre.';
    $chefDefaut = 'Chef de service, nommé par arrêté du Ministre sur proposition du Directeur général.';

    $units = [
        'DGTI' => [
            'nom' => 'Direction Générale des Transmissions et de l’Informatique',
            'type' => 'racine',
            'mission' => 'Assurer la mise en œuvre des politiques et stratégies nationales et ministérielles relatives aux transmissions et aux systèmes d’information au sein du ministère.',
            'dirige' => 'Directeur général, nommé par décret en Conseil des ministres sur proposition du Ministre chargé de la sécurité. Il coordonne, contrôle et évalue l’ensemble des activités des directions.',
            'taches' => [
                'Coordonner la contribution du ministère à la formulation des politiques et stratégies relatives au numérique.',
                'Coordonner l’élaboration, la mise en œuvre et le suivi du schéma directeur du système d’information (SDSI), du système de management de la sécurité de l’information (SMSI) et du plan d’urbanisation du système d’information (PUSI).',
                'Réaliser, déployer, administrer et maintenir les applications numériques de la transformation digitale du ministère.',
                'Assurer la gestion du parc informatique, de l’infrastructure de communication électronique et des systèmes numériques.',
                'Renforcer les capacités du personnel en matière numérique, et assurer la formation et le support technique des utilisateurs.',
                'Assurer la cohérence, la sécurité et l’évolution du système d’information.',
                'Administrer les bases de données du ministère et mettre en œuvre la politique d’information géographique.',
                'Assurer la liaison par radio télécommunication entre le cabinet, les états-majors et les forces de sécurité intérieure.',
                'Concevoir la stratégie d’équipement des services en appareils de communication, de surveillance et d’informatique.',
            ],
        ],

        // ----- Services d'appui -----
        'SEC' => [
            'nom' => 'Secrétariat particulier', 'type' => 'appui', 'parent' => null,
            'mission' => 'Appuyer le Directeur général dans la gestion de son courrier, de ses audiences et de l’information de la direction.',
            'dirige' => 'Secrétaire particulier, nommé par arrêté du Ministre sur proposition du Directeur général (rang de chef de service).',
            'taches' => [
                'Centraliser, traiter, archiver et ventiler tous les courriers de la direction générale.',
                'Centraliser et traiter les courriers des projets et programmes gérés par la direction générale.',
                'Organiser les audiences du Directeur général.',
                'Gérer les appels téléphoniques et accueillir les visiteurs.',
                'Collecter et gérer toute information utile au fonctionnement de la direction générale.',
            ],
        ],
        'SAF' => [
            'nom' => 'Service administratif et financier', 'type' => 'appui', 'parent' => null,
            'mission' => 'Gérer les ressources humaines et les moyens matériels et financiers.',
            'dirige' => $chefDefaut,
            'taches' => [
                'Planifier les besoins en ressources humaines et gérer les dossiers individuels des agents.',
                'Participer à l’organisation de la formation du personnel et assurer la sécurité au travail.',
                'Gérer la régie d’avances de la direction générale.',
                'Élaborer et exécuter le budget, et produire les rapports trimestriels d’exécution.',
                'Gérer les biens meubles et immeubles et tenir la comptabilité des matières.',
            ],
        ],
        'SSECU' => [
            'nom' => 'Service de sécurité', 'type' => 'appui', 'parent' => null,
            'mission' => 'Assurer la sécurité des installations de la direction générale.',
            'dirige' => $chefDefaut,
            'taches' => [
                'Assurer la sécurité des équipements et du personnel de la direction générale.',
                'Identifier et analyser régulièrement les risques et menaces.',
                'Mettre en place un dispositif efficace de réponse en cas d’incident.',
                'Assurer l’escorte des missions de la direction générale.',
                'Exprimer les besoins en matériels et outils de sécurité.',
            ],
        ],

        // ----- Directions techniques -----
        'DT' => [
            'nom' => 'Direction des transmissions', 'type' => 'technique', 'parent' => null,
            'mission' => 'Assurer les liaisons par radio télécommunication et la mise en œuvre de la stratégie d’équipement des forces de sécurité en matériels de télécommunication et de surveillance.',
            'dirige' => $dirDefaut,
            'services' => ['STR', 'ST', 'SVP'],
            'taches' => [
                'Établir les liaisons par radio télécommunication entre le cabinet, les états-majors et les forces de sécurité intérieure.',
                'Participer à la conception et à la mise en œuvre de la stratégie d’équipement en matériels de télécommunication et de surveillance.',
                'Élaborer les projets et programmes relatifs à la politique nationale des radios télécommunications et de la vidéo protection.',
                'Gérer les systèmes de radiocommunication et les fréquences radio attribuées au ministère.',
                'Étudier, gérer et exploiter les installations téléphoniques.',
                'Conduire les missions d’inspection et de contrôle des installations radios.',
            ],
        ],
        'STR' => [
            'nom' => 'Service des transmissions radio', 'type' => 'technique', 'parent' => 'DT',
            'mission' => 'Gérer les systèmes de communication radio du ministère.',
            'dirige' => $chefDefaut,
            'taches' => [
                'Étudier les sites d’implantation de radio communication.',
                'Suivre l’exploitation du réseau administratif de transmission (RAT).',
                'Établir les états de besoins en installation radio.',
                'Veiller au bon fonctionnement des installations et équipements radio, et émettre des avis techniques sur leur acquisition.',
                'Assurer la maintenance et le dépannage des systèmes de transmission radio.',
            ],
        ],
        'ST' => [
            'nom' => 'Service de la téléphonie', 'type' => 'technique', 'parent' => 'DT',
            'mission' => 'Gérer les systèmes de téléphonie du ministère.',
            'dirige' => $chefDefaut,
            'taches' => [
                'Établir les états de besoins en téléphonie.',
                'Veiller au bon fonctionnement des installations, des lignes et des postes téléphoniques.',
                'Acquérir et gérer la flotte téléphonique, et gérer le standard.',
                'Élaborer et mettre à jour l’annuaire téléphonique.',
                'Acquérir, avec les opérateurs nationaux, les nouvelles lignes sollicitées par le ministère.',
            ],
        ],
        'SVP' => [
            'nom' => 'Service de vidéo protection', 'type' => 'technique', 'parent' => 'DT',
            'mission' => 'Assurer l’administration et la maintenance du système de vidéo protection.',
            'dirige' => $chefDefaut,
            'taches' => [
                'Gérer le centre de données et la salle de commandement.',
                'Gérer l’infrastructure réseau du système et en assurer la sécurité physique et logique.',
                'Participer à l’identification des sites caméras et de la technologie LTE.',
                'Surveiller en permanence les principaux axes des villes et des lieux publics.',
                'Participer à la fluidité du trafic routier urbain et à la lutte contre l’insécurité routière.',
                'Alerter les services compétents en cas de détection d’incidents.',
                'Accompagner les officiers de police judiciaire dans les enquêtes judiciaires.',
            ],
        ],

        'DSI' => [
            'nom' => 'Direction des systèmes d’information', 'type' => 'technique', 'parent' => null,
            'mission' => 'Assurer la mise en œuvre des politiques et stratégies nationales et ministérielles relatives au numérique au sein du ministère.',
            'dirige' => $dirDefaut,
            'services' => ['SRS', 'SID', 'SES', 'USI'],
            'taches' => [
                'Mettre en œuvre le SDSI, le SMSI et le PUSI.',
                'Administrer les bases de données et les systèmes informatiques.',
                'Veiller à la cohérence des systèmes d’information développés.',
                'Assurer la sécurité du système d’information et la maintenance du matériel informatique.',
                'Assister les maîtrises d’ouvrage dans leurs projets de développement applicatif.',
                'Participer à l’élaboration des cahiers des charges techniques des appels d’offres.',
                'Concevoir, mettre en service et assurer le bon fonctionnement des réseaux informatiques.',
            ],
        ],
        'SRS' => [
            'nom' => 'Service réseaux et systèmes', 'type' => 'technique', 'parent' => 'DSI',
            'mission' => 'Mettre en œuvre l’interconnexion à travers la planification, la conception, l’exploitation et la maintenance des réseaux de communication du ministère.',
            'dirige' => $chefDefaut,
            'taches' => [
                'Assurer le bon fonctionnement du réseau et veiller au respect des normes de trafic.',
                'Assurer le contrôle périodique du système informatique.',
                'Assister les projets transversaux et participer à la conception des SDSI.',
                'Administrer les réseaux informatiques.',
            ],
        ],
        'SID' => [
            'nom' => 'Service ingénierie de développement', 'type' => 'technique', 'parent' => 'DSI',
            'mission' => 'Concevoir, développer et maintenir les applications et les e-services.',
            'dirige' => $chefDefaut,
            'taches' => [
                'Participer à la mise en exploitation des applications et e-services.',
                'Gérer les bases de données du ministère.',
                'Participer à la conception des SDSI.',
                'Mettre en œuvre les projets d’automatisation et d’informatisation.',
            ],
        ],
        'SES' => [
            'nom' => 'Service exploitation et support', 'type' => 'technique', 'parent' => 'DSI',
            'mission' => 'Veiller à la bonne administration et au respect des normes des systèmes d’exploitation et des supports techniques.',
            'dirige' => $chefDefaut,
            'taches' => [
                'Assurer le bon fonctionnement des applications déployées et des postes de travail.',
                'Mettre à disposition un guide de bonne pratique du matériel informatique.',
                'Conseiller la bonne utilisation des outils informatiques.',
                'Assurer la maintenance du matériel informatique.',
            ],
        ],
        'USI' => [
            'nom' => 'Urbaniste des systèmes d’information', 'type' => 'technique', 'parent' => 'DSI',
            'mission' => 'Informaticien de profil urbaniste, ou cadre ayant de bonnes connaissances de l’ensemble des processus métiers.',
            'dirige' => 'Nommé par arrêté du Ministre sur proposition du Directeur général (rang de chef de service).',
            'taches' => [
                'Participer à l’élaboration de la stratégie et du plan d’urbanisation du système d’information.',
                'Suivre la mise en œuvre de cette stratégie et de ce plan.',
                'Porter la conduite du changement pour une meilleure appropriation des solutions informatiques.',
            ],
        ],

        'DIG' => [
            'nom' => 'Direction de l’information géographique', 'type' => 'technique', 'parent' => null,
            'mission' => 'Mettre en place, gérer et promouvoir l’utilisation de l’information géographique.',
            'dirige' => $dirDefaut,
            'services' => ['SIGT', 'SPCTG'],
            'taches' => [
                'Participer à l’élaboration et à la mise en œuvre de la politique d’information géographique du ministère.',
                'Collecter, traiter et analyser les données géographiques liées à la sécurité.',
                'Acquérir et gérer les outils de système d’information géographique et de télédétection.',
                'Réaliser et maintenir une base de données à référence spatiale.',
                'Réaliser la cartographie des services de sécurité et de la situation sécuritaire du territoire.',
                'Former le personnel du ministère à l’utilisation des outils de géolocalisation.',
                'Encourager l’utilisation des drones de surveillance dans les opérations de sécurisation.',
            ],
        ],
        'SIGT' => [
            'nom' => 'Service de l’information géographique et de la télédétection', 'type' => 'technique', 'parent' => 'DIG',
            'mission' => 'Gérer les outils liés à l’information géographique et à la télédétection.',
            'dirige' => $chefDefaut,
            'taches' => [
                'Élaborer le programme d’activités relatif à l’information géographique.',
                'Collecter, traiter et analyser les données géographiques.',
                'Exploiter les outils de télédétection, notamment les drones, les avions et les satellites munis de capteurs.',
                'Réaliser et gérer les bases de données à référence spatiale.',
                'Former le personnel à l’utilisation des outils de l’information géographique.',
            ],
        ],
        'SPCTG' => [
            'nom' => 'Service de la production cartographique, du traçage et de la géolocalisation', 'type' => 'technique', 'parent' => 'DIG',
            'mission' => 'Produire des cartes de situation sécuritaire et localiser les lieux, les objets et les personnes au moyen d’outils de géolocalisation.',
            'dirige' => $chefDefaut,
            'taches' => [
                'Produire régulièrement des cartes des services de sécurité et des zones d’insécurité.',
                'Produire des cartes d’aide à la décision, d’itinéraires de patrouilles, topographiques et thématiques.',
                'Exploiter les outils et systèmes de géolocalisation.',
                'Installer et suivre des balises GPS des équipements du ministère.',
            ],
        ],

        'DESF' => [
            'nom' => 'Direction des études, de la stratégie et de la formation', 'type' => 'technique', 'parent' => null,
            'mission' => 'Assurer la conception des différents projets stratégiques et la formation du personnel en lien avec les systèmes d’information.',
            'dirige' => $dirDefaut,
            'services' => ['SEAT', 'SFPP', 'SVIT'],
            'taches' => [
                'Coordonner l’élaboration du SDSI, du SMSI et du PUSI du ministère.',
                'Concevoir les projets stratégiques liés aux technologies de l’information et de la communication.',
                'Assurer la formation et le renforcement de capacité du personnel en matière numérique.',
                'Promouvoir l’expertise du ministère dans le domaine du numérique.',
                'Analyser les offres et dossiers techniques, et participer aux processus d’acquisition du matériel technologique.',
                'Proposer des solutions technologiques innovantes.',
                'Participer aux politiques et plans de prévention et de lutte contre la cybercriminalité.',
            ],
        ],
        'SEAT' => [
            'nom' => 'Service des études et de l’analyse technique', 'type' => 'technique', 'parent' => 'DESF',
            'mission' => 'Déterminer les spécifications techniques du matériel et en assurer le respect.',
            'dirige' => $chefDefaut,
            'taches' => [
                'Analyser les offres de service et en élaborer les rapports.',
                'Participer aux études dans les commissions techniques.',
                'Participer à la réception du matériel technique et dresser les procès-verbaux.',
                'Définir les spécifications techniques des outils technologiques.',
            ],
        ],
        'SFPP' => [
            'nom' => 'Service de la formation et du perfectionnement professionnel', 'type' => 'technique', 'parent' => 'DESF',
            'mission' => 'Renforcer les capacités des personnels du ministère dans le domaine des technologies de l’information et de la communication (TIC).',
            'dirige' => $chefDefaut,
            'taches' => [
                'Identifier les besoins en perfectionnement et en formation du personnel.',
                'Organiser les formations et élaborer un plan de formation.',
                'Assurer la mise à niveau du personnel nouvellement affecté.',
                'Élaborer des supports de formation et dresser un répertoire de formateurs.',
                'Mener des actions de sensibilisation sur les bonnes pratiques et sur la cybercriminalité.',
            ],
        ],
        'SVIT' => [
            'nom' => 'Service de veille et de l’innovation technologique', 'type' => 'technique', 'parent' => 'DESF',
            'mission' => 'Assurer la veille technologique dans le domaine des systèmes d’information et de communication.',
            'dirige' => $chefDefaut,
            'taches' => [
                'Déterminer les besoins d’innovation dans le domaine des TIC.',
                'Étudier et concevoir les projets stratégiques en matière de TIC.',
                'Proposer et élaborer des documents stratégiques.',
                'Élaborer et mettre en œuvre une stratégie de communication.',
            ],
        ],

        'DASP' => [
            'nom' => 'Direction de l’administration et du suivi des programmes', 'type' => 'technique', 'parent' => null,
            'mission' => 'Assurer la planification, la coordination, l’évaluation et le suivi des activités de la DGTI.',
            'dirige' => $dirDefaut,
            'services' => ['SPPC', 'SQSSI'],
            'taches' => [
                'Coordonner, organiser et évaluer les activités.',
                'Suivre et évaluer les systèmes de la direction générale.',
                'Capitaliser les acquis des projets et programmes.',
                'Définir et mettre en œuvre la politique de sécurité du système d’information.',
                'Analyser les risques liés à l’utilisation du système d’information.',
                'Assurer la sécurité de la direction générale.',
            ],
        ],
        'SPPC' => [
            'nom' => 'Service programme, planification et capitalisation', 'type' => 'technique', 'parent' => 'DASP',
            'mission' => 'Assurer la conception, la programmation, la coordination, le suivi et l’évaluation des actions de la direction générale.',
            'dirige' => $chefDefaut,
            'taches' => [
                'Élaborer le programme d’activités et produire les rapports d’exécution.',
                'Participer à l’évaluation des performances globales de la direction générale.',
                'Élaborer les statistiques.',
                'Tenir à jour le fichier des installations de matériels de télécommunication et d’informatique.',
                'Capitaliser les acquis des projets et programmes.',
            ],
        ],
        'SQSSI' => [
            'nom' => 'Service qualité et sécurité des systèmes d’information', 'type' => 'technique', 'parent' => 'DASP',
            'mission' => 'Assurer la qualité et protéger l’intégrité des systèmes d’information.',
            'dirige' => $chefDefaut,
            'taches' => [
                'Analyser les risques et définir la politique de sécurité du système d’information.',
                'Élaborer et tenir à jour le plan de secours informatique et le plan de continuité d’activités.',
                'Assurer la qualité des applications, des e-services et des services informatiques.',
                'Surveiller les niveaux de service et définir la politique des contrôles d’accès.',
                'Élaborer des guides de bonnes pratiques en matière d’utilisation des outils TIC.',
            ],
        ],

        // ----- Antennes régionales -----
        'AR' => [
            'nom' => 'Antennes régionales des transmissions et de l’informatique', 'type' => 'regional', 'parent' => null,
            'mission' => 'Assurer la mise en œuvre des politiques et stratégies relatives aux transmissions et aux systèmes d’information au sein de la région. Ce sont des services déconcentrés dont la zone de compétence peut couvrir une ou plusieurs régions administratives.',
            'dirige' => 'Chef d’antenne, nommé par arrêté du Ministre chargé de la sécurité sur proposition du Directeur général (rang de chef de service). La création d’une antenne se fait par arrêté du Ministre.',
            'taches' => [
                'Veiller au bon fonctionnement des installations et systèmes de la direction générale dans sa zone de compétence.',
                'Assurer la liaison entre la direction générale et les services techniques régionaux en charge des transmissions et de l’informatique.',
                'Évaluer les besoins des structures au niveau régional.',
                'Contribuer à l’organisation des missions de la direction générale au niveau régional.',
            ],
        ],
    ];

    $appui = ['SEC', 'SAF', 'SSECU'];
    $directions = ['DT', 'DSI', 'DIG', 'DESF', 'DASP'];

    // Ordre d'affichage des attributions : racine, appui, chaque direction suivie de ses services, antennes
    $ordre = collect(['DGTI'])->merge($appui);
    foreach ($directions as $d) {
        $ordre->push($d);
        foreach ($units[$d]['services'] as $s) {
            $ordre->push($s);
        }
    }
    $ordre->push('AR');

    $typeLabel = ['racine' => 'Direction générale', 'appui' => 'Service d’appui', 'technique' => 'Direction technique', 'regional' => 'Antennes régionales'];
@endphp

@section('content')

    <!-- ===================== EN-TÊTE ===================== -->
    <section class="dg-page-head">
        <div class="dg-wrap">
            <nav class="dg-crumbs" aria-label="Fil d’Ariane">
                <a href="{{ url('/') }}">Accueil</a>
                <span aria-hidden="true">›</span>
                <span>Direction</span>
                <span aria-hidden="true">›</span>
                <span>Organigramme</span>
            </nav>
            <h1>Organigramme de la DGTI</h1>
            <p class="dg-page-lede">
                Sous l’autorité du Directeur général et au sein du Ministère de la Sécurité, la DGTI s’organise
                en services d’appui, en directions techniques et en antennes régionales.
            </p>
        </div>
    </section>

    <!-- ===================== ARBRE ===================== -->
    <section class="dg-section dg-section-alt">
        <div class="dg-wrap">

            <div class="dg-org-tools dg-reveal">
                <ul class="dg-legend" aria-label="Légende">
                    <li><i class="k-appui"></i> Services d’appui</li>
                    <li><i class="k-tech"></i> Directions techniques et services</li>
                    <li><i class="k-reg"></i> Antennes régionales</li>
                </ul>
                <p class="dg-org-hint">Cliquez sur une structure pour lire sa mission et ses attributions.</p>
            </div>

            <div class="dg-org" role="group" aria-label="Organigramme de la DGTI">

                <a class="dg-node dg-node-root dg-reveal" href="#u-DGTI">
                    <span class="dg-node-sig">DGTI</span>
                    <span class="dg-node-name">{{ $units['DGTI']['nom'] }}</span>
                    <span class="dg-node-sub">Directeur général</span>
                </a>
                <div class="dg-org-stem" aria-hidden="true"></div>

                <div class="dg-org-groups">

                    <section class="dg-org-group g-appui dg-reveal" style="--d:.1s">
                        <h2>Services d’appui</h2>
                        <div class="dg-org-col">
                            @foreach ($appui as $s)
                                <a class="dg-node k-appui" href="#u-{{ $s }}">
                                    <span class="dg-node-sig">{{ $s }}</span>
                                    <span class="dg-node-name">{{ $units[$s]['nom'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </section>

                    <section class="dg-org-group g-tech dg-reveal" style="--d:.2s">
                        <h2>Directions techniques</h2>
                        <div class="dg-org-dirs">
                            @foreach ($directions as $d)
                                <div class="dg-org-dir">
                                    <a class="dg-node k-tech" href="#u-{{ $d }}">
                                        <span class="dg-node-sig">{{ $d }}</span>
                                        <span class="dg-node-name">{{ $units[$d]['nom'] }}</span>
                                    </a>
                                    <ul class="dg-org-svcs">
                                        @foreach ($units[$d]['services'] as $s)
                                            <li>
                                                <a href="#u-{{ $s }}" title="{{ $units[$s]['nom'] }}">{{ $s }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="dg-org-group g-reg dg-reveal" style="--d:.3s">
                        <h2>Antennes régionales</h2>
                        <div class="dg-org-col">
                            <a class="dg-node k-reg" href="#u-AR">
                                <span class="dg-node-sig">AR</span>
                                <span class="dg-node-name">{{ $units['AR']['nom'] }}</span>
                            </a>
                        </div>
                    </section>
                </div>
            </div>

            @if (!empty($param_site->organigramme_pdf))
                <p class="dg-org-pdf">
                    <a class="dg-btn dg-btn-light" href="{{ asset($param_site->organigramme_pdf) }}" target="_blank" rel="noopener">
                        Télécharger l’organigramme (PDF)
                    </a>
                </p>
            @endif
        </div>
    </section>

    <!-- ===================== ATTRIBUTIONS ===================== -->
    <section class="dg-section" id="attributions">
        <div class="dg-wrap dg-units-wrap">
            <div class="dg-head dg-reveal">
                <div>
                    <h2>Missions et attributions</h2>
                    <p>La mission de chaque structure et ses principales attributions, d’après l’arrêté portant organisation de la DGTI (3 février 2025).</p>
                </div>
                <button type="button" class="dg-btn dg-btn-light" id="dgToggleAll" aria-pressed="false">Tout déplier</button>
            </div>

            <div class="dg-units">
                @foreach ($ordre as $s)
                    @php
                        $u = $units[$s];
                        $niveau = !empty($u['parent']) ? 'is-sub' : '';
                        $couleur = $u['type'] === 'appui' ? 'k-appui' : ($u['type'] === 'regional' ? 'k-reg' : 'k-tech');
                    @endphp
                    <details class="dg-unit {{ $niveau }} {{ $couleur }}" id="u-{{ $s }}">
                        <summary>
                            <span class="dg-unit-sig">{{ $s }}</span>
                            <span class="dg-unit-name">
                                {{ $u['nom'] }}
                                <small>
                                    @if (!empty($u['parent']))
                                        Rattaché à la {{ $units[$u['parent']]['nom'] }} ({{ $u['parent'] }})
                                    @else
                                        {{ $typeLabel[$u['type']] }}
                                    @endif
                                </small>
                            </span>
                            <svg class="dg-unit-chev" viewBox="0 0 12 8" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 1l5 5 5-5" /></svg>
                        </summary>

                        <div class="dg-unit-body">
                            <p class="dg-unit-mission"><strong>Mission.</strong> {{ $u['mission'] }}</p>

                            @if (!empty($u['taches']))
                                <h3>Principales attributions</h3>
                                <ul>
                                    @foreach ($u['taches'] as $t)
                                        <li>{{ $t }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            @if (!empty($u['services']))
                                <p class="dg-unit-sub">
                                    <strong>Services rattachés :</strong>
                                    @foreach ($u['services'] as $sv)
                                        <a href="#u-{{ $sv }}">{{ $sv }}</a>@if (!$loop->last), @endif
                                    @endforeach
                                </p>
                            @endif

                            <p class="dg-unit-lead"><strong>Direction.</strong> {{ $u['dirige'] }}</p>
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    @push('styles')
        <style>
            .dg-section { padding: 56px 0 80px; }
            .dg-section-alt { background: var(--dg-soft); }

            /* ---------- En-tête de page ---------- */
            .dg-page-head { position: relative; overflow: hidden; color: #fff; padding: 44px 0 40px; background: linear-gradient(110deg, #06307a 0%, #0a4aa6 55%, #0d62c9 100%); }
            .dg-page-head::after { content: ""; position: absolute; left: 0; right: 0; bottom: 0; height: 5px; background: linear-gradient(90deg, var(--dg-sky), #69c4ff); }
            .dg-crumbs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; font-size: 14px; color: #cfe0f7; }
            .dg-crumbs a { color: #fff; text-decoration: none; }
            .dg-crumbs a:hover { text-decoration: underline; }
            .dg-page-head h1 { color: #fff; font: 800 clamp(28px, 4vw, 44px)/1.1 var(--dg-sans); text-transform: uppercase; }
            .dg-page-lede { margin-top: 12px; max-width: 64ch; color: #eaf3ff; font-size: 17px; }

            /* ---------- Outils et légende ---------- */
            .dg-org-tools { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px 24px; margin-bottom: 34px; }
            .dg-legend { display: flex; flex-wrap: wrap; gap: 8px 22px; font-size: 14px; color: var(--dg-muted); }
            .dg-legend li { display: inline-flex; align-items: center; gap: 8px; }
            .dg-legend i { width: 14px; height: 14px; border-radius: 4px; background: var(--c); }
            .k-appui { --c: var(--dg-amber); }
            .k-tech { --c: var(--dg-brand); }
            .k-reg { --c: var(--dg-green); }
            .dg-org-hint { font-size: 14px; color: var(--dg-muted); }

            /* ---------- Arbre ---------- */
            .dg-org { --line-c: rgba(10, 58, 138, .35); display: flex; flex-direction: column; align-items: center; }

            .dg-node { position: relative; display: flex; flex-direction: column; gap: 3px; padding: 12px 14px 13px; background: #fff; border: 1px solid var(--dg-line); border-top: 4px solid var(--c, var(--dg-brand)); border-radius: 10px; text-decoration: none; color: var(--dg-ink); transition: transform .25s var(--dg-ease), box-shadow .25s, border-color .25s; }
            .dg-node:hover, .dg-node:focus-visible { transform: translateY(-3px); border-color: var(--dg-sky); border-top-color: var(--c, var(--dg-brand)); box-shadow: 0 16px 34px -20px rgba(5, 20, 60, .65); }
            .dg-node-sig { font: 800 19px/1.1 var(--dg-sans); color: var(--dg-brand); }
            .dg-node-name { font-size: 13px; line-height: 1.35; color: var(--dg-muted); }

            .dg-node-root { align-items: center; width: min(100%, 360px); padding: 20px 22px; text-align: center; color: #fff; background: linear-gradient(135deg, #06307a, #0d62c9); border: 0; box-shadow: 0 18px 40px -20px rgba(5, 20, 60, .8); }
            .dg-node-root:hover, .dg-node-root:focus-visible { border: 0; }
            .dg-node-root .dg-node-sig { font-size: 32px; color: #fff; }
            .dg-node-root .dg-node-name { color: #dbe8fb; font-size: 14px; }
            .dg-node-root .dg-node-sub { margin-top: 6px; padding: 3px 12px; border-radius: 999px; background: rgba(255, 255, 255, .16); color: #fff; font: 600 12.5px/1.4 var(--dg-sans); }
            .dg-node-root::after { content: ""; position: absolute; inset: -8px; border: 1px solid rgba(30, 136, 229, .5); border-radius: 16px; animation: dgHalo 3s ease-out infinite; pointer-events: none; }
            @keyframes dgHalo { 0% { transform: scale(.96); opacity: .8; } 100% { transform: scale(1.06); opacity: 0; } }

            .dg-org-stem { width: 2px; height: 34px; background: var(--line-c); }

            /* Colonnes et connecteurs (écran large) */
            .dg-org-groups { position: relative; display: grid; grid-template-columns: 200px minmax(0, 1fr) 180px; gap: 28px; width: 100%; padding-top: 34px; }
            .dg-org-groups::before { content: ""; position: absolute; top: 0; left: 100px; right: 90px; height: 2px; background: linear-gradient(90deg, var(--line-c) 0 35%, var(--dg-sky) 50%, var(--line-c) 65% 100%) 0 0 / 250% 100%; animation: dgLine 5s linear infinite; }
            @keyframes dgLine { to { background-position: -250% 0; } }
            .dg-org-group { position: relative; }
            .dg-org-group::before { content: ""; position: absolute; top: -34px; left: 50%; width: 2px; height: 34px; margin-left: -1px; background: var(--line-c); }
            .dg-org-group h2 { margin-bottom: 14px; text-align: center; font: 700 14.5px/1.3 var(--dg-sans); color: var(--dg-brand); }
            .dg-org-col { display: flex; flex-direction: column; gap: 12px; }

            .dg-org-dirs { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; }
            .dg-org-dir { display: flex; flex-direction: column; }
            .dg-org-dir .dg-node { padding: 11px 10px 12px; }
            .dg-org-dir .dg-node-sig { font-size: 17px; }
            .dg-org-dir .dg-node-name { font-size: 12px; }

            .dg-org-svcs { display: flex; flex-direction: column; gap: 6px; margin: 8px 0 0 12px; padding-left: 12px; border-left: 2px solid var(--line-c); }
            .dg-org-svcs li { position: relative; }
            .dg-org-svcs li::before { content: ""; position: absolute; left: -12px; top: 50%; width: 10px; height: 2px; background: var(--line-c); }
            .dg-org-svcs a { display: block; padding: 6px 10px; font: 700 13px/1.2 var(--dg-sans); color: var(--dg-brand); background: #fff; border: 1px solid var(--dg-line); border-radius: 6px; text-decoration: none; transition: background .2s, color .2s, border-color .2s; }
            .dg-org-svcs a:hover, .dg-org-svcs a:focus-visible { background: var(--dg-brand); border-color: var(--dg-brand); color: #fff; }

            .dg-org-pdf { margin-top: 34px; text-align: center; }

            /* ---------- Attributions ---------- */
            .dg-units-wrap { max-width: 920px; }
            .dg-head { display: flex; justify-content: space-between; align-items: flex-end; gap: 20px; flex-wrap: wrap; margin-bottom: 30px; }
            .dg-head h2 { font-size: clamp(27px, 3.4vw, 36px); font-weight: 700; margin-bottom: 8px; }
            .dg-head p { max-width: 60ch; color: var(--dg-muted); }

            .dg-units { display: flex; flex-direction: column; gap: 10px; }

            .dg-unit { background: #fff; border: 1px solid var(--dg-line); border-left: 5px solid var(--c); border-radius: 10px; scroll-margin-top: 90px; transition: box-shadow .3s, border-color .3s; }
            .dg-unit.is-sub { margin-left: 28px; }
            .dg-unit[open] { box-shadow: 0 16px 36px -26px rgba(5, 20, 60, .7); }
            .dg-unit.is-flash { animation: dgFlash 1.6s ease-out; }
            @keyframes dgFlash { 0% { box-shadow: 0 0 0 0 rgba(30, 136, 229, .7); } 100% { box-shadow: 0 0 0 16px rgba(30, 136, 229, 0); } }

            .dg-unit summary { display: flex; align-items: center; gap: 16px; padding: 14px 18px; cursor: pointer; list-style: none; }
            .dg-unit summary::-webkit-details-marker { display: none; }
            .dg-unit-sig { display: grid; place-items: center; flex-shrink: 0; min-width: 62px; padding: 7px 10px; border-radius: 8px; font: 800 15px/1 var(--dg-sans); color: #fff; background: var(--c); }
            .k-appui .dg-unit-sig { color: #1b1500; }
            .dg-unit-name { flex: 1; font: 700 17px/1.35 var(--dg-serif); color: var(--dg-navy); }
            .dg-unit-name small { display: block; margin-top: 2px; font: 400 13px/1.4 var(--dg-sans); color: var(--dg-muted); }
            .dg-unit-chev { width: 14px; height: 14px; flex-shrink: 0; color: var(--dg-brand); transition: transform .25s var(--dg-ease); }
            .dg-unit[open] .dg-unit-chev { transform: rotate(180deg); }

            .dg-unit-body { padding: 4px 22px 22px 96px; }
            .dg-unit-mission { margin-bottom: 14px; font-size: 16.5px; line-height: 1.7; }
            .dg-unit-body h3 { margin: 6px 0 8px; font: 700 14px/1.3 var(--dg-sans); color: var(--dg-brand); }
            .dg-unit-body ul { display: flex; flex-direction: column; gap: 7px; margin-bottom: 14px; }
            .dg-unit-body li { position: relative; padding-left: 20px; font-size: 15.5px; line-height: 1.6; }
            .dg-unit-body li::before { content: ""; position: absolute; left: 2px; top: .68em; width: 8px; height: 8px; border-radius: 2px; background: var(--c); transform: rotate(45deg); }
            .dg-unit-sub, .dg-unit-lead { font-size: 15px; color: var(--dg-muted); }
            .dg-unit-sub { margin-bottom: 8px; }
            .dg-unit-sub a { font-weight: 700; color: var(--dg-accent); }

            /* ---------- Responsive ---------- */
            @media (max-width: 1100px) {
                .dg-org-groups { grid-template-columns: 1fr; gap: 30px; padding-top: 0; }
                .dg-org-groups::before, .dg-org-group::before, .dg-org-stem { display: none; }
                .dg-node-root { margin-bottom: 26px; }
                .dg-org-group h2 { text-align: left; padding-bottom: 8px; border-bottom: 2px solid var(--c); }
                .g-appui { --c: var(--dg-amber); }
                .g-tech { --c: var(--dg-brand); }
                .g-reg { --c: var(--dg-green); }
                .dg-org-col { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); }
                .dg-org-dirs { grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 18px; }
            }

            @media (max-width: 700px) {
                .dg-section { padding: 44px 0 64px; }
                .dg-unit.is-sub { margin-left: 14px; }
                .dg-unit summary { gap: 12px; padding: 12px 14px; }
                .dg-unit-sig { min-width: 54px; font-size: 14px; }
                .dg-unit-name { font-size: 16px; }
                .dg-unit-body { padding: 4px 16px 18px 16px; }
            }

            @media print {
                .dg-unit { break-inside: avoid; }
                .dg-org-tools, .dg-org-pdf, #dgToggleAll { display: none; }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function() {
                var units = document.querySelectorAll('.dg-unit');
                var toggle = document.getElementById('dgToggleAll');

                /* Un clic sur une structure de l'arbre ouvre ses attributions et les met en évidence */
                function openFromHash() {
                    var id = decodeURIComponent(window.location.hash.slice(1));
                    if (!id || id.indexOf('u-') !== 0) return;
                    var el = document.getElementById(id);
                    if (!el || el.tagName !== 'DETAILS') return;
                    el.open = true;
                    el.classList.remove('is-flash');
                    void el.offsetWidth;
                    el.classList.add('is-flash');
                }
                window.addEventListener('hashchange', openFromHash);
                openFromHash();

                /* Les liens internes (arbre, services rattachés) fonctionnent aussi quand le hash est déjà actif */
                document.addEventListener('click', function(e) {
                    var a = e.target.closest && e.target.closest('a[href^="#u-"]');
                    if (!a) return;
                    var el = document.getElementById(a.getAttribute('href').slice(1));
                    if (!el) return;
                    e.preventDefault();
                    history.replaceState(null, '', a.getAttribute('href'));
                    el.open = true;
                    el.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'start' });
                    el.classList.remove('is-flash');
                    void el.offsetWidth;
                    el.classList.add('is-flash');
                });

                /* Tout déplier / tout replier */
                if (toggle) {
                    toggle.addEventListener('click', function() {
                        var open = toggle.getAttribute('aria-pressed') !== 'true';
                        units.forEach(function(u) { u.open = open; });
                        toggle.setAttribute('aria-pressed', open ? 'true' : 'false');
                        toggle.textContent = open ? 'Tout replier' : 'Tout déplier';
                    });
                }
            })();
        </script>
    @endpush

@endsection