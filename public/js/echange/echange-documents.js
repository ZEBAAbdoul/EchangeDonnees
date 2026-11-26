/*!
 * echange-documents.js : SED, validation interétatique des DOCUMENTS
 *
 * À charger APRÈS echange.js et AVANT app.js. Il remplace Echange.documents.deposer / lister / ouvrir
 * et ajoute decider, liberer, libererEnAttente et annuler : un document destiné à un autre État reste
 * illisible pour cet État tant que TOUS les administrateurs actifs de l'État émetteur ne l'ont pas approuvé.
 *
 * Les clés privées ne quittent jamais le navigateur : seules des clés publiques, des données chiffrées
 * et des signatures sont envoyées au serveur.
 */
(function (global) {
    'use strict';

    const E = global.Echange;
    const APP = global.APP;
    const sodium = global.sodium;

    if (!E || !APP || !sodium || !E.documents || !E.donnees || !E.donnees.analyserEnvoi) {
        const msg = "echange-documents.js : echange.js (avec la validation des échanges de données) doit être chargé avant ce fichier.";
        console.error(msg);
        if (global.sedErreur) global.sedErreur(msg);
        return;
    }

    const ready = sodium.ready;

    /* ------------------------------------------------------------------ */
    /*  Utilitaires (mêmes conventions que echange.js)                     */
    /* ------------------------------------------------------------------ */
    const b64 = (u8) => sodium.to_base64(u8, sodium.base64_variants.ORIGINAL);
    const deB64 = (s) => sodium.from_base64(s, sodium.base64_variants.ORIGINAL);
    const enOctets = (s) => sodium.from_string(s);
    const enTexte = (u8) => sodium.to_string(u8);

    async function requete(methode, chemin, corps) {
        const rep = await fetch(APP.api + chemin, {
            method: methode,
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': APP.csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: corps ? JSON.stringify(corps) : undefined
        });
        let json = null;
        try { json = await rep.json(); } catch (e) { /* réponse vide */ }
        if (!rep.ok) {
            let detail = json && json.message;
            if (json && json.errors) {
                const premiere = Object.values(json.errors)[0];
                if (premiere && premiere[0]) detail = premiere[0];
            }
            const err = new Error(detail || 'Erreur serveur (' + rep.status + ').');
            err.status = rep.status;
            throw err;
        }
        return json;
    }

    function signatureValide(signatureB64, octets, cleSignatureB64) {
        try {
            return sodium.crypto_sign_verify_detached(deB64(signatureB64), octets, deB64(cleSignatureB64));
        } catch (e) {
            return false;
        }
    }

    async function lireOctets(blob) {
        if (blob.arrayBuffer) return new Uint8Array(await blob.arrayBuffer());
        return new Promise(function (ok, ko) {
            const r = new FileReader();
            r.onload = function () { ok(new Uint8Array(r.result)); };
            r.onerror = function () { ko(new Error('Lecture du fichier impossible.')); };
            r.readAsArrayBuffer(blob);
        });
    }

    // Une clé maître par document, scellée pour chaque destinataire : sous-clé du fichier et sous-clé des métadonnées
    function sousCles(maitre) {
        return {
            fichier: sodium.crypto_kdf_derive_from_key(32, 1, 'SEDFILE1', maitre),
            meta: sodium.crypto_kdf_derive_from_key(32, 2, 'SEDMETA1', maitre)
        };
    }

    // Ce que l'expéditeur signe : tout ce qui décrit le document chiffré (inchangé : les documents déjà envoyés restent valides)
    const aSignerDoc = (niveau, header, metaNonce, meta, hash) =>
        enOctets(['doc', niveau, header, metaNonce, meta, hash].join('|'));

    // Texte signé par un administrateur : lié à CE document, à la signature de l'expéditeur et à son choix.
    // Le préfixe « validdoc » l'empêche d'être rejoué sur un échange de données.
    const aSignerValidationDoc = (id, decision, signatureExpediteur, motifNonce, motifChiffre) =>
        enOctets(['validdoc', id, decision, signatureExpediteur, motifNonce || '', motifChiffre || ''].join('|'));

    /** Clés publiques d'une liste d'agents (clé présente, aucune clé modifiée depuis l'épinglage). */
    function clesDe(ids, annuaire, alertes, commeValideur) {
        return ids.map(function (id) {
            const a = annuaire.get(id);
            if (!a || !a.cle_chiffrement) {
                throw new Error(((a && a.nom) || 'Un agent') + " n'a pas encore de clé de chiffrement" +
                    (commeValideur ? " : il ne pourra pas valider cet envoi." : "."));
            }
            if (alertes.indexOf(id) !== -1) throw new Error('La clé de ' + a.nom + " a changé. Vérifiez son empreinte avant d'envoyer.");
            return { agent_id: id, pk: deB64(a.cle_chiffrement) };
        });
    }

    /** Tous les administrateurs exigés par l'expéditeur (liste signée) ont-ils approuvé, avec une signature valide ? */
    function verifierValidations(requis, validations, annuaire) {
        const manquants = [];
        const invalides = [];
        (requis || []).forEach(function (id) {
            const v = validations.find((x) => x.agent.id === id);
            const nom = (v && v.agent.nom) || (annuaire.get(id) && annuaire.get(id).nom) || id;
            if (!v || v.decision !== 'approuve') manquants.push(nom);
            else if (v.signatureOk !== true) invalides.push(nom);
        });
        return { complet: !manquants.length && !invalides.length, manquants: manquants, invalides: invalides, exige: (requis || []).length };
    }

    /* ------------------------------------------------------------------ */
    /*  Dépôt                                                              */
    /* ------------------------------------------------------------------ */
    /**
     * Chiffre un fichier et le dépose pour les agents choisis (l'expéditeur y est ajouté).
     * Vers un autre État : les destinataires étrangers n'ont aucune clé tant que les administrateurs n'ont pas tous approuvé.
     */
    async function deposer(fichier, niveau, idsDestinataires) {
        await ready;
        const k = E.trousseau.cles();

        if (fichier.size > E.documents.tailleMax) {
            throw new Error('Fichier trop volumineux (maximum ' + Math.round(E.documents.tailleMax / 1048576) + ' Mo).');
        }

        // Qui reçoit tout de suite, qui attend la validation des administrateurs, qui doit valider
        const plan = await E.donnees.analyserEnvoi(idsDestinataires, true);
        if (plan.erreur) throw new Error(plan.erreur);
        const annuaire = await E.confiance.charger(false);
        const alertes = E.confiance.changements();

        const idsAvecCle = Array.from(new Set([APP.agent.id].concat(
            plan.internes.map((a) => a.id),
            plan.libererDirect ? plan.externes.map((a) => a.id) : []
        )));
        const dest = clesDe(idsAvecCle, annuaire, alertes, false);
        clesDe(plan.externes.map((a) => a.id), annuaire, alertes, false);   // leur clé doit déjà exister pour la libération
        const valideurs = clesDe(plan.valideurs.map((a) => a.id), annuaire, alertes, true);

        const octets = await lireOctets(fichier);

        // 1) Clé maître et sous-clés
        const maitre = sodium.randombytes_buf(32);
        const sk = sousCles(maitre);

        // 2) Fichier chiffré par blocs
        const flux = E.documents._flux.chiffrer(octets, sk.fichier);

        // 3) Métadonnées chiffrées (le serveur ne voit pas le nom). La liste des administrateurs exigés y est écrite :
        //    elle est couverte par la signature, donc le serveur ne peut pas la raccourcir après coup.
        const metaNonce = sodium.randombytes_buf(sodium.crypto_secretbox_NONCEBYTES);
        const meta = sodium.crypto_secretbox_easy(enOctets(JSON.stringify({
            nom: fichier.name || 'document',
            type: fichier.type || '',
            taille: octets.length,
            valideurs: plan.valideurs.length ? plan.valideurs.map((a) => a.id) : undefined,
            date: new Date().toISOString()
        })), metaNonce, sk.meta);

        // 4) Empreinte du fichier chiffré et signature
        const hash = b64(sodium.crypto_generichash(32, flux.chiffre));
        const headerB64 = b64(flux.header);
        const metaNonceB64 = b64(metaNonce);
        const metaB64 = b64(meta);
        const signature = b64(sodium.crypto_sign_detached(aSignerDoc(niveau, headerB64, metaNonceB64, metaB64, hash), k.signSk));

        // 5) Enveloppes de la clé maître : moi, mes collègues, (libération directe) les autres États, et les valideurs
        const sceller = (liste) => liste.map((d) => ({ agent_id: d.agent_id, cle: b64(sodium.crypto_box_seal(maitre, d.pk)) }));
        const enveloppes = sceller(dest).concat(
            plan.libererDirect ? [] : plan.externes.map((a) => ({ agent_id: a.id, cle: null }))
        );
        const enveloppesValideurs = sceller(valideurs);

        sodium.memzero(maitre);
        sodium.memzero(sk.fichier);
        sodium.memzero(sk.meta);

        const form = new FormData();
        form.append('fichier', new Blob([flux.chiffre], { type: 'application/octet-stream' }), 'document.bin');
        form.append('niveau', String(niveau));
        form.append('header', headerB64);
        form.append('meta_nonce', metaNonceB64);
        form.append('meta_chiffree', metaB64);
        form.append('hash', hash);
        form.append('signature', signature);
        form.append('destinataires', JSON.stringify(enveloppes));
        form.append('valideurs', JSON.stringify(enveloppesValideurs));

        return E.api.deposerDocument(form);
    }

    /* ------------------------------------------------------------------ */
    /*  Liste                                                              */
    /* ------------------------------------------------------------------ */
    /**
     * Liste les documents de l'agent (envoyés, reçus, à valider), avec nom et taille déchiffrés.
     * Chaque élément : { id, expediteur, niveau, nom, type, taille, date, signatureValide, erreur, brut,
     *                    sens, statut, validation, expire_le, ma_validation, validations, destinataires,
     *                    valideursExiges, valideursIndisponibles, verification }
     * IMPORTANT : `nom` et les motifs sont du texte brut : les afficher avec textContent, jamais innerHTML.
     */
    async function lister() {
        await ready;
        const k = E.trousseau.cles();
        const annuaire = await E.confiance.charger(false);
        const liste = await E.api.documents();

        return liste.map(function (d) {
            const sortie = {
                id: d.id, expediteur: d.expediteur, niveau: d.niveau,
                nom: null, type: null, taille: null, date: d.created_at,
                signatureValide: null, erreur: null, brut: d,
                sens: d.sens, statut: d.statut,
                validation: d.validation || 'non_requise', expire_le: d.expire_le || null, ma_validation: d.ma_validation || null,
                validations: (d.validations || []).map((v) => ({ agent: v.agent, decision: v.decision, decide_le: v.decide_le, signatureOk: null, motif: null })),
                destinataires: d.destinataires || [],
                valideursExiges: [], valideursIndisponibles: [], verification: null
            };
            try {
                const exp = annuaire.get(d.expediteur.id);
                if (exp && exp.cle_signature) {
                    sortie.signatureValide = signatureValide(
                        d.signature,
                        aSignerDoc(d.niveau, d.header, d.meta_nonce, d.meta_chiffree, d.hash),
                        exp.cle_signature
                    );
                }
                if (!d.ma_cle) throw new Error('enveloppe absente');
                const maitre = sodium.crypto_box_seal_open(deB64(d.ma_cle), k.boxPk, k.boxSk);
                const sk = sousCles(maitre);
                const meta = JSON.parse(enTexte(sodium.crypto_secretbox_open_easy(deB64(d.meta_chiffree), deB64(d.meta_nonce), sk.meta)));
                sortie.nom = String(meta.nom);
                sortie.type = String(meta.type || '');
                sortie.taille = meta.taille;
                sortie.date = meta.date || d.created_at;

                // Décisions des administrateurs : signature vérifiée, motif de refus déchiffré avec la sous-clé des métadonnées
                (d.validations || []).forEach(function (v, i) {
                    const cible = sortie.validations[i];
                    const va = annuaire.get(v.agent.id);
                    if (v.decision !== 'en_attente') {
                        cible.signatureOk = !!(va && va.cle_signature && v.signature) && signatureValide(
                            v.signature, aSignerValidationDoc(d.id, v.decision, d.signature, v.motif_nonce, v.motif_chiffre), va.cle_signature);
                    }
                    if (v.decision === 'refuse' && v.motif_chiffre && v.motif_nonce) {
                        try { cible.motif = enTexte(sodium.crypto_secretbox_open_easy(deB64(v.motif_chiffre), deB64(v.motif_nonce), sk.meta)); }
                        catch (e) { cible.motif = null; }
                    }
                });
                sortie.valideursExiges = Array.isArray(meta.valideurs) ? meta.valideurs : [];
                sortie.valideursIndisponibles = sortie.valideursExiges.filter((id) => !annuaire.get(id));
                if (d.validation === 'validee' || d.validation === 'liberee') {
                    sortie.verification = verifierValidations(sortie.valideursExiges, sortie.validations, annuaire);
                }

                sodium.memzero(maitre);
                sodium.memzero(sk.fichier);
                sodium.memzero(sk.meta);
            } catch (e) {
                sortie.erreur = 'Document illisible (clé différente ou métadonnées altérées).';
            }
            return sortie;
        });
    }

    /* ------------------------------------------------------------------ */
    /*  Décision, libération, annulation                                   */
    /* ------------------------------------------------------------------ */
    /** Un administrateur approuve ou refuse l'envoi d'un document vers un autre État. La décision est SIGNÉE. */
    async function decider(d, decision, motifTexte) {
        await ready;
        const k = E.trousseau.cles();

        if (decision !== 'approuve' && decision !== 'refuse') throw new Error('Décision inconnue.');
        if (d.ma_validation !== 'en_attente' || d.validation !== 'en_attente') throw new Error('Vous ne pouvez plus décider pour ce document.');
        if (d.erreur) throw new Error(d.erreur);
        if (d.signatureValide !== true) throw new Error("La signature de l'expéditeur n'a pas pu être vérifiée : validation impossible.");

        let motifNonce = null;
        let motifChiffre = null;
        if (decision === 'refuse') {
            const motif = String(motifTexte || '').trim();
            if (!motif) throw new Error('Indiquez le motif du refus.');
            if (motif.length > 1000) throw new Error('Motif trop long (1000 caractères maximum).');
            // Le motif est chiffré avec la sous-clé des métadonnées : lisible par l'expéditeur et les administrateurs, pas par le serveur
            const maitre = sodium.crypto_box_seal_open(deB64(d.brut.ma_cle), k.boxPk, k.boxSk);
            const sk = sousCles(maitre);
            const nonce = sodium.randombytes_buf(sodium.crypto_secretbox_NONCEBYTES);
            motifChiffre = b64(sodium.crypto_secretbox_easy(enOctets(motif), nonce, sk.meta));
            motifNonce = b64(nonce);
            sodium.memzero(maitre);
            sodium.memzero(sk.fichier);
            sodium.memzero(sk.meta);
        }

        const signature = b64(sodium.crypto_sign_detached(
            aSignerValidationDoc(d.id, decision, d.brut.signature, motifNonce, motifChiffre), k.signSk));

        const rep = await requete('POST', '/documents/' + d.id + '/validation',
            { decision: decision, signature: signature, motif_chiffre: motifChiffre, motif_nonce: motifNonce });

        const resultat = { validation: rep.validation, libere: false, erreurLiberation: null };
        if (rep.a_liberer) {
            try {
                const frais = (await lister()).find((x) => x.id === d.id);
                if (!frais) throw new Error('Document introuvable.');
                await liberer(frais);
                resultat.libere = true;
            } catch (e) {
                resultat.erreurLiberation = e.message;   // le document reste « validé » : relance possible
            }
        }
        return resultat;
    }

    /** Remet aux destinataires des autres États la clé du document, une fois TOUS les administrateurs d'accord (contrôle refait ici). */
    async function liberer(d) {
        await ready;
        const k = E.trousseau.cles();

        if (d.validation !== 'validee') throw new Error("Ce document n'attend pas de libération.");
        if (d.erreur) throw new Error(d.erreur);
        if (d.signatureValide !== true) throw new Error("La signature de l'expéditeur n'a pas pu être vérifiée : libération impossible.");
        if (!d.verification || !d.verification.complet) {
            throw new Error("Toutes les approbations des administrateurs ne sont pas présentes et valides : libération refusée.");
        }

        const annuaire = await E.confiance.charger(true);
        const alertes = E.confiance.changements();
        const enAttente = (d.destinataires || []).filter((x) => x.statut === 'attente');
        if (!enAttente.length) throw new Error('Aucun destinataire en attente de clé.');

        const pks = clesDe(enAttente.map((x) => x.agent.id), annuaire, alertes, false);
        const maitre = sodium.crypto_box_seal_open(deB64(d.brut.ma_cle), k.boxPk, k.boxSk);
        const enveloppes = pks.map((x) => ({ agent_id: x.agent_id, cle: b64(sodium.crypto_box_seal(maitre, x.pk)) }));
        sodium.memzero(maitre);

        return requete('POST', '/documents/' + d.id + '/liberer', { enveloppes: enveloppes });
    }

    /** Reprend les libérations interrompues (navigateur fermé trop tôt). `dejaEssayes` évite de réessayer en boucle. */
    async function libererEnAttente(liste, dejaEssayes) {
        let n = 0;
        for (const d of liste) {
            if (d.validation !== 'validee') continue;
            if (!(d.sens === 'envoye' || d.ma_validation === 'approuve')) continue;
            if (dejaEssayes) {
                if (dejaEssayes.has(d.id)) continue;
                dejaEssayes.add(d.id);
            }
            try { await liberer(d); n++; } catch (e) { /* silencieux : relance manuelle possible */ }
        }
        return n;
    }

    /** L'expéditeur retire un document non transmis (refusé, bloqué, ou envoyé par erreur). */
    async function annuler(d) {
        await ready;
        if (d.sens !== 'envoye') throw new Error("Seul l'expéditeur peut annuler ce document.");
        return requete('POST', '/documents/' + d.id + '/annuler', {});
    }

    /* ------------------------------------------------------------------ */
    /*  Branchement sur Echange.documents                                  */
    /* ------------------------------------------------------------------ */
    const ouvrirOrigine = E.documents.ouvrir;

    E.documents.deposer = deposer;
    E.documents.lister = lister;
    E.documents.decider = decider;
    E.documents.liberer = liberer;
    E.documents.libererEnAttente = libererEnAttente;
    E.documents.annuler = annuler;
    // Ne jamais déchiffrer un document dont les approbations de l'État émetteur ne sont pas toutes valides
    E.documents.ouvrir = async function (doc) {
        if (doc.verification && !doc.verification.complet) {
            throw new Error("Les approbations de l'État émetteur sont incomplètes ou invalides : téléchargement refusé.");
        }
        return ouvrirOrigine(doc);
    };
})(window);