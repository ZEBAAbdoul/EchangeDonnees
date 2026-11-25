/* Interface de validation des échanges vers un autre État.
   Dépend de window.Echange (echange.js). Aucune injection HTML : tout le texte passe par textContent. */
(function () {
    'use strict';

    const DELAI_JOURS = 7;   // même valeur que ValidationEchange::DELAI_JOURS côté serveur

    const LIBELLES = {
        en_attente: { t: 'En attente de validation', c: 'ech-v-attente' },
        validee:    { t: 'Validé : remise des clés en cours', c: 'ech-v-validee' },
        liberee:    { t: 'Validé et transmis', c: 'ech-v-liberee' },
        refusee:    { t: 'Refusé', c: 'ech-v-refusee' },
        expiree:    { t: 'Expiré', c: 'ech-v-refusee' },
        annulee:    { t: 'Annulé', c: 'ech-v-refusee' }
    };

    const DECISIONS = { en_attente: 'en attente', approuve: 'a approuvé', refuse: 'a refusé' };

    const el = (tag, classe, texte) => {
        const n = document.createElement(tag);
        if (classe) n.className = classe;
        if (texte != null) n.textContent = texte;
        return n;
    };
    const alerte = (type, texte) => el('div', 'ech-alerte ech-alerte-' + type, texte);
    const dateFr = (iso) => (iso ? new Date(iso).toLocaleString('fr-FR') : '');
    const noms = (liste) => liste.map((a) => a.nom).join(', ');
    const api = () => window.Echange.donnees;

    /* ------------------------------------------------------------------ Badge pour la liste */
    function badge(d) {
        const l = LIBELLES[d.validation];
        return l ? el('span', 'ech-badge ' + l.c, l.t) : null;
    }

    /** Libellé d'un destinataire d'un autre État qui n'a pas encore la clé. */
    function libelleStatutDestinataire(statut) {
        return statut === 'attente' ? 'En attente de validation' : statut;
    }

    /* ------------------------------------------------------------------ Aperçu avant envoi */
    /**
     * Affiche, avant l'envoi, ce qui va se passer. Retourne le plan (ou null) : bloque l'envoi si plan.erreur.
     * @param {HTMLElement} conteneur  ex. document.getElementById('ech-apercu-envoi')
     * @param {string[]} ids           identifiants des destinataires choisis
     */
    async function apercuEnvoi(conteneur, ids) {
        conteneur.replaceChildren();
        if (!ids || !ids.length) return null;

        let plan;
        try {
            plan = await api().analyserEnvoi(ids, false);
        } catch (e) {
            conteneur.append(alerte('err', e.message));
            return null;
        }

        if (plan.erreur) {
            conteneur.append(alerte('err', plan.erreur));
        } else if (plan.libererDirect) {
            conteneur.append(alerte('info',
                "Vous êtes le seul administrateur de votre État : votre envoi vaut validation. " +
                "Les destinataires de " + noms(plan.externes) + " le recevront immédiatement."));
        } else if (plan.besoinValidation) {
            conteneur.append(alerte('warn',
                "Cet échange est destiné à un autre État (" + noms(plan.externes) + "). Il leur restera illisible " +
                "jusqu'à l'approbation de tous les administrateurs de votre État : " + noms(plan.valideurs) +
                ". Délai : " + DELAI_JOURS + " jours."));
        }
        return plan;
    }

    /* ------------------------------------------------------------------ Détail d'un échange */
    /**
     * @param {object} d             échange issu de Echange.donnees.lister()
     * @param {HTMLElement} racine   ex. document.getElementById('ech-validation-detail')
     * @param {Function} rafraichir  fonction async qui recharge la liste et réaffiche le détail
     */
    function detail(d, racine, rafraichir) {
        racine.replaceChildren();
        if (!d || d.validation === 'non_requise') return;

        const boite = el('div');
        const titre = el('h4', null, 'Validation interétatique ');
        const b = badge(d);
        if (b) titre.append(b);
        if (d.validation === 'en_attente' && d.expire_le) {
            titre.append(el('span', 'ech-discret', 'expire le ' + dateFr(d.expire_le)));
        }
        boite.append(titre);

        // Décisions des administrateurs
        if (d.validations && d.validations.length) {
            const ul = el('ul');
            d.validations.forEach(function (v) {
                const li = el('li');
                const ligne = el('div', 'ech-ligne');
                ligne.append(el('span', null, v.agent.nom + ' ' + (DECISIONS[v.decision] || v.decision)));
                const droite = el('span', 'ech-discret', v.decide_le ? dateFr(v.decide_le) : '');
                if (v.decision !== 'en_attente') {
                    droite.append(' ');
                    droite.append(v.signatureOk === true
                        ? el('span', 'ech-ok', '✓ signature vérifiée')
                        : el('span', 'ech-ko', '✗ signature NON valide'));
                }
                ligne.append(droite);
                li.append(ligne);
                if (v.decision === 'refuse') {
                    li.append(el('div', 'ech-motif', 'Motif : ' + (v.motif || '(illisible)')));
                }
                ul.append(li);
            });
            boite.append(ul);
        }

        // Expéditeur : un valideur exigé n'est plus actif → échange bloqué
        if (d.sens === 'envoye' && d.valideursIndisponibles && d.valideursIndisponibles.length && d.validation === 'en_attente') {
            boite.append(alerte('warn',
                "Un administrateur exigé n'est plus actif : cet échange ne pourra pas être validé. " +
                "Annulez-le puis renvoyez-le."));
        }

        // Destinataire d'un autre État : contrôle des approbations faites dans son navigateur
        if (d.sens === 'recu' && d.verification) {
            boite.append(d.verification.complet
                ? alerte('ok', 'Approbations vérifiées : ' + d.verification.exige + ' administrateur(s) de l\'État émetteur ont signé.')
                : alerte('err', 'Les approbations de l\'État émetteur ne sont pas toutes valides. Ne tenez pas compte de cet échange.'));
        }

        // Messages d'état pour l'expéditeur
        if (d.sens === 'envoye') {
            if (d.validation === 'refusee') boite.append(alerte('err', 'Un administrateur a refusé cet échange : il n\'a pas été transmis.'));
            if (d.validation === 'expiree') boite.append(alerte('err', 'Le délai de validation est dépassé : l\'échange n\'a pas été transmis.'));
            if (d.validation === 'liberee')  boite.append(alerte('ok', 'Échange validé et transmis aux destinataires.'));
        }

        const zoneMsg = el('div');
        boite.append(zoneMsg, actions(d, zoneMsg, rafraichir));
        racine.append(boite);
    }

    function actions(d, zoneMsg, rafraichir) {
        const barre = el('div', 'ech-actions');

        async function agir(boutons, travail) {
            zoneMsg.replaceChildren();
            boutons.forEach((x) => { x.disabled = true; });
            try {
                const message = await travail();
                if (message) zoneMsg.append(message);
                await rafraichir();
            } catch (e) {
                zoneMsg.append(alerte('err', e.message || 'Opération impossible.'));
                boutons.forEach((x) => { x.disabled = false; });
            }
        }

        // 1) Décision de l'administrateur valideur
        if (d.sens === 'a_valider' && d.ma_validation === 'en_attente' && d.validation === 'en_attente') {
            if (d.signatureValide !== true) {
                barre.append(alerte('err', "La signature de l'expéditeur n'a pas pu être vérifiée : validation impossible."));
                return barre;
            }
            const approuver = el('button', 'ech-btn-ok', 'Approuver');
            const refuser = el('button', 'ech-btn-ko', 'Refuser…');
            const zoneRefus = el('div', 'ech-refus');
            zoneRefus.hidden = true;
            const motif = el('textarea');
            motif.placeholder = 'Motif du refus (obligatoire, chiffré : le serveur ne peut pas le lire)';
            motif.maxLength = 1000;
            const confirmer = el('button', 'ech-btn-ko', 'Confirmer le refus');
            zoneRefus.append(motif, confirmer);

            approuver.addEventListener('click', function () {
                if (!window.confirm("Approuver l'envoi de cet échange à un autre État ?")) return;
                agir([approuver, refuser, confirmer], async function () {
                    const r = await api().decider(d, 'approuve');
                    if (r.libere) return alerte('ok', "Dernière approbation : l'échange a été transmis aux destinataires.");
                    if (r.erreurLiberation) {
                        return alerte('warn', "Approbation enregistrée, mais la remise des clés a échoué (" + r.erreurLiberation +
                            "). L'expéditeur ou un administrateur ayant approuvé peut relancer.");
                    }
                    return alerte('info', 'Approbation enregistrée. En attente des autres administrateurs.');
                });
            });
            refuser.addEventListener('click', function () { zoneRefus.hidden = !zoneRefus.hidden; motif.focus(); });
            confirmer.addEventListener('click', function () {
                if (!motif.value.trim()) { zoneMsg.replaceChildren(alerte('err', 'Indiquez le motif du refus.')); return; }
                if (!window.confirm('Refuser définitivement cet échange ?')) return;
                agir([approuver, refuser, confirmer], async function () {
                    await api().decider(d, 'refuse', motif.value);
                    return alerte('info', 'Échange refusé : il ne sera pas transmis.');
                });
            });
            barre.append(approuver, refuser, zoneRefus);
        }

        // 2) Relance de la remise des clés (navigateur fermé trop tôt, réseau coupé…)
        const peutLiberer = d.validation === 'validee' && (d.sens === 'envoye' || d.ma_validation === 'approuve');
        if (peutLiberer) {
            const relancer = el('button', null, 'Relancer la transmission');
            relancer.addEventListener('click', function () {
                agir([relancer], async function () {
                    await api().liberer(d);
                    return alerte('ok', 'Échange transmis aux destinataires.');
                });
            });
            barre.append(relancer);
        }

        // 3) Annulation par l'expéditeur
        if (d.sens === 'envoye' && (d.validation === 'en_attente' || d.validation === 'validee')) {
            const annuler = el('button', null, "Annuler l'envoi");
            annuler.addEventListener('click', function () {
                if (!window.confirm("Annuler cet échange ? Il ne sera jamais transmis à l'autre État.")) return;
                agir([annuler], async function () {
                    await api().annuler(d);
                    return alerte('info', 'Échange annulé.');
                });
            });
            barre.append(annuler);
        }

        return barre;
    }

    /* ------------------------------------------------------------------ Onglet « À valider » */
    function aValider(liste) {
        return liste.filter((d) => d.sens === 'a_valider' && d.ma_validation === 'en_attente' && d.validation === 'en_attente');
    }

    function majCompteur(liste) {
        const n = aValider(liste).length;
        const c = document.getElementById('ech-nb-a-valider');
        if (c) { c.textContent = String(n); c.hidden = n === 0; }
        return n;
    }

    function creerOnglet() {
        const tpl = document.getElementById('ech-tpl-onglet-a-valider');
        return tpl ? tpl.content.firstElementChild.cloneNode(true) : null;
    }

    window.EchangeValidation = {
        badge: badge, apercuEnvoi: apercuEnvoi, detail: detail, aValider: aValider,
        majCompteur: majCompteur, creerOnglet: creerOnglet, libelleStatutDestinataire: libelleStatutDestinataire
    };
})();