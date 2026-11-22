/*!
 * app.js : SED, interface du module « Échanges sécurisés »
 * Dépend de : window.APP, window.Echange (echange.js) et des partials echange/partials/*.
 *
 * Règle de sécurité : tout texte déchiffré est inséré avec textContent (jamais innerHTML).
 */
(function () {
    "use strict";

    const E = window.Echange;
    const APP = window.APP;
    if (!E) {
        const msg =
            "echange.js n'a pas démarré (voir l'erreur précédente dans la console).";
        console.error(msg);
        if (window.sedErreur) window.sedErreur(msg);
        return;
    }

    /* ------------------------------------------------------------------ */
    /*  Utilitaires                                                        */
    /* ------------------------------------------------------------------ */
    const $ = (s, r) => (r || document).querySelector(s);
    const $$ = (s, r) => Array.from((r || document).querySelectorAll(s));
    const toast = (m, t) =>
        (
            window.showToast ||
            function (x) {
                alert(x);
            }
        )(m, t || "info");

    const NIVEAUX = {
        1: "Public",
        2: "Restreint",
        3: "Confidentiel",
        4: "Secret",
    };
    const VUES = [
        "conversations",
        "documents",
        "echanges",
        "trousseau",
        "admin",
    ];
    const INTERVALLE_SYNC_MS = 8000;

    function el(tag, classes, texte) {
        const n = document.createElement(tag);
        if (classes) n.className = classes;
        if (texte !== undefined) n.textContent = texte;
        return n;
    }

    /** Exécute une action en désactivant le bouton et en affichant les erreurs dans un toast. */
    async function agir(bouton, fn) {
        if (bouton) bouton.disabled = true;
        try {
            return await fn();
        } catch (e) {
            toast(e.message || "Erreur inattendue.", "error");
            console.error(e);
        } finally {
            if (bouton) bouton.disabled = false;
        }
    }

    /* ------------------------------------------------------------------ */
    /*  État                                                               */
    /* ------------------------------------------------------------------ */
    let conversations = [];
    let courante = null; // conversation ouverte
    let dernierIdAffiche = null; // évite de redessiner si rien n'a changé
    let occupe = false;

    /* ------------------------------------------------------------------ */
    /*  Navigation (hash #/vue)                                            */
    /* ------------------------------------------------------------------ */
    function vueDemandee() {
        const h = (location.hash || "").replace("#/", "");
        return VUES.indexOf(h) !== -1 && $("#vue-" + h) ? h : null;
    }

    function afficherVue() {
        let nom = vueDemandee();
        if (!nom) {
            nom = E.trousseau.estOuvert() ? "conversations" : "trousseau";
            history.replaceState(null, "", "#/" + nom);
        }
        $$(".echange-vue").forEach((s) =>
            s.classList.toggle("d-none", s.id !== "vue-" + nom),
        );
        $$("#echange-nav .nav-link").forEach((a) =>
            a.classList.toggle("active", a.getAttribute("href") === "#/" + nom),
        );

        if (nom === "trousseau") chargerEmpreintes();
        if (nom === "documents") ouvrirDocuments();
        if (nom === "echanges") ouvrirDonnees();
        if (nom === "admin") ouvrirAdmin();
        if (nom === "conversations" && courante)
            chargerMessages(false).catch(function () {});
    }

    /* ------------------------------------------------------------------ */
    /*  TROUSSEAU                                                          */
    /* ------------------------------------------------------------------ */
    function rendreTrousseau() {
        const existe = E.trousseau.existe();
        const ouvert = E.trousseau.estOuvert();

        $("#zone-creation").classList.toggle("d-none", existe);
        $("#zone-deverrouillage").classList.toggle("d-none", !existe || ouvert);
        $("#zone-cles").classList.toggle("d-none", !existe);

        const pub = E.trousseau.publiques();
        $("#mon-empreinte").textContent = pub
            ? E.empreinte(pub.cle_chiffrement, pub.cle_signature)
            : "—";

        // Vue conversations
        $("#bandeau-verrou").classList.toggle("d-none", ouvert);
        $("#zone-saisie").classList.toggle("d-none", !ouvert);

        // Vue documents (ignorée si resources/views/echange/partials/documents.blade.php est l'ancienne version)
        [
            ["#bandeau-verrou-doc", ouvert],
            ["#zone-depot", !ouvert],
            ["#zone-liste-docs", !ouvert],
            ["#bandeau-verrou-don", ouvert],
            ["#zone-donnees", !ouvert],
        ].forEach(function (c) {
            const n = $(c[0]);
            if (n) n.classList.toggle("d-none", c[1]);
        });
    }

    /** La vue documents est-elle la bonne version ? */
    function vueDocumentsPrete() {
        return !!(
            $("#btn-deposer") &&
            $("#doc-max") &&
            $("#doc-agents") &&
            $("#table-documents")
        );
    }

    function majAlerteNav() {
        const lien = $('#echange-nav a[href="#/trousseau"]');
        if (!lien) return;
        let badge = $(".badge", lien);
        const n = E.confiance.changements().length;
        if (n && !badge)
            lien.appendChild(el("span", "badge bg-danger ms-1", "!"));
        else if (!n && badge) badge.remove();
    }

    async function chargerEmpreintes() {
        const tbody = $("#table-empreintes tbody");
        let annuaire;
        try {
            annuaire = await E.confiance.charger(true);
        } catch (e) {
            toast("Impossible de charger l'annuaire : " + e.message, "error");
            return;
        }
        majAlerteNav();

        const alertes = E.confiance.changements();
        tbody.textContent = "";

        Array.from(annuaire.values()).forEach(function (a) {
            const moi = a.id === APP.agent.id;
            const change = alertes.indexOf(a.id) !== -1;
            const tr = el("tr");

            const tdNom = el("td");
            tdNom.appendChild(el("strong", "", a.nom));
            if (moi)
                tdNom.appendChild(
                    el("span", "badge bg-info text-dark ms-2", "moi"),
                );
            tr.appendChild(tdNom);

            tr.appendChild(
                el("td", "", (a.organisme || "") + " (" + (a.etat || "") + ")"),
            );
            tr.appendChild(el("td", "sed-empreinte", a.empreinte || "—"));

            const tdStatut = el("td");
            if (!a.empreinte)
                tdStatut.appendChild(
                    el("span", "badge bg-secondary", "Pas de clé"),
                );
            else if (change)
                tdStatut.appendChild(
                    el("span", "badge bg-danger", "Clé modifiée"),
                );
            else
                tdStatut.appendChild(
                    el("span", "badge bg-success", "Épinglée"),
                );
            tr.appendChild(tdStatut);

            const tdAction = el("td");
            if (change && !moi) {
                const btn = el("button", "btn btn-sm btn-warning", "Valider");
                btn.type = "button";
                btn.addEventListener("click", function () {
                    const ok = confirm(
                        "La clé de " +
                            a.nom +
                            " a changé.\n\nN'acceptez que si vous avez comparé cette empreinte " +
                            "avec la personne elle-même (téléphone, en face à face), pas par ce système.\n\nValider la nouvelle clé ?",
                    );
                    if (!ok) return;
                    E.confiance.accepterChangement(a.id);
                    chargerEmpreintes();
                });
                tdAction.appendChild(btn);
            } else if (change && moi) {
                tdAction.appendChild(
                    el(
                        "small",
                        "text-danger",
                        "La clé du serveur ne correspond pas à votre trousseau. Contactez l’administrateur.",
                    ),
                );
            }
            tr.appendChild(tdAction);
            tbody.appendChild(tr);
        });
    }

    function sauverBlob(nomFichier, blob) {
        const url = URL.createObjectURL(blob);
        const a = el("a");
        a.href = url;
        a.download = nomFichier;
        document.body.appendChild(a);
        a.click();
        a.remove();
        setTimeout(function () {
            URL.revokeObjectURL(url);
        }, 1000);
    }

    function telecharger(nomFichier, contenu) {
        sauverBlob(
            nomFichier,
            new Blob([contenu], { type: "application/json" }),
        );
    }

    function brancherTrousseau() {
        // Création
        $("#btn-creer-trousseau").addEventListener("click", function (ev) {
            agir(ev.currentTarget, async function () {
                const p1 = $("#phrase-creation").value;
                const p2 = $("#phrase-confirmation").value;
                if (p1 !== p2)
                    throw new Error(
                        "Les deux phrases secrètes ne sont pas identiques.",
                    );

                await E.trousseau.creer(p1);
                await E.publierCleSiNecessaire();

                $("#phrase-creation").value = "";
                $("#phrase-confirmation").value = "";
                $("#alerte-sauvegarde").classList.remove("d-none");
                rendreTrousseau();
                toast(
                    "Trousseau créé. Exportez une sauvegarde maintenant.",
                    "success",
                );
                await chargerConversations();
            });
        });

        // Déverrouillage
        function deverrouiller(ev) {
            agir(ev && ev.currentTarget, async function () {
                const champ = $("#phrase-deverrouiller");
                await E.trousseau.deverrouiller(champ.value);
                champ.value = "";

                let revoquee = false;
                try {
                    await E.publierCleSiNecessaire();
                } catch (e) {
                    if (e.code === "cle_revoquee") revoquee = true;
                    else console.warn("Publication de clé :", e);
                }
                $("#alerte-cle-revoquee").classList.toggle("d-none", !revoquee);
                try {
                    await E.confiance.charger(true);
                } catch (e) {
                    /* non bloquant */
                }
                majAlerteNav();

                if (revoquee) {
                    toast(
                        "La clé de ce trousseau a été révoquée : supprimez-le et créez-en un nouveau.",
                        "error",
                    );
                    if (vueDemandee() !== "trousseau")
                        location.hash = "#/trousseau";
                } else if (E.confiance.changements().length) {
                    toast(
                        "Attention : une ou plusieurs clés ont changé. Vérifiez l'onglet Trousseau.",
                        "error",
                    );
                } else {
                    toast("Trousseau déverrouillé.", "success");
                    if (vueDemandee() === "trousseau")
                        location.hash = "#/conversations";
                }
                await chargerConversations();
            });
        }
        $("#btn-deverrouiller").addEventListener("click", deverrouiller);
        $("#phrase-deverrouiller").addEventListener("keydown", function (ev) {
            if (ev.key === "Enter") {
                ev.preventDefault();
                deverrouiller(null);
            }
        });

        // Suppression du trousseau de cet appareil (phrase perdue ou clé révoquée)
        function supprimerTrousseau() {
            const ok = confirm(
                "Supprimer le trousseau de cet appareil ?\n\n" +
                    "Vos anciens messages, documents et échanges deviendront DÉFINITIVEMENT illisibles, " +
                    "sauf si vous avez une sauvegarde et sa phrase secrète.\n\n" +
                    "Vous pourrez ensuite créer un nouveau trousseau.",
            );
            if (!ok) return;
            E.trousseau.supprimer();
            $("#alerte-cle-revoquee").classList.add("d-none");
            rendreTrousseau();
            toast(
                "Trousseau supprimé. Vous pouvez en créer un nouveau.",
                "info",
            );
        }
        ["#btn-supprimer-trousseau", "#btn-supprimer-trousseau-rev"].forEach(
            function (sel) {
                const b = $(sel);
                if (b) b.addEventListener("click", supprimerTrousseau);
            },
        );

        // Sauvegarde / restauration
        $("#btn-exporter").addEventListener("click", function () {
            try {
                const nom = String(APP.agent.nom || "agent").replace(
                    /[^\w-]+/g,
                    "_",
                );
                telecharger(
                    "trousseau-sed-" + nom + ".json",
                    E.trousseau.exporter(),
                );
                $("#alerte-sauvegarde").classList.add("d-none");
                toast(
                    "Sauvegarde téléchargée. Conservez-la hors de cet ordinateur.",
                    "success",
                );
            } catch (e) {
                toast(e.message, "error");
            }
        });

        $("#fichier-import").addEventListener("change", function (ev) {
            const fichier = ev.target.files[0];
            if (!fichier) return;
            const lecteur = new FileReader();
            lecteur.onload = function () {
                try {
                    let ecraser = false;
                    if (E.trousseau.existe()) {
                        ecraser = confirm(
                            "Un trousseau existe déjà sur cet appareil. Le remplacer par la sauvegarde ?",
                        );
                        if (!ecraser) return;
                    }
                    E.trousseau.importer(String(lecteur.result), ecraser);
                    rendreTrousseau();
                    toast(
                        "Sauvegarde importée. Saisissez votre phrase secrète pour déverrouiller.",
                        "success",
                    );
                } catch (e) {
                    toast(e.message, "error");
                }
            };
            lecteur.readAsText(fichier);
            ev.target.value = "";
        });
    }

    /* ------------------------------------------------------------------ */
    /*  CONVERSATIONS                                                      */
    /* ------------------------------------------------------------------ */
    function nomConversation(c) {
        if (c.titre) return c.titre;
        const autres = (c.membres || [])
            .filter((m) => m.id !== APP.agent.id)
            .map((m) => m.nom);
        return autres.length ? autres.join(", ") : "Moi-même";
    }

    function rendreListe() {
        const liste = $("#liste-conversations");
        liste.textContent = "";

        if (!conversations.length) {
            liste.appendChild(
                el(
                    "p",
                    "text-muted p-3 mb-0",
                    "Aucune conversation. Cliquez sur « Nouvelle ».",
                ),
            );
            return;
        }

        conversations.forEach(function (c) {
            const b = el(
                "button",
                "sed-conv-item" +
                    (courante && courante.id === c.id ? " active" : ""),
            );
            b.type = "button";
            b.appendChild(el("strong", "", nomConversation(c)));
            b.appendChild(
                el("small", "", (c.membres || []).length + " participant(s)"),
            );
            b.addEventListener("click", function () {
                ouvrirConversation(c);
            });
            liste.appendChild(b);
        });
    }

    async function chargerConversations() {
        try {
            conversations = await E.api.conversations();
            if (courante)
                courante =
                    conversations.find((c) => c.id === courante.id) || courante;
            rendreListe();
        } catch (e) {
            toast(
                "Impossible de charger les conversations : " + e.message,
                "error",
            );
        }
    }

    function viderMessages(message) {
        const zone = $("#conv-messages");
        zone.textContent = "";
        if (message)
            zone.appendChild(el("p", "text-muted text-center my-4", message));
    }

    function bulle(m) {
        const moi = m.expediteur && m.expediteur.id === APP.agent.id;
        const ligne = el("div", "sed-msg " + (moi ? "moi" : "autre"));
        const b = el(
            "div",
            "sed-bulle" + (m.signatureValide === false ? " invalide" : ""),
        );

        const entete = el("div", "d-flex align-items-center gap-2");
        entete.appendChild(
            el(
                "strong",
                "",
                moi ? "Moi" : m.expediteur ? m.expediteur.nom : "Inconnu",
            ),
        );
        entete.appendChild(
            el(
                "span",
                "sed-niveau n" + m.niveau,
                NIVEAUX[m.niveau] || "Niveau " + m.niveau,
            ),
        );
        b.appendChild(entete);

        if (m.erreur)
            b.appendChild(
                el("p", "sed-bulle-texte fst-italic text-muted", m.erreur),
            );
        else b.appendChild(el("p", "sed-bulle-texte", m.texte));

        const pied = el("div", "sed-bulle-pied");
        if (m.date)
            pied.appendChild(
                el("span", "", new Date(m.date).toLocaleString("fr-FR")),
            );

        const sig = el("i");
        if (m.signatureValide === true) {
            sig.className = "fas fa-shield-halved text-success";
            sig.title = "Signature vérifiée";
        } else if (m.signatureValide === false) {
            sig.className = "fas fa-triangle-exclamation text-danger";
            sig.title = "Signature INVALIDE : message possiblement altéré";
            pied.appendChild(
                el("span", "text-danger fw-bold", "Signature invalide"),
            );
        } else {
            sig.className = "fas fa-circle-question text-muted";
            sig.title = "Signature non vérifiable";
        }
        pied.appendChild(sig);
        b.appendChild(pied);

        ligne.appendChild(b);
        return ligne;
    }

    function rendreMessages(messages) {
        const zone = $("#conv-messages");
        zone.textContent = "";
        if (!messages.length) {
            viderMessages("Aucun message. Écrivez le premier.");
            return;
        }
        messages.forEach((m) => zone.appendChild(bulle(m)));
        zone.scrollTop = zone.scrollHeight;
    }

    async function chargerMessages(forcer) {
        if (!courante) return;
        if (!E.trousseau.estOuvert()) {
            viderMessages(
                "Trousseau verrouillé : déverrouillez-le pour lire les messages.",
            );
            return;
        }

        const id = courante.id;
        let page = courante.pageFinale || 1;
        let res = await E.messages.lire(id, page);
        while (res.derniere > page) {
            // on va toujours à la dernière page
            page = res.derniere;
            res = await E.messages.lire(id, page);
        }
        if (!courante || courante.id !== id) return; // l'utilisateur a changé de conversation entre-temps
        courante.pageFinale = page;

        const dernier = res.messages.length
            ? res.messages[res.messages.length - 1].id
            : null;
        if (!forcer && dernier === dernierIdAffiche) return;
        dernierIdAffiche = dernier;
        rendreMessages(res.messages);
    }

    async function ouvrirConversation(c) {
        courante = c;
        dernierIdAffiche = null;
        $("#conv-vide").classList.add("d-none");
        $("#conv-fenetre").classList.remove("d-none");
        $("#conv-titre").textContent = nomConversation(c);
        $("#conv-membres").textContent = (c.membres || [])
            .map((m) => m.nom)
            .join(", ");
        rendreListe();
        viderMessages("Chargement…");
        try {
            await chargerMessages(true);
        } catch (e) {
            viderMessages("");
            toast(e.message, "error");
        }
    }

    async function envoyer() {
        if (!courante || occupe) return;
        const champ = $("#msg-texte");
        const texte = champ.value.trim();
        if (!texte) return;

        occupe = true;
        await agir($("#btn-envoyer"), async function () {
            await E.messages.envoyer(
                courante,
                parseInt($("#msg-niveau").value, 10),
                texte,
            );
            champ.value = "";
            await chargerMessages(true);
        });
        occupe = false;
    }

    /* ---- Nouvelle conversation ---- */
    async function basculerPanneauNouvelle() {
        const panneau = $("#panneau-nouvelle-conv");
        panneau.classList.toggle("d-none");
        if (panneau.classList.contains("d-none")) return;

        const zone = $("#nc-agents");
        zone.textContent = "";
        let annuaire;
        try {
            // On recharge à chaque ouverture : un collègue a pu publier sa clé entre-temps
            annuaire = await E.confiance.charger(true);
        } catch (e) {
            toast("Impossible de charger l'annuaire : " + e.message, "error");
            return;
        }
        majAlerteNav();

        Array.from(annuaire.values())
            .filter((a) => a.id !== APP.agent.id)
            .forEach(function (a) {
                const item = el("div", "form-check nc-item");
                item.dataset.recherche = (
                    a.nom +
                    " " +
                    a.organisme +
                    " " +
                    a.etat
                ).toLowerCase();

                const cb = el("input", "form-check-input");
                cb.type = "checkbox";
                cb.value = a.id;
                cb.id = "nc-" + a.id;
                cb.disabled = !a.cle_chiffrement;

                const lab = el(
                    "label",
                    "form-check-label",
                    a.nom +
                        " — " +
                        a.organisme +
                        " (" +
                        a.etat +
                        ")" +
                        (a.cle_chiffrement ? "" : " · pas encore de clé"),
                );
                lab.htmlFor = cb.id;

                item.append(cb, lab);
                zone.appendChild(item);
            });

        if (!zone.children.length)
            zone.appendChild(
                el(
                    "p",
                    "text-muted mb-0",
                    "Aucun autre agent dans l’annuaire.",
                ),
            );
    }

    function brancherConversations() {
        $("#btn-nouvelle-conv").addEventListener(
            "click",
            basculerPanneauNouvelle,
        );

        $("#nc-recherche").addEventListener("input", function (ev) {
            const q = ev.target.value.trim().toLowerCase();
            $$(".nc-item").forEach((i) =>
                i.classList.toggle(
                    "d-none",
                    q && i.dataset.recherche.indexOf(q) === -1,
                ),
            );
        });

        $("#nc-creer").addEventListener("click", function (ev) {
            agir(ev.currentTarget, async function () {
                const ids = $$("#nc-agents input:checked").map((i) => i.value);
                if (!ids.length)
                    throw new Error("Sélectionnez au moins un participant.");

                const conv = await E.api.creerConversation(
                    ids,
                    $("#nc-titre").value.trim(),
                );
                $("#panneau-nouvelle-conv").classList.add("d-none");
                $("#nc-titre").value = "";
                await chargerConversations();
                const c = conversations.find((x) => x.id === conv.id);
                if (c) await ouvrirConversation(c);
            });
        });

        $("#btn-actualiser-conv").addEventListener("click", function (ev) {
            agir(ev.currentTarget, async function () {
                await chargerConversations();
                if (courante) await chargerMessages(true);
            });
        });

        $("#btn-envoyer").addEventListener("click", envoyer);
        $("#msg-texte").addEventListener("keydown", function (ev) {
            if (ev.key === "Enter" && (ev.ctrlKey || ev.metaKey)) {
                ev.preventDefault();
                envoyer();
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /*  DOCUMENTS                                                          */
    /* ------------------------------------------------------------------ */
    function formatTaille(octets) {
        if (octets === null || octets === undefined) return "—";
        if (octets < 1024) return octets + " o";
        if (octets < 1048576)
            return (octets / 1024).toFixed(1).replace(".", ",") + " Ko";
        return (octets / 1048576).toFixed(1).replace(".", ",") + " Mo";
    }

    /** Nom de fichier sans caractères dangereux (le nom vient d'un document déchiffré). */
    function nomFichierSur(nom) {
        const n = String(nom || "document")
            .replace(/[\\/:*?"<>|\u0000-\u001f]/g, "_")
            .replace(/^\.+/, "")
            .slice(0, 150);
        return n || "document";
    }

    function rendreDocuments(docs) {
        const tbody = $("#table-documents tbody");
        tbody.textContent = "";

        if (!docs.length) {
            const tr = el("tr");
            const td = el(
                "td",
                "text-center text-muted py-4",
                "Aucun document.",
            );
            td.colSpan = 7;
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }

        docs.forEach(function (d) {
            const tr = el("tr");

            const tdNom = el("td");
            if (d.erreur) {
                tdNom.appendChild(
                    el("span", "fst-italic text-muted", d.erreur),
                );
            } else {
                tdNom.appendChild(el("i", "fas fa-file-lines me-2 text-info"));
                tdNom.appendChild(el("strong", "", d.nom));
            }
            tr.appendChild(tdNom);

            tr.appendChild(el("td", "", formatTaille(d.taille)));

            const tdNiv = el("td");
            tdNiv.appendChild(
                el(
                    "span",
                    "sed-niveau n" + d.niveau,
                    NIVEAUX[d.niveau] || "Niveau " + d.niveau,
                ),
            );
            tr.appendChild(tdNiv);

            tr.appendChild(
                el("td", "", d.expediteur ? d.expediteur.nom : "Inconnu"),
            );
            tr.appendChild(
                el(
                    "td",
                    "small",
                    d.date ? new Date(d.date).toLocaleString("fr-FR") : "",
                ),
            );

            const tdSig = el("td");
            const sig = el("i");
            if (d.signatureValide === true) {
                sig.className = "fas fa-shield-halved text-success";
                sig.title = "Signature vérifiée";
            } else if (d.signatureValide === false) {
                sig.className = "fas fa-triangle-exclamation text-danger";
                sig.title = "Signature INVALIDE : document possiblement altéré";
            } else {
                sig.className = "fas fa-circle-question text-muted";
                sig.title = "Signature non vérifiable";
            }
            tdSig.appendChild(sig);
            tr.appendChild(tdSig);

            const tdAct = el("td", "text-end");
            const btn = el("button", "btn btn-sm btn-primary");
            btn.type = "button";
            btn.appendChild(el("i", "fas fa-download me-1"));
            btn.appendChild(document.createTextNode("Télécharger"));
            btn.disabled = !!d.erreur || d.signatureValide === false;
            btn.addEventListener("click", function (ev) {
                agir(ev.currentTarget, async function () {
                    const r = await E.documents.ouvrir(d);
                    sauverBlob(
                        nomFichierSur(r.nom),
                        new Blob([r.octets], {
                            type: "application/octet-stream",
                        }),
                    );
                    toast(
                        "Document vérifié, déchiffré et téléchargé.",
                        "success",
                    );
                });
            });
            tdAct.appendChild(btn);
            tr.appendChild(tdAct);

            tbody.appendChild(tr);
        });
    }

    async function chargerDocuments() {
        if (!E.trousseau.estOuvert()) return;
        try {
            rendreDocuments(await E.documents.lister());
        } catch (e) {
            toast(
                "Impossible de charger les documents : " + e.message,
                "error",
            );
        }
    }

    function remplirChoixDocuments(annuaire) {
        const zone = $("#doc-agents");
        zone.textContent = "";

        Array.from(annuaire.values())
            .filter((a) => a.id !== APP.agent.id)
            .forEach(function (a) {
                const item = el("div", "form-check dc-item");
                item.dataset.recherche = (
                    a.nom +
                    " " +
                    a.organisme +
                    " " +
                    a.etat
                ).toLowerCase();

                const cb = el("input", "form-check-input");
                cb.type = "checkbox";
                cb.value = a.id;
                cb.id = "dc-" + a.id;
                cb.disabled = !a.cle_chiffrement;

                const lab = el(
                    "label",
                    "form-check-label",
                    a.nom +
                        " — " +
                        a.organisme +
                        " (" +
                        a.etat +
                        ")" +
                        (a.cle_chiffrement ? "" : " · pas encore de clé"),
                );
                lab.htmlFor = cb.id;

                item.append(cb, lab);
                zone.appendChild(item);
            });

        if (!zone.children.length)
            zone.appendChild(
                el(
                    "p",
                    "text-muted mb-0",
                    "Aucun autre agent dans l’annuaire.",
                ),
            );
    }

    async function ouvrirDocuments() {
        if (!vueDocumentsPrete() || !E.trousseau.estOuvert()) return;
        try {
            remplirChoixDocuments(await E.confiance.charger(true)); // à jour : un collègue a pu publier sa clé
            majAlerteNav();
        } catch (e) {
            toast("Impossible de charger l'annuaire : " + e.message, "error");
        }
        await chargerDocuments();
    }

    function brancherDocuments() {
        if (!vueDocumentsPrete()) {
            const msg =
                "La vue des documents est obsolète : remplacez resources/views/echange/partials/documents.blade.php par la nouvelle version.";
            console.warn(msg);
            if (window.sedErreur) window.sedErreur(msg);
            return;
        }

        $("#doc-max").textContent =
            Math.round(E.documents.tailleMax / 1048576) + " Mo";

        $("#doc-recherche").addEventListener("input", function (ev) {
            const q = ev.target.value.trim().toLowerCase();
            $$(".dc-item").forEach((i) =>
                i.classList.toggle(
                    "d-none",
                    q && i.dataset.recherche.indexOf(q) === -1,
                ),
            );
        });

        $("#btn-actualiser-docs").addEventListener("click", function (ev) {
            agir(ev.currentTarget, ouvrirDocuments);
        });

        $("#btn-deposer").addEventListener("click", function (ev) {
            agir(ev.currentTarget, async function () {
                const fichier = $("#doc-fichier").files[0];
                if (!fichier) throw new Error("Choisissez un fichier.");
                const ids = $$("#doc-agents input:checked").map((i) => i.value);
                if (!ids.length)
                    throw new Error("Sélectionnez au moins un destinataire.");

                $("#doc-etat").textContent = "Chiffrement et envoi en cours…";
                await new Promise((r) => setTimeout(r, 30)); // laisse le message s'afficher avant le calcul
                try {
                    await E.documents.deposer(
                        fichier,
                        parseInt($("#doc-niveau").value, 10),
                        ids,
                    );
                } finally {
                    $("#doc-etat").textContent = "";
                }

                $("#doc-fichier").value = "";
                $$("#doc-agents input:checked").forEach((i) => {
                    i.checked = false;
                });
                toast("Document chiffré et envoyé.", "success");
                await chargerDocuments();
            });
        });
    }

    /* ------------------------------------------------------------------ */
    /*  DONNÉES STRUCTURÉES                                                */
    /* ------------------------------------------------------------------ */
    const STATUTS = {
        nouveau: "Nouveau",
        lu: "Lu",
        traite: "Traité",
        rejete: "Rejeté",
        emis: "Envoyé",
    };
    const PRIO = { normale: "Normale", haute: "Haute", urgente: "Urgente" };

    let donnees = [];
    let donneeCourante = null; // identifiant de l'échange affiché dans le détail
    let sensDon = "recu"; // 'recu' ou 'envoye'
    let empreinteDon = "";
    let photoChoisie = null; // fichier photo choisi dans le formulaire
    let apercuUrl = null; // adresse temporaire de l'aperçu
    let photoAffichee = null; // { id, url, octets } : photo déchiffrée du détail

    function vueDonneesPrete() {
        return !!(
            $("#table-donnees") &&
            $("#don-type") &&
            $("#btn-envoyer-don") &&
            $("#don-detail-champs")
        );
    }

    function schemaDe(type) {
        return (
            E.donnees.schemas[type] || {
                libelle: type,
                icone: "fa-file",
                champs: [],
            }
        );
    }

    /** Date lisible pour un champ date ou date-heure ; texte inchangé sinon. */
    function formatChamp(champ, valeur) {
        if (champ.type === "datetime-local" && !isNaN(new Date(valeur)))
            return new Date(valeur).toLocaleString("fr-FR");
        if (champ.type === "date" && !isNaN(new Date(valeur)))
            return new Date(valeur + "T00:00:00").toLocaleDateString("fr-FR");
        return valeur;
    }

    function badgeStatut(statut) {
        return el("span", "sed-statut " + statut, STATUTS[statut] || statut);
    }

    function partieConcernee(d) {
        if (d.sens === "recu")
            return d.expediteur ? d.expediteur.nom : "Inconnu";
        const noms = (d.destinataires || []).map((x) => x.agent.nom);
        if (!noms.length) return "—";
        return (
            noms.slice(0, 2).join(", ") +
            (noms.length > 2 ? " +" + (noms.length - 2) : "")
        );
    }

    /** Affiche (ou retire) la pastille rouge du menu Données. */
    function appliquerBadgeDonnees(n) {
        const lien = $('#echange-nav a[href="#/echanges"]');
        if (!lien) return;
        let badge = $(".sed-badge-don", lien);
        if (n && !badge) {
            lien.appendChild(
                el("span", "badge bg-danger ms-1 sed-badge-don", String(n)),
            );
        } else if (badge) {
            if (n) badge.textContent = String(n);
            else badge.remove();
        }
    }

    /** Échanges reçus pas encore ouverts : fonctionne même trousseau fermé (le statut n'est pas chiffré). */
    async function majBadgeDonnees() {
        const brut = await E.api.donnees();
        appliquerBadgeDonnees(
            brut.filter((d) => d.sens === "recu" && d.statut === "nouveau")
                .length,
        );
    }

    function rendreListeDonnees() {
        const tbody = $("#table-donnees tbody");
        tbody.textContent = "";

        const nonLus = donnees.filter(
            (d) => d.sens === "recu" && d.statut === "nouveau",
        ).length;
        const pastille = $("#don-nb-recus");
        pastille.textContent = String(nonLus);
        pastille.classList.toggle("d-none", !nonLus);
        appliquerBadgeDonnees(nonLus); // le menu reflète toujours la liste affichée

        $("#don-col-partie").textContent = sensDon === "recu" ? "De" : "À";
        $("#don-sens-recus").classList.toggle("active", sensDon === "recu");
        $("#don-sens-envoyes").classList.toggle("active", sensDon === "envoye");

        const fType = $("#don-filtre-type").value;
        const fStatut = $("#don-filtre-statut").value;
        const lignes = donnees.filter(
            (d) =>
                d.sens === sensDon &&
                (!fType || d.type === fType) &&
                (!fStatut || d.statut === fStatut),
        );

        if (!lignes.length) {
            const tr = el("tr");
            const td = el(
                "td",
                "text-center text-muted py-4",
                "Aucun échange.",
            );
            td.colSpan = 7;
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }

        lignes.forEach(function (d) {
            const tr = el(
                "tr",
                (d.id === donneeCourante ? "active " : "") +
                    (d.sens === "recu" && d.statut === "nouveau"
                        ? "sed-non-lu"
                        : ""),
            );
            const sc = schemaDe(d.type);

            const tdType = el("td", "text-nowrap");
            tdType.appendChild(el("i", "fas " + sc.icone + " me-2 text-info"));
            tdType.appendChild(document.createTextNode(sc.libelle));
            tr.appendChild(tdType);

            const tdObjet = el("td", "", d.erreur ? "(illisible)" : d.objet);
            if (d.a_photo) {
                const cam = el("i", "fas fa-camera ms-2 text-info");
                cam.title = "Photo jointe";
                tdObjet.appendChild(cam);
            }
            tr.appendChild(tdObjet);

            const tdPrio = el("td");
            tdPrio.appendChild(
                el(
                    "span",
                    "sed-prio " + d.priorite,
                    PRIO[d.priorite] || d.priorite,
                ),
            );
            tr.appendChild(tdPrio);

            const tdNiv = el("td");
            tdNiv.appendChild(
                el(
                    "span",
                    "sed-niveau n" + d.niveau,
                    NIVEAUX[d.niveau] || "Niveau " + d.niveau,
                ),
            );
            tr.appendChild(tdNiv);

            tr.appendChild(el("td", "", partieConcernee(d)));
            tr.appendChild(
                el(
                    "td",
                    "small text-nowrap",
                    d.date ? new Date(d.date).toLocaleString("fr-FR") : "",
                ),
            );

            const tdSt = el("td");
            tdSt.appendChild(badgeStatut(d.statut));
            if (d.signatureValide === false) {
                const alerte = el(
                    "i",
                    "fas fa-triangle-exclamation text-danger ms-2",
                );
                alerte.title =
                    "Signature INVALIDE : échange possiblement altéré";
                tdSt.appendChild(alerte);
            }
            tr.appendChild(tdSt);

            tr.addEventListener("click", function () {
                afficherDonnee(d.id);
            });
            tbody.appendChild(tr);
        });
    }

    function photoPrete() {
        return !!(
            $("#don-zone-photo") &&
            $("#don-photo") &&
            $("#don-detail-photo-zone") &&
            $("#don-detail-photo")
        );
    }

    /** Efface la photo déchiffrée de l'écran et de la mémoire. */
    function libererPhoto() {
        if (photoAffichee) URL.revokeObjectURL(photoAffichee.url);
        photoAffichee = null;
        if (!photoPrete()) return;
        const img = $("#don-detail-photo");
        img.removeAttribute("src");
        img.classList.add("d-none");
        $("#btn-photo-telecharger").classList.add("d-none");
        $("#don-detail-photo-etat").textContent = "";
        $("#don-detail-photo-zone").classList.add("d-none");
    }

    /** Charge, vérifie et affiche la photo d'un échange (une seule fois par échange affiché). */
    async function afficherPhotoDonnee(d) {
        if (!photoPrete()) return;
        const zone = $("#don-detail-photo-zone");
        const etat = $("#don-detail-photo-etat");
        zone.dataset.type = d.type; // le cadre est en portrait pour une personne, en paysage pour un véhicule

        if (d.erreur || (!d.a_photo && !d.photo_manquante)) {
            libererPhoto();
            return;
        }
        if (!d.a_photo && d.photo_manquante) {
            libererPhoto();
            zone.classList.remove("d-none");
            etat.textContent =
                "La photo jointe par l'expéditeur n'est plus disponible sur le serveur.";
            return;
        }
        if (photoAffichee && photoAffichee.id === d.id) return;

        libererPhoto();
        zone.classList.remove("d-none");
        etat.textContent = "Chargement de la photo…";
        try {
            const r = await E.donnees.ouvrirPhoto(d);
            if (donneeCourante !== d.id) return; // une autre sélection a eu lieu entre-temps
            const url = URL.createObjectURL(
                new Blob([r.octets], { type: "image/jpeg" }),
            );
            photoAffichee = { id: d.id, url: url, octets: r.octets };
            const img = $("#don-detail-photo");
            img.src = url;
            img.classList.remove("d-none");
            $("#btn-photo-telecharger").classList.remove("d-none");
            etat.textContent = "";
        } catch (e) {
            if (donneeCourante === d.id)
                etat.textContent = "Photo indisponible : " + e.message;
        }
    }

    /* ---- Photo du formulaire ---- */
    function retirerPhotoDon() {
        photoChoisie = null;
        if (apercuUrl) URL.revokeObjectURL(apercuUrl);
        apercuUrl = null;
        if (!photoPrete()) return;
        $("#don-photo").value = "";
        $("#don-photo-img").removeAttribute("src");
        $("#don-photo-apercu").classList.add("d-none");
    }

    function choisirPhotoDon() {
        const fichier = $("#don-photo").files[0];
        retirerPhotoDon();
        if (!fichier) return;

        if (
            ["image/jpeg", "image/png", "image/webp"].indexOf(fichier.type) ===
            -1
        ) {
            $("#don-photo").value = "";
            toast(
                "Format de photo non pris en charge (JPEG, PNG ou WebP).",
                "error",
            );
            return;
        }
        if (fichier.size > 15 * 1024 * 1024) {
            $("#don-photo").value = "";
            toast("Photo trop volumineuse (15 Mo maximum).", "error");
            return;
        }
        photoChoisie = fichier;
        apercuUrl = URL.createObjectURL(fichier);
        $("#don-photo-img").src = apercuUrl;
        $("#don-photo-apercu").classList.remove("d-none");
    }

    function viderDetailDonnee() {
        libererPhoto();
        $("#don-detail-vide").classList.remove("d-none");
        $("#don-detail-contenu").classList.add("d-none");
        $("#don-detail-champs").textContent = "";
        $("#don-detail-actions").textContent = "";
        $("#don-detail-suivi tbody").textContent = "";
    }

    function rendreDetailDonnee(d) {
        $("#don-detail-vide").classList.add("d-none");
        $("#don-detail-contenu").classList.remove("d-none");

        const sc = schemaDe(d.type);
        const badges = $("#don-detail-badges");
        badges.textContent = "";
        const typeBadge = el("span", "badge bg-info text-dark");
        typeBadge.appendChild(el("i", "fas " + sc.icone + " me-1"));
        typeBadge.appendChild(document.createTextNode(sc.libelle));
        badges.appendChild(typeBadge);
        badges.appendChild(
            el(
                "span",
                "sed-prio " + d.priorite,
                PRIO[d.priorite] || d.priorite,
            ),
        );
        badges.appendChild(
            el(
                "span",
                "sed-niveau n" + d.niveau,
                NIVEAUX[d.niveau] || "Niveau " + d.niveau,
            ),
        );
        badges.appendChild(badgeStatut(d.statut));

        $("#don-detail-objet").textContent = d.erreur
            ? "Échange illisible"
            : d.objet;

        const meta = $("#don-detail-meta");
        meta.textContent = "";
        meta.appendChild(
            document.createTextNode(
                (d.sens === "recu" ? "De : " : "Envoyé par vous · À : ") +
                    partieConcernee(d) +
                    (d.date
                        ? " · " + new Date(d.date).toLocaleString("fr-FR")
                        : ""),
            ),
        );
        if (d.signatureValide === false) {
            meta.appendChild(
                el(
                    "div",
                    "text-danger fw-bold",
                    "Signature invalide : ne pas se fier à ce contenu.",
                ),
            );
            if (d.photo_manquante) {
                // Cause la plus fréquente : échange envoyé via un serveur qui n'enregistrait pas encore les photos
                meta.appendChild(
                    el(
                        "div",
                        "small text-warning",
                        "Cause probable : la photo jointe n'a pas été enregistrée par le serveur au moment de l'envoi. " +
                            "Demandez à l'expéditeur de renvoyer l'échange.",
                    ),
                );
            }
        } else if (d.signatureValide === true) {
            const ic = el("i", "fas fa-shield-halved text-success ms-2");
            ic.title = "Signature vérifiée";
            meta.appendChild(ic);
        }

        // Champs du formulaire, dans l'ordre du schéma (le contenu déchiffré ne choisit pas ce qui s'affiche)
        const dl = $("#don-detail-champs");
        dl.textContent = "";
        if (d.erreur) {
            dl.appendChild(el("dd", "fst-italic text-muted", d.erreur));
        } else {
            sc.champs.forEach(function (c) {
                if (c.cle === "objet" || !d.champs[c.cle]) return;
                dl.appendChild(el("dt", "", c.libelle));
                dl.appendChild(
                    el("dd", "", formatChamp(c, String(d.champs[c.cle]))),
                );
            });
        }

        afficherPhotoDonnee(d);

        // Actions du destinataire
        const actions = $("#don-detail-actions");
        actions.textContent = "";
        if (
            d.sens === "recu" &&
            !d.erreur &&
            (d.statut === "nouveau" || d.statut === "lu")
        ) {
            [
                ["traite", "Marquer comme traité", "btn-success", "fa-check"],
                ["rejete", "Rejeter", "btn-outline-danger", "fa-ban"],
            ].forEach(function (a) {
                const b = el("button", "btn btn-sm " + a[2]);
                b.type = "button";
                b.appendChild(el("i", "fas " + a[3] + " me-1"));
                b.appendChild(document.createTextNode(a[1]));
                b.addEventListener("click", function (ev) {
                    if (
                        a[0] === "rejete" &&
                        !confirm(
                            "Rejeter cet échange ? L'expéditeur en sera informé.",
                        )
                    )
                        return;
                    agir(ev.currentTarget, async function () {
                        await E.donnees.statut(d.id, a[0]);
                        toast(
                            a[0] === "traite"
                                ? "Échange marqué comme traité."
                                : "Échange rejeté.",
                            "success",
                        );
                        await chargerDonnees(true);
                    });
                });
                actions.appendChild(b);
            });
        }

        // Suivi (expéditeur uniquement)
        const zone = $("#don-detail-suivi-zone");
        const corps = $("#don-detail-suivi tbody");
        corps.textContent = "";
        zone.classList.toggle(
            "d-none",
            d.sens !== "envoye" || !(d.destinataires || []).length,
        );
        (d.destinataires || []).forEach(function (x) {
            const tr = el("tr");
            tr.appendChild(el("td", "", x.agent.nom));
            const tdS = el("td");
            tdS.appendChild(badgeStatut(x.statut));
            tr.appendChild(tdS);
            tr.appendChild(
                el(
                    "td",
                    "small text-muted",
                    x.traite_le
                        ? new Date(x.traite_le).toLocaleString("fr-FR")
                        : x.lu_le
                          ? "lu le " + new Date(x.lu_le).toLocaleString("fr-FR")
                          : "",
                ),
            );
            corps.appendChild(tr);
        });
    }

    /** Affiche le détail ; un échange reçu « nouveau » passe automatiquement à « lu ». */
    async function afficherDonnee(id) {
        $("#don-formulaire").classList.add("d-none");
        $("#don-detail").classList.remove("d-none");

        const d = donnees.find((x) => x.id === id);
        if (!d) return;
        donneeCourante = id;

        if (d.sens === "recu" && d.statut === "nouveau" && !d.erreur) {
            try {
                await E.donnees.statut(d.id, "lu");
                d.statut = "lu";
                majBadgeDonnees().catch(function () {});
            } catch (e) {
                /* non bloquant : le statut sera réessayé à la prochaine ouverture */
            }
        }
        rendreListeDonnees();
        rendreDetailDonnee(d);
    }

    async function chargerDonnees(forcer) {
        if (!E.trousseau.estOuvert()) return;
        try {
            const liste = await E.donnees.lister();
            const empreinte = liste
                .map(
                    (d) =>
                        d.id +
                        ":" +
                        d.statut +
                        ":" +
                        (d.destinataires || []).map((x) => x.statut).join(""),
                )
                .join("|");
            if (!forcer && empreinte === empreinteDon) return;
            empreinteDon = empreinte;
            donnees = liste;
            rendreListeDonnees();
            const courant = donnees.find((x) => x.id === donneeCourante);
            if (courant && $("#don-formulaire").classList.contains("d-none"))
                rendreDetailDonnee(courant);
            else if (!courant) viderDetailDonnee();
        } catch (e) {
            toast("Impossible de charger les échanges : " + e.message, "error");
        }
    }

    /* ---- Formulaire ---- */
    function rendreChampsDon() {
        const type = $("#don-type").value;
        const zone = $("#don-champs");
        zone.textContent = "";

        schemaDe(type).champs.forEach(function (c) {
            const groupe = el("div", "mb-2");
            const lab = el(
                "label",
                "form-label fw-bold mb-1",
                c.libelle + (c.requis ? " *" : ""),
            );
            lab.htmlFor = "dchamp-" + c.cle;
            groupe.appendChild(lab);

            let champ;
            if (c.type === "textarea") {
                champ = el("textarea", "form-control form-control-sm");
                champ.rows = 3;
                champ.maxLength = 5000;
            } else if (c.type === "select") {
                champ = el("select", "form-select form-select-sm");
                champ.appendChild(el("option", "", "— choisir —")).value = "";
                c.options.forEach((o) => {
                    champ.appendChild(el("option", "", o)).value = o;
                });
            } else {
                champ = el("input", "form-control form-control-sm");
                champ.type = c.type;
                if (c.type === "text") champ.maxLength = 300;
            }
            champ.id = "dchamp-" + c.cle;
            champ.dataset.cle = c.cle;
            groupe.appendChild(champ);
            zone.appendChild(groupe);
        });

        // Photo possible seulement pour certains types
        if (photoPrete()) {
            const schema = schemaDe(type);
            $("#don-zone-photo").classList.toggle("d-none", !schema.photo);
            if (schema.photo)
                $("#don-photo-libelle").textContent =
                    schema.photo.libelle + " (facultatif)";
            retirerPhotoDon();
        }
    }

    function viderFormulaireDon() {
        retirerPhotoDon();
        $$("#don-champs [data-cle]").forEach((c) => {
            c.value = "";
        });
        $$("#don-agents input:checked").forEach((i) => {
            i.checked = false;
        });
    }

    function fermerFormulaireDon() {
        viderFormulaireDon();
        $("#don-formulaire").classList.add("d-none");
        $("#don-detail").classList.remove("d-none");
    }

    async function ouvrirFormulaireDon() {
        $("#don-detail").classList.add("d-none");
        $("#don-formulaire").classList.remove("d-none");
        rendreChampsDon();

        const zone = $("#don-agents");
        zone.textContent = "";
        let annuaire;
        try {
            annuaire = await E.confiance.charger(true); // à jour : un collègue a pu publier sa clé
            majAlerteNav();
        } catch (e) {
            toast("Impossible de charger l'annuaire : " + e.message, "error");
            return;
        }

        Array.from(annuaire.values())
            .filter((a) => a.id !== APP.agent.id)
            .forEach(function (a) {
                const item = el("div", "form-check dn-item");
                item.dataset.recherche = (
                    a.nom +
                    " " +
                    a.organisme +
                    " " +
                    a.etat
                ).toLowerCase();
                const cb = el("input", "form-check-input");
                cb.type = "checkbox";
                cb.value = a.id;
                cb.id = "dn-" + a.id;
                cb.disabled = !a.cle_chiffrement;
                const lab = el(
                    "label",
                    "form-check-label",
                    a.nom +
                        " — " +
                        a.organisme +
                        " (" +
                        a.etat +
                        ")" +
                        (a.cle_chiffrement ? "" : " · pas encore de clé"),
                );
                lab.htmlFor = cb.id;
                item.append(cb, lab);
                zone.appendChild(item);
            });
        if (!zone.children.length)
            zone.appendChild(
                el(
                    "p",
                    "text-muted mb-0",
                    "Aucun autre agent dans l’annuaire.",
                ),
            );
    }

    async function ouvrirDonnees() {
        if (!vueDonneesPrete() || !E.trousseau.estOuvert()) return;
        await chargerDonnees(true);
    }

    function brancherDonnees() {
        if (!vueDonneesPrete()) {
            const msg =
                "La vue des données est obsolète : remplacez resources/views/echange/partials/echanges.blade.php par la nouvelle version.";
            console.warn(msg);
            if (window.sedErreur) window.sedErreur(msg);
            return;
        }

        // Listes de types (formulaire et filtre)
        Object.keys(E.donnees.schemas).forEach(function (t) {
            const lib = E.donnees.schemas[t].libelle;
            $("#don-type").appendChild(el("option", "", lib)).value = t;
            $("#don-filtre-type").appendChild(el("option", "", lib)).value = t;
        });
        $("#don-type").addEventListener("change", rendreChampsDon);

        $("#don-sens-recus").addEventListener("click", function () {
            sensDon = "recu";
            rendreListeDonnees();
        });
        $("#don-sens-envoyes").addEventListener("click", function () {
            sensDon = "envoye";
            rendreListeDonnees();
        });
        $("#don-filtre-type").addEventListener("change", rendreListeDonnees);
        $("#don-filtre-statut").addEventListener("change", rendreListeDonnees);

        $("#btn-actualiser-don").addEventListener("click", function (ev) {
            agir(ev.currentTarget, () => chargerDonnees(true));
        });
        if (photoPrete()) {
            $("#don-photo").addEventListener("change", choisirPhotoDon);
            $("#btn-retirer-photo").addEventListener("click", retirerPhotoDon);
            // Un clic sur la photo l'ouvre en grand dans un nouvel onglet (adresse temporaire, jamais publique)
            $("#don-detail-photo").addEventListener("click", function () {
                if (photoAffichee)
                    window.open(photoAffichee.url, "_blank", "noopener");
            });
            $("#btn-photo-telecharger").addEventListener("click", function () {
                const d = donnees.find((x) => x.id === donneeCourante);
                if (!photoAffichee || !d) return;
                sauverBlob(
                    nomFichierSur(d.objet || "photo") + ".jpg",
                    new Blob([photoAffichee.octets], { type: "image/jpeg" }),
                );
            });
        } else {
            const msg =
                "La vue des données ne gère pas encore les photos : remplacez resources/views/echange/partials/echanges.blade.php par la nouvelle version.";
            console.warn(msg);
            if (window.sedErreur) window.sedErreur(msg);
        }
        $("#btn-nouveau-don").addEventListener("click", ouvrirFormulaireDon);
        $("#btn-annuler-don").addEventListener("click", fermerFormulaireDon);

        $("#don-recherche").addEventListener("input", function (ev) {
            const q = ev.target.value.trim().toLowerCase();
            $$(".dn-item").forEach((i) =>
                i.classList.toggle(
                    "d-none",
                    q && i.dataset.recherche.indexOf(q) === -1,
                ),
            );
        });

        $("#btn-envoyer-don").addEventListener("click", function (ev) {
            agir(ev.currentTarget, async function () {
                const valeurs = {};
                $$("#don-champs [data-cle]").forEach((c) => {
                    valeurs[c.dataset.cle] = c.value;
                });
                const ids = $$("#don-agents input:checked").map((i) => i.value);
                if (!ids.length)
                    throw new Error("Sélectionnez au moins un destinataire.");

                const type = $("#don-type").value;
                await E.donnees.envoyer(
                    type,
                    parseInt($("#don-niveau").value, 10),
                    $("#don-priorite").value,
                    valeurs,
                    ids,
                    schemaDe(type).photo ? photoChoisie : null,
                );

                toast("Échange chiffré et envoyé.", "success");
                fermerFormulaireDon();
                sensDon = "envoye";
                await chargerDonnees(true);
            });
        });
    }

    /* ------------------------------------------------------------------ */
    /*  ADMINISTRATION (agents, organismes, audit)                         */
    /* ------------------------------------------------------------------ */
    const LIBELLES_AUDIT = {
        "page.ouverte": "Ouverture de la page",
        "cle.enregistree": "Clé publique enregistrée",
        "conversation.creee": "Conversation créée",
        "message.envoye": "Message envoyé",
        "document.depose": "Document déposé",
        "document.telecharge": "Document téléchargé",
        "donnees.envoyees": "Données envoyées",
        "donnees.lu": "Données lues",
        "donnees.traite": "Données traitées",
        "donnees.rejete": "Données rejetées",
        "admin.agent.cree": "Agent créé",
        "admin.agent.modifie": "Agent modifié",
        "admin.cle.revoquee": "Clé révoquée",
        "admin.organisme.cree": "Organisme créé",
        "admin.organisme.modifie": "Organisme modifié",
        "admin.organisme.supprime": "Organisme supprimé",
    };

    let admAgents = [];
    let admEdition = null; // id de l'agent en cours de modification (null = création)
    let admOrgListe = { etats: [], organismes: [] };
    let admOrgEdition = null; // id de l'organisme en cours de modification
    let admOnglet = "agents";

    function vueAdminPrete() {
        return !!(
            $("#adm-onglets") &&
            $("#table-adm-agents") &&
            $("#table-adm-organismes") &&
            $("#table-adm-audit")
        );
    }

    function ligneVide(tbody, colonnes, message) {
        const tr = el("tr");
        const td = el("td", "text-center text-muted py-4", message);
        td.colSpan = colonnes;
        tr.appendChild(td);
        tbody.appendChild(tr);
    }

    function boutonAction(libelle, icone, classe, action) {
        const b = el("button", "btn btn-sm " + classe + " me-1");
        b.type = "button";
        b.appendChild(el("i", "fas " + icone + " me-1"));
        b.appendChild(document.createTextNode(libelle));
        b.addEventListener("click", function (ev) {
            agir(ev.currentTarget, action);
        });
        return b;
    }

    function afficherOngletAdmin(nom) {
        admOnglet = nom;
        $$("#adm-onglets .nav-link").forEach((b) =>
            b.classList.toggle("active", b.dataset.adm === nom),
        );
        ["agents", "organismes", "audit"].forEach((n) =>
            $("#adm-" + n).classList.toggle("d-none", n !== nom),
        );
        if (nom === "agents") chargerAdminAgents();
        if (nom === "organismes") chargerAdminOrganismes();
        if (nom === "audit") chargerAdminAudit();
    }

    function ouvrirAdmin() {
        if (!vueAdminPrete()) return;
        afficherOngletAdmin(admOnglet);
    }

    /* ---- Agents ---- */
    async function chargerAdminAgents() {
        try {
            admAgents = await E.api.adminAgents();
            rendreAdminAgents();
        } catch (e) {
            toast("Impossible de charger les agents : " + e.message, "error");
        }
    }

    function rendreAdminAgents() {
        const tbody = $("#table-adm-agents tbody");
        tbody.textContent = "";
        const q = $("#adm-recherche-agent").value.trim().toLowerCase();
        const lignes = admAgents.filter(
            (a) =>
                !q ||
                (
                    a.nom +
                    " " +
                    (a.email || "") +
                    " " +
                    (a.fonction || "") +
                    " " +
                    a.organisme.sigle
                )
                    .toLowerCase()
                    .indexOf(q) !== -1,
        );

        if (!lignes.length) {
            ligneVide(tbody, 7, "Aucun agent.");
            return;
        }

        lignes.forEach(function (a) {
            const moi = a.id === APP.agent.id;
            const tr = el("tr", a.actif ? "" : "inactif");

            const tdNom = el("td");
            tdNom.appendChild(el("strong", "", a.nom));
            if (moi)
                tdNom.appendChild(
                    el("span", "badge bg-info text-dark ms-2", "moi"),
                );
            tdNom.appendChild(el("div", "small text-muted", a.email || ""));
            if (a.fonction) tdNom.appendChild(el("div", "small", a.fonction));
            tr.appendChild(tdNom);

            tr.appendChild(
                el("td", "", a.organisme.sigle + " (" + a.organisme.etat + ")"),
            );

            const tdHab = el("td");
            tdHab.appendChild(
                el(
                    "span",
                    "sed-niveau n" + a.habilitation,
                    a.habilitation + " · " + (NIVEAUX[a.habilitation] || ""),
                ),
            );
            tr.appendChild(tdHab);

            const tdDroits = el("td");
            if (a.est_admin)
                tdDroits.appendChild(el("span", "sed-droit", "Admin"));
            else tdDroits.appendChild(document.createTextNode("—"));
            tr.appendChild(tdDroits);

            const tdSt = el("td");
            tdSt.appendChild(
                el(
                    "span",
                    "badge " + (a.actif ? "bg-success" : "bg-danger"),
                    a.actif ? "Actif" : "Désactivé",
                ),
            );
            tr.appendChild(tdSt);

            const tdCle = el("td");
            const ic = el(
                "i",
                a.a_cle
                    ? "fas fa-key text-success"
                    : "fas fa-circle-xmark text-muted",
            );
            ic.title = a.a_cle
                ? "Clé publiée" +
                  (a.cle_depuis
                      ? " le " +
                        new Date(a.cle_depuis).toLocaleDateString("fr-FR")
                      : "")
                : "Pas encore de clé (trousseau non créé ou clé révoquée)";
            tdCle.appendChild(ic);
            tr.appendChild(tdCle);

            const tdAct = el("td", "text-nowrap text-end");
            tdAct.appendChild(
                boutonAction(
                    "Modifier",
                    "fa-pen",
                    "btn-outline-info",
                    async function () {
                        await ouvrirFormAgent(a);
                    },
                ),
            );

            const bTog = boutonAction(
                a.actif ? "Désactiver" : "Activer",
                a.actif ? "fa-user-slash" : "fa-user-check",
                a.actif ? "btn-outline-warning" : "btn-outline-success",
                async function () {
                    if (
                        a.actif &&
                        !confirm(
                            "Désactiver " +
                                a.nom +
                                " ? Il ne pourra plus se connecter au module d'échanges.",
                        )
                    )
                        return;
                    await E.api.adminModifierAgent(a.id, { actif: !a.actif });
                    toast(
                        a.actif ? "Agent désactivé." : "Agent activé.",
                        "success",
                    );
                    await chargerAdminAgents();
                },
            );
            if (moi) {
                bTog.disabled = true;
                bTog.title =
                    "Vous ne pouvez pas désactiver votre propre compte";
            }
            tdAct.appendChild(bTog);

            if (a.a_cle) {
                tdAct.appendChild(
                    boutonAction(
                        "Révoquer la clé",
                        "fa-ban",
                        "btn-outline-danger",
                        async function () {
                            if (
                                !confirm(
                                    "Révoquer la clé de " +
                                        a.nom +
                                        " ?\n\nPlus personne ne pourra lui écrire tant qu'il n'aura pas créé un nouveau trousseau. " +
                                        "Ses anciens messages resteront lisibles seulement avec l'ancien trousseau.",
                                )
                            )
                                return;
                            await E.api.adminRevoquerCle(a.id);
                            toast("Clé révoquée.", "success");
                            await chargerAdminAgents();
                        },
                    ),
                );
            }
            tr.appendChild(tdAct);
            tbody.appendChild(tr);
        });
    }

    async function ouvrirFormAgent(agent) {
        admEdition = agent ? agent.id : null;
        $("#adm-form-agent").classList.remove("d-none");
        $("#adm-form-agent-titre").textContent = agent
            ? "Modifier " + agent.nom
            : "Nouvel agent";
        $("#adm-groupe-user").classList.toggle("d-none", !!agent);

        try {
            admOrgListe = await E.api.adminOrganismes();

            const selOrg = $("#adm-organisme");
            selOrg.textContent = "";
            selOrg.appendChild(el("option", "", "— choisir —")).value = "";
            admOrgListe.organismes.forEach((o) => {
                selOrg.appendChild(
                    el("option", "", o.sigle + " (" + o.etat + ")"),
                ).value = o.id;
            });

            if (!agent) {
                const users = await E.api.adminUtilisateurs();
                const selUser = $("#adm-user");
                selUser.textContent = "";
                if (!users.length) {
                    const o = el(
                        "option",
                        "",
                        "Aucun compte disponible (tous sont déjà agents)",
                    );
                    o.value = "";
                    selUser.appendChild(o);
                } else {
                    selUser.appendChild(
                        el("option", "", "— choisir un compte —"),
                    ).value = "";
                    users.forEach((u) => {
                        selUser.appendChild(
                            el("option", "", u.nom + " — " + u.email),
                        ).value = u.id;
                    });
                }
            }
        } catch (e) {
            toast(
                "Impossible de préparer le formulaire : " + e.message,
                "error",
            );
            return;
        }

        $("#adm-organisme").value = agent ? agent.organisme.id : "";
        $("#adm-habilitation").value = String(agent ? agent.habilitation : 2);
        $("#adm-fonction").value =
            agent && agent.fonction ? agent.fonction : "";
        $("#adm-est-admin").checked = !!(agent && agent.est_admin);
        if (!agent) $("#adm-user").value = "";
    }

    function fermerFormAgent() {
        admEdition = null;
        $("#adm-form-agent").classList.add("d-none");
    }

    /* ---- Organismes ---- */
    async function chargerAdminOrganismes() {
        try {
            admOrgListe = await E.api.adminOrganismes();
            rendreAdminOrganismes();
        } catch (e) {
            toast(
                "Impossible de charger les organismes : " + e.message,
                "error",
            );
        }
    }

    function rendreAdminOrganismes() {
        const tbody = $("#table-adm-organismes tbody");
        tbody.textContent = "";
        if (!admOrgListe.organismes.length) {
            ligneVide(tbody, 5, "Aucun organisme.");
            return;
        }

        admOrgListe.organismes.forEach(function (o) {
            const tr = el("tr");
            tr.appendChild(el("td", "", o.etat));
            tr.appendChild(el("td", "", o.sigle));
            tr.appendChild(el("td", "", o.nom));
            tr.appendChild(el("td", "", String(o.nb_agents)));

            const tdAct = el("td", "text-nowrap text-end");
            tdAct.appendChild(
                boutonAction(
                    "Modifier",
                    "fa-pen",
                    "btn-outline-info",
                    async function () {
                        ouvrirFormOrganisme(o);
                    },
                ),
            );
            const bSup = boutonAction(
                "Supprimer",
                "fa-trash",
                "btn-outline-danger",
                async function () {
                    if (!confirm("Supprimer l'organisme " + o.sigle + " ?"))
                        return;
                    await E.api.adminSupprimerOrganisme(o.id);
                    toast("Organisme supprimé.", "success");
                    await chargerAdminOrganismes();
                },
            );
            if (o.nb_agents > 0) {
                bSup.disabled = true;
                bSup.title = "Des agents sont rattachés : déplacez-les d'abord";
            }
            tdAct.appendChild(bSup);
            tr.appendChild(tdAct);
            tbody.appendChild(tr);
        });
    }

    function ouvrirFormOrganisme(org) {
        admOrgEdition = org ? org.id : null;
        $("#adm-form-org").classList.remove("d-none");
        $("#adm-form-org-titre").textContent = org
            ? "Modifier " + org.sigle
            : "Nouvel organisme";

        const sel = $("#adm-org-etat");
        sel.textContent = "";
        admOrgListe.etats.forEach((e) => {
            sel.appendChild(el("option", "", e.code + " — " + e.nom)).value =
                e.id;
        });
        sel.value = org
            ? org.etat_id
            : admOrgListe.etats[0]
              ? admOrgListe.etats[0].id
              : "";
        sel.disabled = !!org; // un organisme ne change pas de pays
        $("#adm-org-sigle").value = org ? org.sigle : "";
        $("#adm-org-nom").value = org ? org.nom : "";
    }

    function fermerFormOrganisme() {
        admOrgEdition = null;
        $("#adm-form-org").classList.add("d-none");
    }

    /* ---- Audit ---- */
    async function chargerAdminAudit() {
        const tbody = $("#table-adm-audit tbody");
        try {
            const entrees = await E.api.adminAudit(
                $("#adm-filtre-audit").value.trim(),
            );
            tbody.textContent = "";
            if (!entrees.length) {
                ligneVide(tbody, 5, "Aucune entrée.");
                return;
            }

            entrees.forEach(function (j) {
                const tr = el("tr");
                tr.appendChild(
                    el(
                        "td",
                        "small text-nowrap",
                        new Date(j.created_at).toLocaleString("fr-FR"),
                    ),
                );
                tr.appendChild(el("td", "", j.agent ? j.agent.nom : "—"));

                const tdAction = el("td");
                tdAction.appendChild(
                    document.createTextNode(
                        LIBELLES_AUDIT[j.action] || j.action,
                    ),
                );
                tdAction.appendChild(el("div", "small text-muted", j.action));
                tr.appendChild(tdAction);

                tr.appendChild(
                    el(
                        "td",
                        "small",
                        j.cible_type
                            ? j.cible_type +
                                  (j.cible_id
                                      ? " · " + String(j.cible_id).slice(0, 8)
                                      : "")
                            : "—",
                    ),
                );
                tr.appendChild(el("td", "small", j.ip || "—"));
                tbody.appendChild(tr);
            });
        } catch (e) {
            toast(
                "Impossible de charger le journal d'audit : " + e.message,
                "error",
            );
        }
    }

    function brancherAdmin() {
        if (!vueAdminPrete()) {
            // Page d'un administrateur avec l'ancienne vue provisoire : on le signale
            if (APP.admin && $("#vue-admin")) {
                const msg =
                    "La vue d'administration est obsolète : remplacez resources/views/echange/partials/admin.blade.php par la nouvelle version.";
                console.warn(msg);
                if (window.sedErreur) window.sedErreur(msg);
            }
            return;
        }

        $$("#adm-onglets .nav-link").forEach((b) =>
            b.addEventListener("click", function () {
                afficherOngletAdmin(b.dataset.adm);
            }),
        );

        // Agents
        $("#btn-adm-nouvel-agent").addEventListener("click", function (ev) {
            agir(ev.currentTarget, () => ouvrirFormAgent(null));
        });
        $("#btn-adm-annuler-agent").addEventListener("click", fermerFormAgent);
        $("#btn-adm-actualiser-agents").addEventListener(
            "click",
            function (ev) {
                agir(ev.currentTarget, chargerAdminAgents);
            },
        );
        $("#adm-recherche-agent").addEventListener("input", rendreAdminAgents);
        $("#btn-adm-enregistrer-agent").addEventListener(
            "click",
            function (ev) {
                agir(ev.currentTarget, async function () {
                    const donnees = {
                        organisme_id: $("#adm-organisme").value,
                        habilitation: parseInt(
                            $("#adm-habilitation").value,
                            10,
                        ),
                        fonction: $("#adm-fonction").value.trim() || null,
                        est_admin: $("#adm-est-admin").checked,
                    };
                    if (!donnees.organisme_id)
                        throw new Error("Choisissez un organisme.");

                    if (admEdition) {
                        await E.api.adminModifierAgent(admEdition, donnees);
                        toast("Agent modifié.", "success");
                    } else {
                        donnees.user_id = $("#adm-user").value;
                        if (!donnees.user_id)
                            throw new Error(
                                "Choisissez un compte utilisateur.",
                            );
                        await E.api.adminCreerAgent(donnees);
                        toast(
                            "Agent créé. Il doit se connecter une fois pour créer son trousseau.",
                            "success",
                        );
                    }
                    fermerFormAgent();
                    await chargerAdminAgents();
                });
            },
        );

        // Organismes
        $("#btn-adm-nouvel-org").addEventListener("click", function () {
            ouvrirFormOrganisme(null);
        });
        $("#btn-adm-annuler-org").addEventListener(
            "click",
            fermerFormOrganisme,
        );
        $("#btn-adm-actualiser-org").addEventListener("click", function (ev) {
            agir(ev.currentTarget, chargerAdminOrganismes);
        });
        $("#btn-adm-enregistrer-org").addEventListener("click", function (ev) {
            agir(ev.currentTarget, async function () {
                const sigle = $("#adm-org-sigle").value.trim();
                const nom = $("#adm-org-nom").value.trim();
                if (!sigle || !nom)
                    throw new Error("Le sigle et le nom sont obligatoires.");

                if (admOrgEdition) {
                    await E.api.adminModifierOrganisme(admOrgEdition, {
                        sigle: sigle,
                        nom: nom,
                    });
                    toast("Organisme modifié.", "success");
                } else {
                    await E.api.adminCreerOrganisme({
                        etat_id: $("#adm-org-etat").value,
                        sigle: sigle,
                        nom: nom,
                    });
                    toast("Organisme créé.", "success");
                }
                fermerFormOrganisme();
                await chargerAdminOrganismes();
            });
        });

        // Audit (filtre avec un léger délai pour ne pas interroger à chaque frappe)
        let delaiAudit = null;
        $("#adm-filtre-audit").addEventListener("input", function () {
            clearTimeout(delaiAudit);
            delaiAudit = setTimeout(chargerAdminAudit, 400);
        });
        $("#btn-adm-actualiser-audit").addEventListener("click", function (ev) {
            agir(ev.currentTarget, chargerAdminAudit);
        });
    }

    /* ------------------------------------------------------------------ */
    /*  Synchronisation périodique (en attendant un canal temps réel)      */
    /* ------------------------------------------------------------------ */
    function demarrerSync() {
        setInterval(function () {
            if (document.hidden || occupe || !courante) return;
            if (vueDemandee() !== "conversations" || !E.trousseau.estOuvert())
                return;
            chargerMessages(false).catch(function () {
                /* silencieux */
            });
        }, INTERVALLE_SYNC_MS);

        // Données : rafraîchissement de la liste ouverte, et pastille des nouveautés (même trousseau fermé)
        setInterval(function () {
            if (document.hidden) return;
            if (
                vueDemandee() === "echanges" &&
                vueDonneesPrete() &&
                E.trousseau.estOuvert()
            ) {
                chargerDonnees(false).catch(function () {});
            }
            majBadgeDonnees().catch(function () {});
        }, 15000);
    }

    /* ------------------------------------------------------------------ */
    /*  Initialisation                                                     */
    /* ------------------------------------------------------------------ */
    async function init() {
        await E.ready;

        brancherTrousseau();
        brancherConversations();
        brancherDocuments();
        brancherDonnees();
        brancherAdmin();

        // Verrouillage / déverrouillage : on n'affiche jamais de clair trousseau fermé
        document.addEventListener("trousseau:etat", function (ev) {
            rendreTrousseau();
            if (!ev.detail.ouvert) {
                dernierIdAffiche = null;
                $("#msg-texte").value = "";
                if (vueDocumentsPrete()) {
                    $("#table-documents tbody").textContent = "";
                    $("#doc-agents").textContent = "";
                }
                if (vueDonneesPrete()) {
                    donnees = [];
                    donneeCourante = null;
                    empreinteDon = "";
                    $("#table-donnees tbody").textContent = "";
                    viderDetailDonnee(); // efface aussi la photo affichée
                    viderFormulaireDon(); // efface aussi l'aperçu de la photo choisie
                    $("#don-champs").textContent = "";
                    $("#don-agents").textContent = "";
                    $("#don-formulaire").classList.add("d-none");
                    $("#don-detail").classList.remove("d-none");
                }
                if (courante)
                    viderMessages(
                        "Trousseau verrouillé : déverrouillez-le pour lire les messages.",
                    );
            } else {
                if (courante)
                    chargerMessages(true).catch(function (e) {
                        toast(e.message, "error");
                    });
                if (vueDemandee() === "documents") ouvrirDocuments();
                if (vueDemandee() === "echanges") ouvrirDonnees();
            }
        });

        rendreTrousseau();
        window.addEventListener("hashchange", afficherVue);
        afficherVue();
        window.__sedAppPret = true; // l'interface est démarrée

        await chargerConversations();
        majBadgeDonnees().catch(function () {});
        demarrerSync();
    }

    function demarrer() {
        init().catch(function (e) {
            console.error(e);
            if (window.sedErreur)
                window.sedErreur("Erreur d'initialisation : " + e.message);
        });
    }

    if (document.readyState === "loading")
        document.addEventListener("DOMContentLoaded", demarrer);
    else demarrer();
})();
