<?php

return [
    // Identifiant (table noeuds) du nœud qui fait tourner CETTE installation.
    'noeud_id' => (int) env('ECHANGE_NOEUD_ID', 0),

    // Clés Ed25519 du nœud (base64) pour signer le journal d'audit.
    // À terme : à stocker dans un HSM, pas dans un fichier .env.
    'cle_signature_secrete'  => env('ECHANGE_NOEUD_CLE_SECRETE'),
    'cle_signature_publique' => env('ECHANGE_NOEUD_CLE_PUBLIQUE'),

    // Disque privé où sont stockés les fichiers DÉJÀ CHIFFRÉS.
    'disque_documents' => env('ECHANGE_DISQUE_DOCUMENTS', 'documents'),
    'taille_max_document_ko' => 51200, // 50 Mo
];
