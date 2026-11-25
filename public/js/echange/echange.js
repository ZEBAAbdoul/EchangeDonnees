/*!
 * echange.js : SED (Système d'Échange d'Informations)
 * Trousseau local, chiffrement de bout en bout (libsodium) et client API.
 *
 * Dépend de : window.APP (défini dans la vue) et window.sodium (js/echange/sodium.js).
 * Expose : window.Echange
 *
 * Les clés privées ne quittent JAMAIS le navigateur. Le serveur ne reçoit que
 * des clés publiques, des messages chiffrés et des signatures.
 */
(function (global) {
    "use strict";

    const APP = global.APP;
    if (!APP || !global.sodium) {
        const msg = !global.sodium
            ? "libsodium (sodium.js) non chargé : vérifiez que public/js/echange/sodium.js existe et est accessible."
            : "Configuration window.APP absente.";
        console.error("echange.js :", msg);
        if (global.sedErreur) global.sedErreur(msg);
        return;
    }
    const ready = global.sodium.ready;

    const AUTO_VERROUILLAGE_MS = 10 * 60 * 1000; // 10 minutes d'inactivité
    const LONGUEUR_MIN_PHRASE = 12;

    /* ------------------------------------------------------------------ */
    /*  Utilitaires                                                        */
    /* ------------------------------------------------------------------ */
    // Base64 standard avec padding : 32 octets = 44 caractères (voir validation Laravel)
    const b64 = (u8) => sodium.to_base64(u8, sodium.base64_variants.ORIGINAL);
    const deB64 = (s) => sodium.from_base64(s, sodium.base64_variants.ORIGINAL);
    const enOctets = (s) => sodium.from_string(s);
    const enTexte = (u8) => sodium.to_string(u8);

    function lireStockage(cle, defaut) {
        try {
            const v = localStorage.getItem(cle);
            return v === null ? defaut : JSON.parse(v);
        } catch (e) {
            return defaut;
        }
    }

    function ecrireStockage(cle, valeur) {
        try {
            localStorage.setItem(cle, JSON.stringify(valeur));
        } catch (e) {
            throw new Error(
                "Impossible d'enregistrer le trousseau sur cet appareil (stockage indisponible).",
            );
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Empreintes (pour vérifier une identité hors du serveur)            */
    /* ------------------------------------------------------------------ */
    function empreinte(cleChiffrement, cleSignature) {
        const a = deB64(cleChiffrement);
        const b = deB64(cleSignature);
        const m = new Uint8Array(a.length + b.length);
        m.set(a, 0);
        m.set(b, a.length);
        const hex = sodium
            .to_hex(sodium.crypto_generichash(16, m))
            .toUpperCase();
        return hex.match(/.{4}/g).join(" "); // ex. A1B2 C3D4 ...
    }

    /* ------------------------------------------------------------------ */
    /*  Trousseau (clés privées chiffrées par la phrase secrète)           */
    /* ------------------------------------------------------------------ */
    const Trousseau = (function () {
        let secrets = null; // { boxSk, signSk, boxPk, signPk } : uniquement en mémoire
        let minuteur = null;

        const cleStock = () => "sed.trousseau." + APP.agent.id;
        const lireBlob = () => lireStockage(cleStock(), null);

        function notifier() {
            const ouvert = secrets !== null;
            const el = document.getElementById("etat-trousseau");
            if (el) {
                el.classList.toggle("ouvert", ouvert);
                el.classList.toggle("ferme", !ouvert);
                el.innerHTML = ouvert
                    ? '<i class="fas fa-lock-open me-1"></i> Trousseau déverrouillé'
                    : '<i class="fas fa-lock me-1"></i> Trousseau verrouillé';
            }
            document.dispatchEvent(
                new CustomEvent("trousseau:etat", {
                    detail: { ouvert: ouvert },
                }),
            );
        }

        function armer() {
            clearTimeout(minuteur);
            if (secrets)
                minuteur = setTimeout(verrouiller, AUTO_VERROUILLAGE_MS);
        }

        function verrouiller() {
            if (secrets) {
                sodium.memzero(secrets.boxSk);
                sodium.memzero(secrets.signSk);
            }
            secrets = null;
            clearTimeout(minuteur);
            notifier();
        }

        function deriver(phrase, salt, kdf) {
            return sodium.crypto_pwhash(
                32,
                phrase,
                salt,
                kdf.ops,
                kdf.mem,
                sodium.crypto_pwhash_ALG_ARGON2ID13,
            );
        }

        function verifierPhrase(phrase) {
            if (
                typeof phrase !== "string" ||
                phrase.length < LONGUEUR_MIN_PHRASE
            ) {
                throw new Error(
                    "La phrase secrète doit contenir au moins " +
                        LONGUEUR_MIN_PHRASE +
                        " caractères.",
                );
            }
        }

        const existe = () => lireBlob() !== null;
        const estOuvert = () => secrets !== null;

        /** Clés publiques : disponibles même trousseau verrouillé. */
        function publiques() {
            const blob = lireBlob();
            return blob
                ? {
                      cle_chiffrement: blob.pub.box,
                      cle_signature: blob.pub.sign,
                  }
                : null;
        }

        /** Crée un nouveau trousseau. Renvoie les clés publiques à envoyer au serveur. */
        async function creer(phrase) {
            await ready;
            if (existe())
                throw new Error("Un trousseau existe déjà sur cet appareil.");
            verifierPhrase(phrase);

            const box = sodium.crypto_box_keypair();
            const sign = sodium.crypto_sign_keypair();
            const salt = sodium.randombytes_buf(sodium.crypto_pwhash_SALTBYTES);
            // Argon2id : 3 passes, 64 Mo. Augmentez mem si vos postes le supportent.
            const kdf = {
                ops: sodium.crypto_pwhash_OPSLIMIT_MODERATE,
                mem: sodium.crypto_pwhash_MEMLIMIT_INTERACTIVE,
            };
            const cle = deriver(phrase, salt, kdf);
            const nonce = sodium.randombytes_buf(
                sodium.crypto_secretbox_NONCEBYTES,
            );
            const clair = enOctets(
                JSON.stringify({
                    box: b64(box.privateKey),
                    sign: b64(sign.privateKey),
                }),
            );
            const secret = sodium.crypto_secretbox_easy(clair, nonce, cle);
            sodium.memzero(cle);
            sodium.memzero(clair);

            ecrireStockage(cleStock(), {
                v: 1,
                kdf: kdf,
                salt: b64(salt),
                nonce: b64(nonce),
                secret: b64(secret),
                pub: { box: b64(box.publicKey), sign: b64(sign.publicKey) },
            });

            secrets = {
                boxSk: box.privateKey,
                signSk: sign.privateKey,
                boxPk: box.publicKey,
                signPk: sign.publicKey,
            };
            armer();
            notifier();
            return publiques();
        }

        async function deverrouiller(phrase) {
            await ready;
            const blob = lireBlob();
            if (!blob)
                throw new Error(
                    "Aucun trousseau sur cet appareil. Créez-en un ou importez une sauvegarde.",
                );

            const cle = deriver(phrase, deB64(blob.salt), blob.kdf);
            let clair;
            try {
                clair = sodium.crypto_secretbox_open_easy(
                    deB64(blob.secret),
                    deB64(blob.nonce),
                    cle,
                );
            } catch (e) {
                throw new Error("Phrase secrète incorrecte.");
            } finally {
                sodium.memzero(cle);
            }

            const k = JSON.parse(enTexte(clair));
            sodium.memzero(clair);
            secrets = {
                boxSk: deB64(k.box),
                signSk: deB64(k.sign),
                boxPk: deB64(blob.pub.box),
                signPk: deB64(blob.pub.sign),
            };
            armer();
            notifier();
        }

        /** Accès aux clés privées (interne). Renouvelle le minuteur d'inactivité. */
        function cles() {
            if (!secrets)
                throw new Error(
                    "Trousseau verrouillé : saisissez votre phrase secrète.",
                );
            armer();
            return secrets;
        }

        /** Sauvegarde : le fichier exporté reste chiffré par la phrase secrète. */
        function exporter() {
            const blob = lireBlob();
            if (!blob) throw new Error("Aucun trousseau à exporter.");
            return JSON.stringify(blob);
        }

        /** Supprime le trousseau de CET appareil (phrase perdue, clé révoquée). Irréversible sans sauvegarde. */
        function supprimer() {
            verrouiller();
            try {
                localStorage.removeItem(cleStock());
            } catch (e) {
                /* stockage indisponible */
            }
            notifier();
        }

        function importer(json, ecraser) {
            let blob;
            try {
                blob = JSON.parse(json);
            } catch (e) {
                throw new Error("Fichier de sauvegarde invalide.");
            }
            if (!blob || blob.v !== 1 || !blob.secret || !blob.pub)
                throw new Error("Fichier de sauvegarde invalide.");
            if (existe() && !ecraser)
                throw new Error("Un trousseau existe déjà sur cet appareil.");
            verrouiller();
            ecrireStockage(cleStock(), blob);
            notifier();
        }

        // Verrouillage automatique à la fermeture / mise en veille de l'onglet
        global.addEventListener("pagehide", verrouiller);

        function brancherInterface() {
            notifier();
            const btn = document.getElementById("btn-verrouiller-haut");
            if (btn) btn.addEventListener("click", verrouiller);
        }
        // Fonctionne que le script soit chargé avant ou après la fin du chargement de la page
        if (document.readyState === "loading")
            document.addEventListener("DOMContentLoaded", brancherInterface);
        else brancherInterface();

        return {
            existe,
            estOuvert,
            publiques,
            creer,
            deverrouiller,
            verrouiller,
            cles,
            exporter,
            importer,
            supprimer,
        };
    })();

    /* ------------------------------------------------------------------ */
    /*  Client API                                                         */
    /* ------------------------------------------------------------------ */
    /** Lit une réponse JSON et transforme les erreurs HTTP en Error lisibles. */
    async function traiterReponse(rep) {
        let json = null;
        try {
            json = await rep.json();
        } catch (e) {
            /* réponse vide */
        }

        if (!rep.ok) {
            let detail = json && json.message;
            if (json && json.errors) {
                const premiere = Object.values(json.errors)[0];
                if (premiere && premiere[0]) detail = premiere[0];
            }
            const err = new Error(
                detail || "Erreur serveur (" + rep.status + ").",
            );
            err.status = rep.status;
            throw err;
        }
        return json;
    }

    const ENTETES = {
        Accept: "application/json",
        "X-CSRF-TOKEN": APP.csrf,
        "X-Requested-With": "XMLHttpRequest",
    };

    async function requete(methode, chemin, corps) {
        const rep = await fetch(APP.api + chemin, {
            method: methode,
            credentials: "same-origin",
            headers: Object.assign(
                { "Content-Type": "application/json" },
                ENTETES,
            ),
            body: corps ? JSON.stringify(corps) : undefined,
        });
        return traiterReponse(rep);
    }

    /** Envoi multipart (fichier chiffré) : pas de Content-Type, le navigateur ajoute la frontière. */
    async function requeteForm(chemin, formData) {
        const rep = await fetch(APP.api + chemin, {
            method: "POST",
            credentials: "same-origin",
            headers: ENTETES,
            body: formData,
        });
        return traiterReponse(rep);
    }

    /** Téléchargement binaire (fichier chiffré). */
    async function requeteBinaire(chemin) {
        const rep = await fetch(APP.api + chemin, {
            credentials: "same-origin",
            headers: ENTETES,
        });
        if (!rep.ok) await traiterReponse(rep); // lève l'erreur avec le message du serveur
        return new Uint8Array(await rep.arrayBuffer());
    }

    const api = {
        moi: () => requete("GET", "/moi"),
        annuaire: () => requete("GET", "/agents"),
        enregistrerCle: (pub) => requete("POST", "/cles", pub),
        conversations: () => requete("GET", "/conversations"),
        creerConversation: (membres, titre) =>
            requete("POST", "/conversations", {
                membres: membres,
                titre: titre || null,
            }),
        messages: (convId, page) =>
            requete(
                "GET",
                "/conversations/" + convId + "/messages?page=" + (page || 1),
            ),
        envoyerMessage: (convId, charge) =>
            requete("POST", "/conversations/" + convId + "/messages", charge),
        documents: () => requete("GET", "/documents"),
        deposerDocument: (formData) => requeteForm("/documents", formData),
        fichierDocument: (id) =>
            requeteBinaire("/documents/" + id + "/fichier"),
        donnees: () => requete("GET", "/donnees"),
        envoyerDonnees: (charge) => requete("POST", "/donnees", charge),
        envoyerDonneesForm: (formData) => requeteForm("/donnees", formData),
        photoDonnees: (id) => requeteBinaire("/donnees/" + id + "/photo"),
        statutDonnees: (id, statut) =>
            requete("POST", "/donnees/" + id + "/statut", { statut: statut }),

        validerDonnees: (id, charge) =>
            requete("POST", "/donnees/" + id + "/validation", charge),
        libererDonnees: (id, charge) =>
            requete("POST", "/donnees/" + id + "/liberer", charge),
        annulerDonnees: (id) =>
            requete("POST", "/donnees/" + id + "/annuler", {}),

        // Administration (réservée aux administrateurs : le serveur refuse les autres)
        adminAgents: () => requete("GET", "/admin/agents"),
        adminUtilisateurs: () => requete("GET", "/admin/utilisateurs"),
        adminCreerAgent: (donnees) => requete("POST", "/admin/agents", donnees),
        adminModifierAgent: (id, donnees) =>
            requete("PATCH", "/admin/agents/" + id, donnees),
        adminRevoquerCle: (id) =>
            requete("POST", "/admin/agents/" + id + "/revoquer-cle"),
        adminOrganismes: () => requete("GET", "/admin/organismes"),
        adminCreerOrganisme: (donnees) =>
            requete("POST", "/admin/organismes", donnees),
        adminModifierOrganisme: (id, donnees) =>
            requete("PATCH", "/admin/organismes/" + id, donnees),
        adminSupprimerOrganisme: (id) =>
            requete("DELETE", "/admin/organismes/" + id),
        adminAudit: (action) =>
            requete(
                "GET",
                "/admin/audit" +
                    (action ? "?action=" + encodeURIComponent(action) : ""),
            ),
    };

    /* ------------------------------------------------------------------ */
    /*  Annuaire et épinglage des clés (protège contre un serveur piégé)   */
    /* ------------------------------------------------------------------ */
    // Le serveur central distribue les clés publiques : un serveur compromis pourrait
    // en substituer une. On épingle donc l'empreinte de chaque agent à la première vue
    // (TOFU) et on bloque l'envoi si elle change sans validation explicite.
    const Confiance = (function () {
        let cache = new Map(); // agentId -> agent (avec clés)
        let changes = new Set(); // agentId dont la clé a changé depuis l'épinglage

        const cleEpingles = () => "sed.epingles." + APP.agent.id;

        async function charger(force) {
            await ready;
            if (cache.size && !force) return cache;

            const liste = await api.annuaire();
            const epingles = lireStockage(cleEpingles(), {});
            const miennes = Trousseau.publiques();
            const neuf = new Map();
            changes = new Set();

            liste.forEach(function (a) {
                if (!a.cle_chiffrement || !a.cle_signature) {
                    neuf.set(a.id, a);
                    return;
                }
                a.empreinte = empreinte(a.cle_chiffrement, a.cle_signature);
                neuf.set(a.id, a);

                // Ma propre clé doit correspondre à celle de mon trousseau local
                if (
                    a.id === APP.agent.id &&
                    miennes &&
                    (a.cle_chiffrement !== miennes.cle_chiffrement ||
                        a.cle_signature !== miennes.cle_signature)
                ) {
                    changes.add(a.id);
                    return;
                }
                if (!epingles[a.id])
                    epingles[a.id] = a.empreinte; // première vue
                else if (epingles[a.id] !== a.empreinte) changes.add(a.id); // clé modifiée
            });

            ecrireStockage(cleEpingles(), epingles);
            cache = neuf;
            return cache;
        }

        /** À appeler après comparaison de l'empreinte avec l'agent (téléphone, en personne). */
        function accepterChangement(agentId) {
            const a = cache.get(agentId);
            if (!a || !a.empreinte) return;
            const epingles = lireStockage(cleEpingles(), {});
            epingles[agentId] = a.empreinte;
            ecrireStockage(cleEpingles(), epingles);
            changes.delete(agentId);
        }

        return {
            charger,
            accepterChangement,
            agent: (id) => cache.get(id),
            changements: () => Array.from(changes),
        };
    })();

    /* ------------------------------------------------------------------ */
    /*  Messages chiffrés de bout en bout                                  */
    /* ------------------------------------------------------------------ */
    /** Vérifie une signature Ed25519. Une signature mal formée ou falsifiée donne simplement « false ». */
    function signatureValide(signatureB64, octets, cleSignatureB64) {
        try {
            return sodium.crypto_sign_verify_detached(
                deB64(signatureB64),
                octets,
                deB64(cleSignatureB64),
            );
        } catch (e) {
            return false;
        }
    }

    // Le texte signé lie le message à sa conversation et à son niveau :
    // un message ne peut pas être rejoué dans une autre conversation.
    const aSigner = (convId, niveau, nonce, contenu) =>
        enOctets(convId + "|" + niveau + "|" + nonce + "|" + contenu);

    /**
     * @param {{id:string, membres:{id:string, nom?:string}[]}} conversation
     * @param {number} niveau  1 à 4
     * @param {string} texteClair
     */
    async function envoyer(conversation, niveau, texteClair) {
        await ready;
        const k = Trousseau.cles();
        const annuaire = await Confiance.charger(false);
        const alertes = Confiance.changements();

        const cles = conversation.membres.map(function (m) {
            const a = annuaire.get(m.id);
            if (!a || !a.cle_chiffrement) {
                throw new Error(
                    (m.nom || "Un membre") +
                        " n'a pas encore de clé de chiffrement.",
                );
            }
            if (alertes.indexOf(m.id) !== -1) {
                throw new Error(
                    "La clé de " +
                        a.nom +
                        " a changé. Vérifiez son empreinte avant d'envoyer.",
                );
            }
            return { agent_id: m.id, pk: deB64(a.cle_chiffrement) };
        });

        // 1) Chiffrement du contenu avec une clé aléatoire à usage unique
        const cleMessage = sodium.crypto_secretbox_keygen();
        const nonce = sodium.randombytes_buf(
            sodium.crypto_secretbox_NONCEBYTES,
        );
        const chiffre = sodium.crypto_secretbox_easy(
            enOctets(
                JSON.stringify({ t: texteClair, d: new Date().toISOString() }),
            ),
            nonce,
            cleMessage,
        );

        // 2) Enveloppe de la clé pour chaque membre (y compris moi-même)
        const enveloppes = cles.map((c) => ({
            agent_id: c.agent_id,
            cle: b64(sodium.crypto_box_seal(cleMessage, c.pk)),
        }));
        sodium.memzero(cleMessage);

        // 3) Signature
        const nonceB64 = b64(nonce);
        const contenuB64 = b64(chiffre);
        const signature = b64(
            sodium.crypto_sign_detached(
                aSigner(conversation.id, niveau, nonceB64, contenuB64),
                k.signSk,
            ),
        );

        return api.envoyerMessage(conversation.id, {
            niveau: niveau,
            contenu_chiffre: contenuB64,
            nonce: nonceB64,
            signature: signature,
            cles: enveloppes,
        });
    }

    /**
     * Récupère et déchiffre une page de messages.
     * Chaque élément : { id, expediteur, niveau, texte, date, signatureValide, erreur }
     * IMPORTANT : `texte` est du texte brut : l'afficher avec textContent, jamais innerHTML.
     */
    async function lire(conversationId, page) {
        await ready;
        const k = Trousseau.cles();
        const annuaire = await Confiance.charger(false);
        const rep = await api.messages(conversationId, page);

        const messages = rep.data.map(function (m) {
            const sortie = {
                id: m.id,
                expediteur: m.expediteur,
                niveau: m.niveau,
                texte: null,
                date: null,
                signatureValide: null,
                erreur: null,
            };

            try {
                // Signature (clé de signature actuelle de l'expéditeur)
                const exp = annuaire.get(m.expediteur.id);
                if (exp && exp.cle_signature) {
                    sortie.signatureValide = signatureValide(
                        m.signature,
                        aSigner(
                            conversationId,
                            m.niveau,
                            m.nonce,
                            m.contenu_chiffre,
                        ),
                        exp.cle_signature,
                    );
                }

                // Déchiffrement
                if (!m.ma_cle) throw new Error("enveloppe absente");
                const cleMessage = sodium.crypto_box_seal_open(
                    deB64(m.ma_cle),
                    k.boxPk,
                    k.boxSk,
                );
                const clair = sodium.crypto_secretbox_open_easy(
                    deB64(m.contenu_chiffre),
                    deB64(m.nonce),
                    cleMessage,
                );
                sodium.memzero(cleMessage);
                const obj = JSON.parse(enTexte(clair));
                sortie.texte = obj.t;
                sortie.date = obj.d;
            } catch (e) {
                sortie.erreur =
                    "Message illisible (clé différente, message altéré ou clé rotée).";
            }
            return sortie;
        });

        return {
            messages: messages,
            page: rep.current_page,
            derniere: rep.last_page,
        };
    }

    /* ------------------------------------------------------------------ */
    /*  Documents chiffrés de bout en bout                                 */
    /* ------------------------------------------------------------------ */
    // Une clé maître aléatoire par document, scellée pour chaque destinataire.
    // Elle donne deux sous-clés distinctes : une pour le fichier (flux par blocs de 64 Ko,
    // authentifié bloc par bloc, avec détection de troncature) et une pour les métadonnées
    // (nom, type, taille réelle), que le serveur ne voit donc jamais.
    const TAILLE_BLOC = 64 * 1024;
    const MAX_DOC_OCTETS = (APP.maxDocMo || 20) * 1024 * 1024;

    function sousCles(maitre) {
        return {
            fichier: sodium.crypto_kdf_derive_from_key(
                32,
                1,
                "SEDFILE1",
                maitre,
            ),
            meta: sodium.crypto_kdf_derive_from_key(32, 2, "SEDMETA1", maitre),
        };
    }

    function chiffrerFlux(octets, cle) {
        const S = sodium;
        const init = S.crypto_secretstream_xchacha20poly1305_init_push(cle);
        const nb = Math.max(1, Math.ceil(octets.length / TAILLE_BLOC)); // un fichier vide = 1 bloc final vide
        const morceaux = [];
        let total = 0;

        for (let i = 0; i < nb; i++) {
            const bloc = octets.subarray(
                i * TAILLE_BLOC,
                (i + 1) * TAILLE_BLOC,
            );
            const tag =
                i === nb - 1
                    ? S.crypto_secretstream_xchacha20poly1305_TAG_FINAL
                    : S.crypto_secretstream_xchacha20poly1305_TAG_MESSAGE;
            const c = S.crypto_secretstream_xchacha20poly1305_push(
                init.state,
                bloc,
                null,
                tag,
            );
            morceaux.push(c);
            total += c.length;
        }

        const chiffre = new Uint8Array(total);
        let pos = 0;
        morceaux.forEach(function (c) {
            chiffre.set(c, pos);
            pos += c.length;
        });
        return { header: init.header, chiffre: chiffre };
    }

    function dechiffrerFlux(chiffre, header, cle) {
        const S = sodium;
        const taille =
            TAILLE_BLOC + S.crypto_secretstream_xchacha20poly1305_ABYTES;
        const etat = S.crypto_secretstream_xchacha20poly1305_init_pull(
            header,
            cle,
        );
        const morceaux = [];
        let total = 0;
        let final = false;

        for (let pos = 0; pos < chiffre.length; pos += taille) {
            const r = S.crypto_secretstream_xchacha20poly1305_pull(
                etat,
                chiffre.subarray(pos, pos + taille),
            );
            if (!r) throw new Error("Fichier corrompu ou altéré.");
            morceaux.push(r.message);
            total += r.message.length;
            if (r.tag === S.crypto_secretstream_xchacha20poly1305_TAG_FINAL) {
                if (pos + taille < chiffre.length)
                    throw new Error("Fichier altéré : données après la fin.");
                final = true;
                break;
            }
        }
        if (!final) throw new Error("Fichier tronqué.");

        const clair = new Uint8Array(total);
        let p = 0;
        morceaux.forEach(function (m) {
            clair.set(m, p);
            p += m.length;
        });
        return clair;
    }

    // Ce que l'expéditeur signe : tout ce qui décrit le document chiffré
    const aSignerDoc = (niveau, header, metaNonce, meta, hash) =>
        enOctets(["doc", niveau, header, metaNonce, meta, hash].join("|"));

    async function lireOctets(blob) {
        if (blob.arrayBuffer) return new Uint8Array(await blob.arrayBuffer());
        return new Promise(function (ok, ko) {
            const r = new FileReader();
            r.onload = function () {
                ok(new Uint8Array(r.result));
            };
            r.onerror = function () {
                ko(new Error("Lecture du fichier impossible."));
            };
            r.readAsArrayBuffer(blob);
        });
    }

    /**
     * Chiffre un fichier et le dépose pour les agents choisis (l'expéditeur y est ajouté).
     * @param {File|Blob} fichier
     * @param {number} niveau  1 à 4
     * @param {string[]} idsDestinataires
     */
    async function deposer(fichier, niveau, idsDestinataires) {
        await ready;
        const k = Trousseau.cles();

        if (fichier.size > MAX_DOC_OCTETS) {
            throw new Error(
                "Fichier trop volumineux (maximum " +
                    Math.round(MAX_DOC_OCTETS / 1048576) +
                    " Mo).",
            );
        }

        const annuaire = await Confiance.charger(false);
        const alertes = Confiance.changements();
        const ids = Array.from(
            new Set(idsDestinataires.concat([APP.agent.id])),
        );

        const dest = ids.map(function (id) {
            const a = annuaire.get(id);
            if (!a || !a.cle_chiffrement)
                throw new Error(
                    ((a && a.nom) || "Un destinataire") +
                        " n'a pas encore de clé de chiffrement.",
                );
            if (alertes.indexOf(id) !== -1)
                throw new Error(
                    "La clé de " +
                        a.nom +
                        " a changé. Vérifiez son empreinte avant d'envoyer.",
                );
            return { agent_id: id, pk: deB64(a.cle_chiffrement) };
        });

        const octets = await lireOctets(fichier);

        // 1) Clé maître et sous-clés
        const maitre = sodium.randombytes_buf(32);
        const sk = sousCles(maitre);

        // 2) Fichier chiffré par blocs
        const flux = chiffrerFlux(octets, sk.fichier);

        // 3) Métadonnées chiffrées (le serveur ne voit pas le nom du fichier)
        const metaNonce = sodium.randombytes_buf(
            sodium.crypto_secretbox_NONCEBYTES,
        );
        const meta = sodium.crypto_secretbox_easy(
            enOctets(
                JSON.stringify({
                    nom: fichier.name || "document",
                    type: fichier.type || "",
                    taille: octets.length,
                    date: new Date().toISOString(),
                }),
            ),
            metaNonce,
            sk.meta,
        );

        // 4) Empreinte du fichier chiffré et signature
        const hash = b64(sodium.crypto_generichash(32, flux.chiffre));
        const headerB64 = b64(flux.header);
        const metaNonceB64 = b64(metaNonce);
        const metaB64 = b64(meta);
        const signature = b64(
            sodium.crypto_sign_detached(
                aSignerDoc(niveau, headerB64, metaNonceB64, metaB64, hash),
                k.signSk,
            ),
        );

        // 5) Enveloppe de la clé maître pour chaque destinataire
        const enveloppes = dest.map((d) => ({
            agent_id: d.agent_id,
            cle: b64(sodium.crypto_box_seal(maitre, d.pk)),
        }));

        sodium.memzero(maitre);
        sodium.memzero(sk.fichier);
        sodium.memzero(sk.meta);

        const form = new FormData();
        form.append(
            "fichier",
            new Blob([flux.chiffre], { type: "application/octet-stream" }),
            "document.bin",
        );
        form.append("niveau", String(niveau));
        form.append("header", headerB64);
        form.append("meta_nonce", metaNonceB64);
        form.append("meta_chiffree", metaB64);
        form.append("hash", hash);
        form.append("signature", signature);
        form.append("destinataires", JSON.stringify(enveloppes));

        return api.deposerDocument(form);
    }

    /**
     * Liste les documents de l'agent, avec nom et taille déchiffrés.
     * Chaque élément : { id, expediteur, niveau, nom, type, taille, date, signatureValide, erreur, brut }
     * IMPORTANT : `nom` est du texte brut : l'afficher avec textContent, jamais innerHTML.
     */
    async function listerDocuments() {
        await ready;
        const k = Trousseau.cles();
        const annuaire = await Confiance.charger(false);
        const liste = await api.documents();

        return liste.map(function (d) {
            const sortie = {
                id: d.id,
                expediteur: d.expediteur,
                niveau: d.niveau,
                nom: null,
                type: null,
                taille: null,
                date: d.created_at,
                signatureValide: null,
                erreur: null,
                brut: d,
            };
            try {
                const exp = annuaire.get(d.expediteur.id);
                if (exp && exp.cle_signature) {
                    sortie.signatureValide = signatureValide(
                        d.signature,
                        aSignerDoc(
                            d.niveau,
                            d.header,
                            d.meta_nonce,
                            d.meta_chiffree,
                            d.hash,
                        ),
                        exp.cle_signature,
                    );
                }
                if (!d.ma_cle) throw new Error("enveloppe absente");
                const maitre = sodium.crypto_box_seal_open(
                    deB64(d.ma_cle),
                    k.boxPk,
                    k.boxSk,
                );
                const sk = sousCles(maitre);
                const meta = JSON.parse(
                    enTexte(
                        sodium.crypto_secretbox_open_easy(
                            deB64(d.meta_chiffree),
                            deB64(d.meta_nonce),
                            sk.meta,
                        ),
                    ),
                );
                sodium.memzero(maitre);
                sodium.memzero(sk.fichier);
                sodium.memzero(sk.meta);
                sortie.nom = String(meta.nom);
                sortie.type = String(meta.type || "");
                sortie.taille = meta.taille;
                sortie.date = meta.date || d.created_at;
            } catch (e) {
                sortie.erreur =
                    "Document illisible (clé différente ou métadonnées altérées).";
            }
            return sortie;
        });
    }

    /** Télécharge, vérifie et déchiffre un document. Renvoie { nom, type, octets }. */
    async function ouvrirDocument(doc) {
        await ready;
        const k = Trousseau.cles();

        if (doc.erreur) throw new Error(doc.erreur);
        if (doc.signatureValide === false)
            throw new Error("Signature invalide : téléchargement refusé.");

        const chiffre = await api.fichierDocument(doc.id);

        if (b64(sodium.crypto_generichash(32, chiffre)) !== doc.brut.hash) {
            throw new Error(
                "Le fichier a été altéré sur le serveur (empreinte différente).",
            );
        }

        const maitre = sodium.crypto_box_seal_open(
            deB64(doc.brut.ma_cle),
            k.boxPk,
            k.boxSk,
        );
        const sk = sousCles(maitre);
        let octets;
        try {
            octets = dechiffrerFlux(
                chiffre,
                deB64(doc.brut.header),
                sk.fichier,
            );
        } finally {
            sodium.memzero(maitre);
            sodium.memzero(sk.fichier);
            sodium.memzero(sk.meta);
        }
        return { nom: doc.nom, type: doc.type, octets: octets };
    }

    /* ------------------------------------------------------------------ */
    /*  Données structurées chiffrées (alertes, personnes, véhicules...)   */
    /* ------------------------------------------------------------------ */
    // Le serveur voit le type, la classification, la priorité et les statuts.
    // L'objet et tous les champs du formulaire sont chiffrés de bout en bout.
    const PRIORITES = ["normale", "haute", "urgente"];
    const MAX_CHARGE_DONNEES = 60000; // octets de JSON clair (limite serveur : 90 000 caractères en base64)
    const LONG_TEXTE = 300;
    const LONG_ZONE = 5000;
    const MAX_PHOTO_SOURCE = 15 * 1024 * 1024; // photo d'origine acceptée avant réduction
    const MAX_PHOTO_OCTETS = 4 * 1024 * 1024; // photo après réduction (limite serveur : 5 Mo chiffrés)
    const COTE_MAX_PHOTO = 1600; // plus grand côté, en pixels
    const FORMATS_PHOTO = ["image/jpeg", "image/png", "image/webp"];

    const estJpeg = (o) =>
        o.length > 3 && o[0] === 0xff && o[1] === 0xd8 && o[2] === 0xff;

    /**
     * Redessine l'image dans un canvas et la ré-encode en JPEG : taille réduite, et surtout
     * toutes les métadonnées (position GPS, modèle d'appareil, date) sont supprimées.
     * Renvoie null si le navigateur ne sait pas dessiner (cas rarissime).
     */
    function reduireImage(fichier) {
        return new Promise(function (ok, ko) {
            let canvas;
            try {
                canvas = document.createElement("canvas");
                if (!canvas.getContext || !canvas.getContext("2d")) {
                    ok(null);
                    return;
                }
            } catch (e) {
                ok(null);
                return;
            }

            const url = URL.createObjectURL(fichier);
            const img = new Image();
            const delai = setTimeout(function () {
                URL.revokeObjectURL(url);
                ko(
                    new Error(
                        "Lecture de la photo impossible (délai dépassé).",
                    ),
                );
            }, 20000);

            img.onerror = function () {
                clearTimeout(delai);
                URL.revokeObjectURL(url);
                ko(new Error("Ce fichier n'est pas une image lisible."));
            };
            img.onload = function () {
                clearTimeout(delai);
                URL.revokeObjectURL(url);
                let largeur = img.naturalWidth;
                let hauteur = img.naturalHeight;
                if (!largeur || !hauteur) {
                    ko(new Error("Image invalide."));
                    return;
                }

                const echelle = Math.min(
                    1,
                    COTE_MAX_PHOTO / Math.max(largeur, hauteur),
                );
                largeur = Math.max(1, Math.round(largeur * echelle));
                hauteur = Math.max(1, Math.round(hauteur * echelle));
                canvas.width = largeur;
                canvas.height = hauteur;
                const ctx = canvas.getContext("2d");
                ctx.fillStyle = "#ffffff"; // fond blanc pour les PNG transparents
                ctx.fillRect(0, 0, largeur, hauteur);
                ctx.drawImage(img, 0, 0, largeur, hauteur);

                const encoder = (qualite) =>
                    new Promise((res) =>
                        canvas.toBlob(res, "image/jpeg", qualite),
                    );
                (async function () {
                    let blob = await encoder(0.85);
                    if (blob && blob.size > MAX_PHOTO_OCTETS)
                        blob = await encoder(0.6);
                    if (!blob)
                        throw new Error("Compression de la photo impossible.");
                    ok({
                        octets: await lireOctets(blob),
                        largeur: largeur,
                        hauteur: hauteur,
                    });
                })().catch(ko);
            };
            img.src = url;
        });
    }

    /** Contrôle et prépare la photo : JPEG sans métadonnées, 1600 px maximum. */
    async function preparerPhoto(fichier) {
        if (FORMATS_PHOTO.indexOf(fichier.type) === -1) {
            throw new Error(
                "Format de photo non pris en charge (JPEG, PNG ou WebP).",
            );
        }
        if (fichier.size > MAX_PHOTO_SOURCE) {
            throw new Error(
                "Photo trop volumineuse (" +
                    Math.round(MAX_PHOTO_SOURCE / 1048576) +
                    " Mo maximum).",
            );
        }

        let photo = await reduireImage(fichier);
        if (!photo) {
            // Sans canvas : on n'accepte que du JPEG déjà léger, sans ré-encodage
            const octets = await lireOctets(fichier);
            if (!estJpeg(octets))
                throw new Error(
                    "Ce navigateur ne peut convertir que des photos JPEG.",
                );
            photo = { octets: octets, largeur: null, hauteur: null };
        }
        if (photo.octets.length > MAX_PHOTO_OCTETS) {
            throw new Error(
                "Photo trop volumineuse même après réduction (" +
                    Math.round(MAX_PHOTO_OCTETS / 1048576) +
                    " Mo maximum).",
            );
        }
        return photo;
    }

    // Pour ajouter un type : l'ajouter ici ET dans EchangeDonnees::TYPES (PHP).
    // Chaque type commence par le champ « objet », affiché comme titre dans les listes.
    const SCHEMAS = {
        alerte: {
            libelle: "Alerte sécuritaire",
            icone: "fa-bell",
            champs: [
                { cle: "objet", libelle: "Objet", type: "text", requis: true },
                {
                    cle: "gravite",
                    libelle: "Gravité",
                    type: "select",
                    options: ["Faible", "Moyenne", "Élevée", "Critique"],
                    requis: true,
                },
                {
                    cle: "date_heure",
                    libelle: "Date et heure",
                    type: "datetime-local",
                },
                {
                    cle: "lieu",
                    libelle: "Lieu (pays, région, localité)",
                    type: "text",
                    requis: true,
                },
                {
                    cle: "description",
                    libelle: "Description",
                    type: "textarea",
                    requis: true,
                },
                {
                    cle: "actions",
                    libelle: "Actions recommandées",
                    type: "textarea",
                },
            ],
        },
        personne: {
            libelle: "Personne recherchée",
            icone: "fa-user-secret",
            photo: { libelle: "Photo de la personne" },
            champs: [
                { cle: "objet", libelle: "Objet", type: "text", requis: true },
                { cle: "nom", libelle: "Nom", type: "text", requis: true },
                { cle: "prenoms", libelle: "Prénoms", type: "text" },
                { cle: "alias", libelle: "Alias", type: "text" },
                {
                    cle: "sexe",
                    libelle: "Sexe",
                    type: "select",
                    options: ["Masculin", "Féminin", "Inconnu"],
                },
                {
                    cle: "date_naissance",
                    libelle: "Date de naissance",
                    type: "date",
                },
                { cle: "nationalite", libelle: "Nationalité", type: "text" },
                {
                    cle: "signalement",
                    libelle: "Signalement",
                    type: "textarea",
                },
                {
                    cle: "motif",
                    libelle: "Motif de la recherche",
                    type: "textarea",
                    requis: true,
                },
                {
                    cle: "derniere_localisation",
                    libelle: "Dernière localisation connue",
                    type: "text",
                },
                {
                    cle: "conduite",
                    libelle: "Conduite à tenir",
                    type: "textarea",
                },
            ],
        },
        vehicule: {
            libelle: "Véhicule recherché",
            icone: "fa-car",
            photo: { libelle: "Photo du véhicule" },
            champs: [
                { cle: "objet", libelle: "Objet", type: "text", requis: true },
                {
                    cle: "immatriculation",
                    libelle: "Immatriculation",
                    type: "text",
                    requis: true,
                },
                {
                    cle: "pays",
                    libelle: "Pays d'immatriculation",
                    type: "text",
                },
                { cle: "marque", libelle: "Marque", type: "text" },
                { cle: "modele", libelle: "Modèle", type: "text" },
                { cle: "couleur", libelle: "Couleur", type: "text" },
                {
                    cle: "motif",
                    libelle: "Motif de la recherche",
                    type: "textarea",
                    requis: true,
                },
                {
                    cle: "derniere_localisation",
                    libelle: "Dernière localisation connue",
                    type: "text",
                },
                {
                    cle: "conduite",
                    libelle: "Conduite à tenir",
                    type: "textarea",
                },
            ],
        },
        incident: {
            libelle: "Rapport d'incident",
            icone: "fa-triangle-exclamation",
            champs: [
                { cle: "objet", libelle: "Objet", type: "text", requis: true },
                {
                    cle: "date_heure",
                    libelle: "Date et heure",
                    type: "datetime-local",
                    requis: true,
                },
                { cle: "lieu", libelle: "Lieu", type: "text", requis: true },
                {
                    cle: "description",
                    libelle: "Description des faits",
                    type: "textarea",
                    requis: true,
                },
                {
                    cle: "bilan",
                    libelle: "Bilan (victimes, dégâts)",
                    type: "textarea",
                },
                { cle: "suites", libelle: "Suites données", type: "textarea" },
            ],
        },
        demande: {
            libelle: "Demande d'information",
            icone: "fa-circle-question",
            champs: [
                { cle: "objet", libelle: "Objet", type: "text", requis: true },
                {
                    cle: "details",
                    libelle: "Détails de la demande",
                    type: "textarea",
                    requis: true,
                },
                {
                    cle: "delai",
                    libelle: "Réponse souhaitée avant le",
                    type: "date",
                },
            ],
        },
    };

    /**
     * Contrôle les valeurs saisies selon le schéma du type. Ne garde que les champs connus.
     * Renvoie l'objet nettoyé, ou lève une erreur qui liste les champs à corriger.
     */
    function validerChamps(type, valeurs) {
        const schema = SCHEMAS[type];
        if (!schema) throw new Error("Type d’échange inconnu.");

        const propres = {};
        const manquants = [];
        const invalides = [];

        schema.champs.forEach(function (c) {
            let v =
                valeurs &&
                valeurs[c.cle] !== undefined &&
                valeurs[c.cle] !== null
                    ? String(valeurs[c.cle]).trim()
                    : "";

            if (!v) {
                if (c.requis) manquants.push(c.libelle);
                return;
            }
            if (c.type === "select" && c.options.indexOf(v) === -1) {
                invalides.push(c.libelle);
                return;
            }
            if (c.type === "date" && !/^\d{4}-\d{2}-\d{2}$/.test(v)) {
                invalides.push(c.libelle);
                return;
            }
            if (
                c.type === "datetime-local" &&
                !/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/.test(v)
            ) {
                invalides.push(c.libelle);
                return;
            }

            const max = c.type === "textarea" ? LONG_ZONE : LONG_TEXTE;
            if (v.length > max) {
                invalides.push(c.libelle + " (" + max + " caractères maximum)");
                return;
            }
            propres[c.cle] = v;
        });

        if (manquants.length)
            throw new Error(
                "Champs obligatoires : " + manquants.join(", ") + ".",
            );
        if (invalides.length)
            throw new Error("Valeur invalide : " + invalides.join(", ") + ".");
        return propres;
    }

    /** Destinataires chiffrables (l'expéditeur est toujours ajouté) : clé présente, pas de clé modifiée. */
    async function destinatairesChiffrables(idsDestinataires) {
        const annuaire = await Confiance.charger(false);
        const alertes = Confiance.changements();
        const ids = Array.from(
            new Set(idsDestinataires.concat([APP.agent.id])),
        );

        return ids.map(function (id) {
            const a = annuaire.get(id);
            if (!a || !a.cle_chiffrement)
                throw new Error(
                    ((a && a.nom) || "Un destinataire") +
                        " n'a pas encore de clé de chiffrement.",
                );
            if (alertes.indexOf(id) !== -1)
                throw new Error(
                    "La clé de " +
                        a.nom +
                        " a changé. Vérifiez son empreinte avant d'envoyer.",
                );
            return { agent_id: id, pk: deB64(a.cle_chiffrement) };
        });
    }

    /* ---- Validation des envois vers un autre État ---- */
    // Un échange destiné à un autre État n'est lisible par cet État qu'après approbation de TOUS les
    // administrateurs actifs de l'État émetteur. Avant, les destinataires étrangers n'ont AUCUNE enveloppe
    // de clé : même un serveur compromis ne peut pas leur donner accès.

    // Texte signé par un administrateur : lié à CET échange, à la signature de l'expéditeur et à son choix
    const aSignerValidation = (
        echangeId,
        decision,
        signatureExpediteur,
        motifNonce,
        motifChiffre,
    ) =>
        enOctets(
            [
                "valid",
                echangeId,
                decision,
                signatureExpediteur,
                motifNonce || "",
                motifChiffre || "",
            ].join("|"),
        );

    /** Qui reçoit tout de suite, qui attend, qui doit valider ? (même règle que ValidationEchange::planifier côté PHP) */
    async function analyserEnvoi(idsDestinataires, forcer) {
        const annuaire = await Confiance.charger(!!forcer);
        const moi = annuaire.get(APP.agent.id);
        const plan = {
            etat: moi ? moi.etat : null,
            internes: [],
            externes: [],
            valideurs: [],
            besoinValidation: false,
            libererDirect: false,
            erreur: null,
        };
        if (!moi) {
            plan.erreur = "Votre profil est absent de l'annuaire.";
            return plan;
        }

        idsDestinataires.forEach(function (id) {
            const a = annuaire.get(id);
            if (!a || id === moi.id) return;
            (a.etat === moi.etat ? plan.internes : plan.externes).push(a);
        });

        plan.besoinValidation = plan.externes.length > 0;
        if (!plan.besoinValidation) return plan;

        // Valideurs : tous les administrateurs actifs de MON État, sauf moi
        plan.valideurs = Array.from(annuaire.values()).filter(
            (a) => a.est_admin && a.etat === moi.etat && a.id !== moi.id,
        );
        if (!plan.valideurs.length && !moi.est_admin) {
            plan.erreur =
                "Aucun administrateur actif dans votre État pour valider cet envoi vers un autre État.";
        }
        // Seul administrateur de mon État : mon envoi vaut validation, les clés sont remises tout de suite
        plan.libererDirect = !plan.valideurs.length && !!moi.est_admin;
        return plan;
    }

    /** Clés publiques d'une liste d'agents (clé présente, aucune clé modifiée depuis l'épinglage). */
    function clesDe(ids, annuaire, alertes, commeValideur) {
        return ids.map(function (id) {
            const a = annuaire.get(id);
            if (!a || !a.cle_chiffrement) {
                throw new Error(
                    ((a && a.nom) || "Un agent") +
                        " n'a pas encore de clé de chiffrement" +
                        (commeValideur
                            ? " : il ne pourra pas valider cet envoi."
                            : "."),
                );
            }
            if (alertes.indexOf(id) !== -1)
                throw new Error(
                    "La clé de " +
                        a.nom +
                        " a changé. Vérifiez son empreinte avant d'envoyer.",
                );
            return { agent_id: id, pk: deB64(a.cle_chiffrement) };
        });
    }

    /**
     * Vérifie que TOUS les administrateurs exigés par l'expéditeur ont approuvé, avec une signature valide.
     * La liste est écrite par l'expéditeur DANS le contenu chiffré et signé : le serveur ne peut pas la raccourcir.
     */
    function verifierValidations(requis, validations, annuaire) {
        const manquants = [];
        const invalides = [];
        (requis || []).forEach(function (id) {
            const v = validations.find((x) => x.agent.id === id);
            const nom =
                (v && v.agent.nom) ||
                (annuaire.get(id) && annuaire.get(id).nom) ||
                id;
            if (!v || v.decision !== "approuve") manquants.push(nom);
            else if (v.signatureOk !== true) invalides.push(nom);
        });
        return {
            complet: !manquants.length && !invalides.length,
            manquants: manquants,
            invalides: invalides,
            exige: (requis || []).length,
        };
    }

    // Ce que l'expéditeur signe : le type, la classification et la priorité (visibles du serveur)
    // sont liés au contenu chiffré, donc le serveur ne peut ni les changer ni les rejouer ailleurs.
    // La photo (en-tête et empreinte du fichier chiffré) est liée au même message : on ne peut pas la remplacer.
    // Sans photo, le texte signé reste identique à celui des échanges déjà envoyés.
    const aSignerDonnees = (
        type,
        niveau,
        priorite,
        nonce,
        contenu,
        photoHeader,
        photoHash,
    ) =>
        enOctets(
            ["don", type, niveau, priorite, nonce, contenu]
                .concat(photoHash ? [photoHeader, photoHash] : [])
                .join("|"),
        );

    /**
     * @param {File} [fichierPhoto]  photo facultative (types personne et véhicule)
     */
    async function envoyerDonnees(
        type,
        niveau,
        priorite,
        valeurs,
        idsDestinataires,
        fichierPhoto,
    ) {
        await ready;
        const k = Trousseau.cles();

        if (PRIORITES.indexOf(priorite) === -1)
            throw new Error("Priorité invalide.");
        const champs = validerChamps(type, valeurs);
        if (fichierPhoto && !SCHEMAS[type].photo)
            throw new Error("Ce type d'échange n'accepte pas de photo.");

        const photo = fichierPhoto ? await preparerPhoto(fichierPhoto) : null;

        // Qui reçoit tout de suite, qui attend la validation des administrateurs, qui doit valider
        const plan = await analyserEnvoi(idsDestinataires, true);
        if (plan.erreur) throw new Error(plan.erreur);
        const annuaire = await Confiance.charger(false);
        const alertes = Confiance.changements();

        const clair = enOctets(
            JSON.stringify({
                v: 1,
                type: type,
                champs: champs,
                photo: photo
                    ? {
                          type: "image/jpeg",
                          taille: photo.octets.length,
                          largeur: photo.largeur,
                          hauteur: photo.hauteur,
                      }
                    : undefined,
                // Administrateurs dont l'approbation est exigée : relue par les destinataires pour vérifier la validation
                valideurs: plan.valideurs.length
                    ? plan.valideurs.map((a) => a.id)
                    : undefined,
                date: new Date().toISOString(),
            }),
        );
        if (clair.length > MAX_CHARGE_DONNEES)
            throw new Error("Contenu trop volumineux.");

        // Enveloppes : moi, mes collègues de mon État, et (libération directe) les autres États.
        // Les destinataires d'un autre État n'en reçoivent PAS avant la validation, mais leur clé doit déjà exister.
        const idsAvecCle = Array.from(
            new Set(
                [APP.agent.id].concat(
                    plan.internes.map((a) => a.id),
                    plan.libererDirect ? plan.externes.map((a) => a.id) : [],
                ),
            ),
        );
        const dest = clesDe(idsAvecCle, annuaire, alertes, false);
        clesDe(
            plan.externes.map((a) => a.id),
            annuaire,
            alertes,
            false,
        );
        const valideurs = clesDe(
            plan.valideurs.map((a) => a.id),
            annuaire,
            alertes,
            true,
        );

        const cle = sodium.crypto_secretbox_keygen();
        const nonce = sodium.randombytes_buf(
            sodium.crypto_secretbox_NONCEBYTES,
        );
        const chiffre = sodium.crypto_secretbox_easy(clair, nonce, cle);

        // Photo : sous-clé dédiée dérivée de la clé de l'échange, flux chiffré par blocs
        let photoChiffree = null;
        if (photo) {
            const clePhoto = sodium.crypto_kdf_derive_from_key(
                32,
                3,
                "SEDPHOTO",
                cle,
            );
            const flux = chiffrerFlux(photo.octets, clePhoto);
            sodium.memzero(clePhoto);
            photoChiffree = {
                chiffre: flux.chiffre,
                header: b64(flux.header),
                hash: b64(sodium.crypto_generichash(32, flux.chiffre)),
            };
        }

        const sceller = (liste) =>
            liste.map((d) => ({
                agent_id: d.agent_id,
                cle: b64(sodium.crypto_box_seal(cle, d.pk)),
            }));
        const enveloppes = sceller(dest).concat(
            plan.libererDirect
                ? []
                : plan.externes.map((a) => ({ agent_id: a.id, cle: null })),
        );
        const enveloppesValideurs = sceller(valideurs);
        sodium.memzero(cle);

        const nonceB64 = b64(nonce);
        const contenuB64 = b64(chiffre);
        const signature = b64(
            sodium.crypto_sign_detached(
                aSignerDonnees(
                    type,
                    niveau,
                    priorite,
                    nonceB64,
                    contenuB64,
                    photoChiffree && photoChiffree.header,
                    photoChiffree && photoChiffree.hash,
                ),
                k.signSk,
            ),
        );

        if (!photoChiffree) {
            return api.envoyerDonnees({
                type: type,
                niveau: niveau,
                priorite: priorite,
                contenu_chiffre: contenuB64,
                nonce: nonceB64,
                signature: signature,
                destinataires: enveloppes,
                valideurs: enveloppesValideurs,
            });
        }

        // Avec photo : envoi de fichier (multipart)
        const form = new FormData();
        form.append("type", type);
        form.append("niveau", String(niveau));
        form.append("priorite", priorite);
        form.append("contenu_chiffre", contenuB64);
        form.append("nonce", nonceB64);
        form.append("signature", signature);
        enveloppes.forEach(function (e, i) {
            form.append("destinataires[" + i + "][agent_id]", e.agent_id);
            if (e.cle) form.append("destinataires[" + i + "][cle]", e.cle); // absente : destinataire d'un autre État
        });
        enveloppesValideurs.forEach(function (e, i) {
            form.append("valideurs[" + i + "][agent_id]", e.agent_id);
            form.append("valideurs[" + i + "][cle]", e.cle);
        });
        form.append(
            "photo",
            new Blob([photoChiffree.chiffre], {
                type: "application/octet-stream",
            }),
            "photo.bin",
        );
        form.append("photo_header", photoChiffree.header);
        form.append("photo_hash", photoChiffree.hash);

        const reponse = await api.envoyerDonneesForm(form);

        if (!reponse || reponse.a_photo !== true) {
            const ancien = !reponse || reponse.a_photo === undefined;
            const err = new Error(
                ancien
                    ? "L'échange a été envoyé, mais le serveur n'a PAS enregistré la photo : le contrôleur n'est pas à jour. " +
                          "Remplacez EchangeDonneesController.php, puis renvoyez l'échange (celui-ci sera affiché « signature invalide »)."
                    : "L'échange a été envoyé, mais le serveur n'a PAS enregistré la photo, alors que le contrôleur est à jour. " +
                          "Vérifiez que EchangeDonnees.php contient les champs photo_* dans $fillable, que la migration est faite " +
                          "(php artisan migrate) et les limites d'envoi de php.ini (upload_max_filesize, post_max_size), " +
                          "puis renvoyez l'échange.",
            );
            err.code = "photo_non_enregistree";
            throw err;
        }
        return reponse;
    }

    /**
     * Liste les échanges (envoyés et reçus) déchiffrés.
     * Chaque élément : { id, type, niveau, priorite, sens, statut, expediteur, destinataires, date,
     *                    objet, champs, signatureValide, erreur }
     * IMPORTANT : objet et champs sont du texte brut : les afficher avec textContent, jamais innerHTML.
     */
    async function listerDonnees() {
        await ready;
        const k = Trousseau.cles();
        const annuaire = await Confiance.charger(false);
        const liste = await api.donnees();

        return liste.map(function (d) {
            const sortie = {
                id: d.id,
                type: d.type,
                niveau: d.niveau,
                priorite: d.priorite,
                sens: d.sens,
                statut: d.statut,
                expediteur: d.expediteur,
                destinataires: d.destinataires || [],
                nb_destinataires: d.nb_destinataires,
                date: d.created_at,
                objet: null,
                champs: null,
                signatureValide: null,
                erreur: null,
                a_photo: !!d.a_photo,
                photo: null,
                photo_manquante: false,
                brut: d,
                validation: d.validation || "non_requise",
                expire_le: d.expire_le || null,
                ma_validation: d.ma_validation || null,
                validations: (d.validations || []).map((v) => ({
                    agent: v.agent,
                    decision: v.decision,
                    decide_le: v.decide_le,
                    signatureOk: null,
                    motif: null,
                })),
                valideursExiges: [],
                valideursIndisponibles: [],
                verification: null,
            };
            try {
                const exp = annuaire.get(d.expediteur.id);
                if (exp && exp.cle_signature) {
                    sortie.signatureValide = signatureValide(
                        d.signature,
                        aSignerDonnees(
                            d.type,
                            d.niveau,
                            d.priorite,
                            d.nonce,
                            d.contenu_chiffre,
                            d.photo_header,
                            d.photo_hash,
                        ),
                        exp.cle_signature,
                    );
                }
                if (!d.ma_cle) throw new Error("enveloppe absente");
                const cle = sodium.crypto_box_seal_open(
                    deB64(d.ma_cle),
                    k.boxPk,
                    k.boxSk,
                );
                const clair = JSON.parse(
                    enTexte(
                        sodium.crypto_secretbox_open_easy(
                            deB64(d.contenu_chiffre),
                            deB64(d.nonce),
                            cle,
                        ),
                    ),
                );

                // Le type annoncé par le serveur doit être celui que l'expéditeur a chiffré
                if (clair.type !== d.type || !clair.champs)
                    throw new Error("contenu incohérent");
                sortie.champs = clair.champs;
                sortie.photo = clair.photo || null;
                sortie.photo_manquante = !!clair.photo && !d.a_photo;
                sortie.objet = String(clair.champs.objet || "");
                sortie.date = clair.date || d.created_at;

                // Décisions des administrateurs : signature vérifiée, motif de refus déchiffré avec la clé de l'échange
                (d.validations || []).forEach(function (v, i) {
                    const cible = sortie.validations[i];
                    const va = annuaire.get(v.agent.id);
                    if (v.decision !== "en_attente") {
                        cible.signatureOk =
                            !!(va && va.cle_signature && v.signature) &&
                            signatureValide(
                                v.signature,
                                aSignerValidation(
                                    d.id,
                                    v.decision,
                                    d.signature,
                                    v.motif_nonce,
                                    v.motif_chiffre,
                                ),
                                va.cle_signature,
                            );
                    }
                    if (
                        v.decision === "refuse" &&
                        v.motif_chiffre &&
                        v.motif_nonce
                    ) {
                        try {
                            cible.motif = enTexte(
                                sodium.crypto_secretbox_open_easy(
                                    deB64(v.motif_chiffre),
                                    deB64(v.motif_nonce),
                                    cle,
                                ),
                            );
                        } catch (e) {
                            cible.motif = null;
                        }
                    }
                });
                sortie.valideursExiges = Array.isArray(clair.valideurs)
                    ? clair.valideurs
                    : [];
                sortie.valideursIndisponibles = sortie.valideursExiges.filter(
                    (id) => !annuaire.get(id),
                );
                if (d.validation === "validee" || d.validation === "liberee") {
                    sortie.verification = verifierValidations(
                        sortie.valideursExiges,
                        sortie.validations,
                        annuaire,
                    );
                }
                sodium.memzero(cle);
            } catch (e) {
                sortie.erreur =
                    "Échange illisible (clé différente ou contenu altéré).";
            }
            return sortie;
        });
    }

    /** Télécharge, vérifie et déchiffre la photo d'un échange. Renvoie { octets, type }. */
    async function ouvrirPhotoDonnee(d) {
        await ready;
        const k = Trousseau.cles();

        if (!d.a_photo)
            throw new Error("Cet échange ne contient pas de photo.");
        if (d.erreur) throw new Error(d.erreur);
        if (d.signatureValide === false)
            throw new Error("Signature invalide : photo non affichée.");

        const chiffre = await api.photoDonnees(d.id);
        if (b64(sodium.crypto_generichash(32, chiffre)) !== d.brut.photo_hash) {
            throw new Error(
                "La photo a été altérée sur le serveur (empreinte différente).",
            );
        }

        const cle = sodium.crypto_box_seal_open(
            deB64(d.brut.ma_cle),
            k.boxPk,
            k.boxSk,
        );
        const clePhoto = sodium.crypto_kdf_derive_from_key(
            32,
            3,
            "SEDPHOTO",
            cle,
        );
        let octets;
        try {
            octets = dechiffrerFlux(
                chiffre,
                deB64(d.brut.photo_header),
                clePhoto,
            );
        } finally {
            sodium.memzero(cle);
            sodium.memzero(clePhoto);
        }

        // On n'affiche que du JPEG authentique (jamais de SVG ni de format actif)
        if (!estJpeg(octets)) throw new Error("Format de photo invalide.");
        return { octets: octets, type: "image/jpeg" };
    }

    /**
     * Un administrateur approuve ou refuse l'envoi d'un échange vers un autre État. La décision est SIGNÉE.
     * Si c'est la dernière approbation, les clés sont aussitôt remises aux destinataires des autres États.
     */
    async function deciderDonnees(d, decision, motifTexte) {
        await ready;
        const k = Trousseau.cles();

        if (decision !== "approuve" && decision !== "refuse")
            throw new Error("Décision inconnue.");
        if (d.ma_validation !== "en_attente" || d.validation !== "en_attente")
            throw new Error("Vous ne pouvez plus décider pour cet échange.");
        if (d.erreur) throw new Error(d.erreur);
        if (d.signatureValide !== true)
            throw new Error(
                "La signature de l'expéditeur n'a pas pu être vérifiée : validation impossible.",
            );

        let motifNonce = null;
        let motifChiffre = null;
        if (decision === "refuse") {
            const motif = String(motifTexte || "").trim();
            if (!motif) throw new Error("Indiquez le motif du refus.");
            if (motif.length > 1000)
                throw new Error("Motif trop long (1000 caractères maximum).");
            // Le motif est chiffré avec la clé de l'échange : lisible par l'expéditeur et les administrateurs, pas par le serveur
            const cle = sodium.crypto_box_seal_open(
                deB64(d.brut.ma_cle),
                k.boxPk,
                k.boxSk,
            );
            const nonce = sodium.randombytes_buf(
                sodium.crypto_secretbox_NONCEBYTES,
            );
            motifChiffre = b64(
                sodium.crypto_secretbox_easy(enOctets(motif), nonce, cle),
            );
            motifNonce = b64(nonce);
            sodium.memzero(cle);
        }

        const signature = b64(
            sodium.crypto_sign_detached(
                aSignerValidation(
                    d.id,
                    decision,
                    d.brut.signature,
                    motifNonce,
                    motifChiffre,
                ),
                k.signSk,
            ),
        );

        const rep = await api.validerDonnees(d.id, {
            decision: decision,
            signature: signature,
            motif_chiffre: motifChiffre,
            motif_nonce: motifNonce,
        });

        const resultat = {
            validation: rep.validation,
            libere: false,
            erreurLiberation: null,
        };
        if (rep.a_liberer) {
            try {
                const liste = await listerDonnees();
                const frais = liste.find((x) => x.id === d.id);
                if (!frais) throw new Error("Échange introuvable.");
                await libererDonnees(frais);
                resultat.libere = true;
            } catch (e) {
                resultat.erreurLiberation = e.message; // l'échange reste « validé » : relance possible
            }
        }
        return resultat;
    }

    /**
     * Remet aux destinataires des autres États la clé de lecture, une fois TOUS les administrateurs d'accord.
     * Le contrôle des approbations est refait ICI, dans le navigateur, sans faire confiance au seul serveur.
     */
    async function libererDonnees(d) {
        await ready;
        const k = Trousseau.cles();

        if (d.validation !== "validee")
            throw new Error("Cet échange n'attend pas de libération.");
        if (d.erreur) throw new Error(d.erreur);
        if (d.signatureValide !== true)
            throw new Error(
                "La signature de l'expéditeur n'a pas pu être vérifiée : libération impossible.",
            );
        if (!d.verification || !d.verification.complet) {
            throw new Error(
                "Toutes les approbations des administrateurs ne sont pas présentes et valides : libération refusée.",
            );
        }

        const annuaire = await Confiance.charger(true);
        const alertes = Confiance.changements();
        const enAttente = (d.destinataires || []).filter(
            (x) => x.statut === "attente",
        );
        if (!enAttente.length)
            throw new Error("Aucun destinataire en attente de clé.");

        const pks = clesDe(
            enAttente.map((x) => x.agent.id),
            annuaire,
            alertes,
            false,
        );
        const cle = sodium.crypto_box_seal_open(
            deB64(d.brut.ma_cle),
            k.boxPk,
            k.boxSk,
        );
        const enveloppes = pks.map((x) => ({
            agent_id: x.agent_id,
            cle: b64(sodium.crypto_box_seal(cle, x.pk)),
        }));
        sodium.memzero(cle);

        return api.libererDonnees(d.id, { enveloppes: enveloppes });
    }

    /**
     * Reprend les libérations interrompues (navigateur fermé trop tôt) pour les échanges dont je suis
     * l'expéditeur ou un administrateur approbateur. `dejaEssayes` évite de réessayer en boucle.
     * @returns {Promise<number>} nombre d'échanges libérés
     */
    async function libererEnAttente(liste, dejaEssayes) {
        let n = 0;
        for (const d of liste) {
            if (d.validation !== "validee") continue;
            if (!(d.sens === "envoye" || d.ma_validation === "approuve"))
                continue;
            if (dejaEssayes) {
                if (dejaEssayes.has(d.id)) continue;
                dejaEssayes.add(d.id);
            }
            try {
                await libererDonnees(d);
                n++;
            } catch (e) {
                /* silencieux : relance manuelle possible */
            }
        }
        return n;
    }

    /** L'expéditeur retire un échange non transmis (refusé, bloqué, ou envoyé par erreur). */
    async function annulerDonnees(d) {
        await ready;
        if (d.sens !== "envoye")
            throw new Error("Seul l'expéditeur peut annuler cet échange.");
        return api.annulerDonnees(d.id);
    }

    /** Un destinataire marque l'échange : 'lu', 'traite' ou 'rejete'. */
    function changerStatutDonnees(id, statut) {
        return api.statutDonnees(id, statut);
    }

    /* ------------------------------------------------------------------ */
    /*  Initialisation : publier ma clé publique si le serveur n'en a pas  */
    /* ------------------------------------------------------------------ */
    async function publierCleSiNecessaire() {
        const pub = Trousseau.publiques();
        if (!pub) return false;
        const moi = await api.moi();
        if (
            moi.cle_active &&
            moi.cle_active.cle_chiffrement === pub.cle_chiffrement
        )
            return false;

        // Clé révoquée (par un administrateur, ou remplacée depuis) : on ne la republie JAMAIS
        if ((moi.cles_revoquees || []).indexOf(pub.cle_chiffrement) !== -1) {
            const err = new Error(
                "La clé de ce trousseau a été révoquée. Supprimez ce trousseau et créez-en un nouveau.",
            );
            err.code = "cle_revoquee";
            throw err;
        }

        await api.enregistrerCle(pub);
        // L'annuaire mis en cache ne contenait pas encore ma clé : on le rafraîchit
        try {
            await Confiance.charger(true);
        } catch (e) {
            /* non bloquant */
        }
        return true;
    }

    /* ------------------------------------------------------------------ */
    /*  API publique                                                       */
    /* ------------------------------------------------------------------ */
    global.Echange = {
        ready: ready,
        trousseau: Trousseau,
        api: api,
        confiance: Confiance,
        messages: { envoyer: envoyer, lire: lire },
        donnees: {
            schemas: SCHEMAS,
            priorites: PRIORITES,
            valider: validerChamps,
            envoyer: envoyerDonnees,
            lister: listerDonnees,
            analyserEnvoi: analyserEnvoi,
            decider: deciderDonnees,
            liberer: libererDonnees,
            libererEnAttente: libererEnAttente,
            annuler: annulerDonnees,
            ouvrirPhoto: ouvrirPhotoDonnee,
            preparerPhoto: preparerPhoto,
            photoMaxOctets: MAX_PHOTO_OCTETS,
            statut: changerStatutDonnees,
        },
        documents: {
            deposer: deposer,
            lister: listerDocuments,
            ouvrir: ouvrirDocument,
            tailleMax: MAX_DOC_OCTETS,
            _flux: {
                chiffrer: chiffrerFlux,
                dechiffrer: dechiffrerFlux,
                TAILLE_BLOC: TAILLE_BLOC,
            }, // interne (tests)
        },
        empreinte: empreinte,
        publierCleSiNecessaire: publierCleSiNecessaire,
    };
})(window);
