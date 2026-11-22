/*
 * app.js : interface (page unique). Les clés privées restent en mémoire : un rechargement
 * de la page verrouille le trousseau. Tout contenu affiché passe par textContent (jamais innerHTML),
 * car le contenu déchiffré vient d'autres agents.
 */
(function () {
  'use strict';

  const E = window.Echange;
  const APP = window.APP;

  /* ------------------------------ Utilitaires DOM ------------------------------ */
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
  const vider = (n) => { while (n.firstChild) n.removeChild(n.firstChild); };
  const quand = (d) => (d ? new Date(d).toLocaleString('fr-FR') : '');

  function el(tag, attrs, ...enfants) {
    const n = document.createElement(tag);
    for (const [k, v] of Object.entries(attrs || {})) {
      if (k === 'class') n.className = v;
      else if (k.startsWith('on')) n.addEventListener(k.slice(2), v);
      else if (v !== false && v != null) n.setAttribute(k, v);
    }
    for (const e of enfants.flat()) if (e != null) n.append(e instanceof Node ? e : document.createTextNode(String(e)));
    return n;
  }

  function signaler(texte, erreur = false) {
    if (window.toastr) {
      if (erreur) window.toastr.error(texte); else window.toastr.success(texte);
    } else {
      alert(texte);
    }
  }

  /** Exécute une action et affiche l'erreur éventuelle au lieu de la laisser planter. */
  async function proteger(fn) {
    try { return await fn(); } catch (e) { signaler(e.message || String(e), true); return undefined; }
  }

  const ligneVide = (tbody, colonnes, texte) =>
    tbody.append(el('tr', {}, el('td', { colspan: colonnes, class: 'vide' }, texte)));

  /* ------------------------------ Annuaire ------------------------------ */
  let annuaire = [];
  const agentParId = (id) => annuaire.find((a) => a.id === id);

  async function chargerAnnuaire() { annuaire = await E.api('GET', APP.api + '/agents'); }

  function rendreChoixAgents(conteneur) {
    vider(conteneur);
    annuaire.filter((a) => a.id !== APP.agent.id).forEach((a) => {
      conteneur.append(el('label', { class: 'choix' },
        el('input', { type: 'checkbox', value: a.id }), ' ',
        a.nom, ' — ', a.fonction, ' (', a.etat.code, ')'));
    });
    if (!conteneur.firstChild) conteneur.append(el('p', { class: 'vide' }, 'Aucun autre agent dans l\'annuaire.'));
  }

  /** Agents cochés + moi-même (pour pouvoir relire mes propres envois). */
  function destinatairesChoisis(conteneur) {
    const moi = agentParId(APP.agent.id);
    if (!moi) throw new Error('Votre fiche est absente de l\'annuaire (compte inactif ?).');
    const autres = $$('input:checked', conteneur).map((i) => agentParId(Number(i.value))).filter(Boolean);
    if (autres.length === 0) throw new Error('Choisissez au moins un destinataire.');
    return [moi, ...autres];
  }

  /* ------------------------------ Navigation ------------------------------ */
  const sections = {};

  function majTrousseau() {
    const ouvert = E.estOuvert();
    const e = $('#etat-trousseau');
    e.textContent = ouvert ? '🔓 Trousseau ouvert' : '🔒 Trousseau verrouillé';
    e.className = 'etat ' + (ouvert ? 'ouvert' : 'ferme');
    $('#btn-verrouiller').classList.toggle('cache', !ouvert);
  }

  async function aller() {
    const nom = (location.hash || '#/conversations').replace('#/', '');
    const cible = sections[nom] ? nom : 'conversations';
    $$('.echange-section').forEach((s) => s.classList.toggle('cache', s.id !== 'section-' + cible));
    $$('#echange-nav a').forEach((a) => a.classList.toggle('active', a.getAttribute('href') === '#/' + cible));
    majTrousseau();
    await proteger(async () => {
      if (cible !== 'trousseau') await chargerAnnuaire();
      await sections[cible]();
    });
  }

  /* ------------------------------ Trousseau ------------------------------ */
  sections.trousseau = async () => { majTrousseau(); };

  function afficherInfos(infos, texteFichier) {
    const r = $('#resultat-trousseau');
    vider(r);
    r.append(
      el('h3', {}, 'À remettre à l\'administrateur de votre État'),
      el('p', {}, 'Vérifiez ensemble les empreintes (en personne ou par un canal sûr) avant tout enregistrement.'),
      el('table', { class: 'table table-sm table-hover' },
        el('tr', {}, el('th', {}, 'Usage'), el('th', {}, 'Clé publique (base64)'), el('th', {}, 'Empreinte SHA-256')),
        el('tr', {}, el('td', {}, 'signature'), el('td', { class: 'mono' }, infos.signature.cle), el('td', { class: 'mono' }, infos.signature.empreinte)),
        el('tr', {}, el('td', {}, 'chiffrement'), el('td', { class: 'mono' }, infos.chiffrement.cle), el('td', { class: 'mono' }, infos.chiffrement.empreinte))));
    if (texteFichier) {
      const url = URL.createObjectURL(new Blob([texteFichier], { type: 'application/json' }));
      r.append(
        el('p', {}, el('a', { class: 'btn btn-primary', href: url, download: 'trousseau-' + APP.agent.id + '.json' }, 'Télécharger mon fichier de trousseau')),
        el('p', { class: 'text-warning font-weight-bold' }, 'Conservez ce fichier en lieu sûr. Sans lui et sans votre phrase secrète, vos messages sont définitivement illisibles.'));
    }
  }

  $('#form-generer').addEventListener('submit', (ev) => {
    ev.preventDefault();
    proteger(async () => {
      const f = ev.target;
      if (f.phrase.value.length < 14) throw new Error('Phrase secrète trop courte (14 caractères minimum).');
      if (f.phrase.value !== f.phrase2.value) throw new Error('Les deux phrases ne correspondent pas.');
      const t = await E.genererTrousseau();
      const texte = await E.exporterTrousseau(t, f.phrase.value);
      E.utiliserTrousseau(t);
      afficherInfos(E.infosPubliques(), texte);
      f.reset();
      majTrousseau();
      signaler('Trousseau généré et ouvert. Téléchargez le fichier maintenant.');
    });
  });

  $('#form-ouvrir').addEventListener('submit', (ev) => {
    ev.preventDefault();
    proteger(async () => {
      const f = ev.target;
      const fichier = f.fichier.files[0];
      if (!fichier) throw new Error('Choisissez votre fichier de trousseau.');
      const infos = await E.ouvrirTrousseau(await fichier.text(), f.phrase.value);
      afficherInfos(infos, null);
      f.reset();
      majTrousseau();
      signaler('Trousseau déverrouillé.');
    });
  });

  $('#btn-verrouiller').addEventListener('click', () => {
    E.verrouiller();
    majTrousseau();
    vider($('#resultat-trousseau'));
    signaler('Trousseau verrouillé.');
  });

  /* ------------------------------ Conversations ------------------------------ */
  let courante = null; // { uuid, participants }

  sections.conversations = async () => {
    rendreChoixAgents($('#choix-conv'));
    const r = await E.api('GET', APP.api + '/conversations');
    const corps = $('#liste-conversations tbody');
    vider(corps);
    if (r.data.length === 0) ligneVide(corps, 4, 'Aucune conversation.');
    r.data.forEach((c) => corps.append(el('tr', {},
      el('td', {}, c.createur ? c.createur.nom : ''),
      el('td', {}, c.classification),
      el('td', {}, quand(c.updated_at)),
      el('td', {}, el('button', { type: 'button', class: 'btn btn-sm btn-primary', onclick: () => proteger(() => ouvrirConversation(c.uuid)) }, 'Ouvrir')))));
    if (courante) await proteger(() => ouvrirConversation(courante.uuid));
  };

  $('#form-conversation').addEventListener('submit', (ev) => {
    ev.preventDefault();
    proteger(async () => {
      const ids = $$('input:checked', $('#choix-conv')).map((i) => Number(i.value));
      if (ids.length === 0) throw new Error('Choisissez au moins un participant.');
      const c = await E.api('POST', APP.api + '/conversations', { classification: ev.target.classification.value, participants: ids });
      signaler('Conversation créée.');
      courante = null;
      await sections.conversations();
      await ouvrirConversation(c.uuid);
    });
  });

  async function chargerMessages(uuid) {
    let page = 1; let derniere = 1; let tous = [];
    do {
      const r = await E.api('GET', APP.api + '/conversations/' + uuid + '/messages?page=' + page);
      tous = tous.concat(r.data); derniere = r.last_page; page++;
    } while (page <= derniere && page <= 20);
    return tous;
  }

  async function rendreMessage(m, uuid) {
    const bloc = el('div', { class: 'message' + (m.expediteur_id === APP.agent.id ? ' moi' : '') },
      el('div', { class: 'entete' }, m.expediteur ? m.expediteur.nom : '?', ' · ', quand(m.envoye_le)));
    const corps = el('div', { class: 'corps' });
    try {
      if (!E.estOuvert()) throw new Error('🔒 Trousseau verrouillé : déverrouillez-le pour lire.');
      const d = m.destinataires && m.destinataires[0];
      if (!d) throw new Error('Aucune clé enveloppée pour vous sur ce message.');
      const clair = await E.ouvrir({
        chiffre: E.deb64(m.contenu_chiffre), nonce: m.nonce, signature: m.signature,
        enveloppe: d.cle_enveloppee, ad: 'msg:' + uuid,
        clesSignature: await E.clesSignatureDe(m.expediteur_id),
      });
      corps.textContent = E.decodeur.decode(clair);
      if (!d.lu_le) E.api('POST', APP.api + '/messages/' + m.id + '/lu').catch(() => {});
    } catch (e) {
      corps.textContent = e.message;
      corps.classList.add('illisible');
    }
    bloc.append(corps);
    return bloc;
  }

  async function ouvrirConversation(uuid) {
    const conv = await E.api('GET', APP.api + '/conversations/' + uuid);
    courante = { uuid, participants: conv.participants };
    $('#fil').classList.remove('cache');
    $('#titre-fil').textContent = 'Conversation ' + conv.classification + ' — ' + conv.participants.map((p) => p.nom).join(', ');
    const zone = $('#messages');
    vider(zone);
    const messages = await chargerMessages(uuid);
    if (messages.length === 0) zone.append(el('p', { class: 'vide' }, 'Aucun message.'));
    for (const m of messages) zone.append(await rendreMessage(m, uuid));
    zone.scrollTop = zone.scrollHeight;
  }

  $('#form-message').addEventListener('submit', (ev) => {
    ev.preventDefault();
    proteger(async () => {
      if (!courante) throw new Error('Aucune conversation ouverte.');
      const texte = ev.target.texte.value.trim();
      if (!texte) return;
      const dests = courante.participants.map((p) => agentParId(p.id));
      if (dests.some((d) => !d)) throw new Error('Un participant n\'est plus dans l\'annuaire (inactif ?).');
      const p = await E.preparer(E.encodeur.encode(texte), 'msg:' + courante.uuid, dests);
      await E.api('POST', APP.api + '/conversations/' + courante.uuid + '/messages', {
        contenu_chiffre: E.b64(p.chiffre), nonce: p.nonce, signature: p.signature, destinataires: p.destinataires,
      });
      ev.target.reset();
      await ouvrirConversation(courante.uuid);
    });
  });

  /* ------------------------------ Documents ------------------------------ */
  sections.documents = async () => {
    rendreChoixAgents($('#choix-doc'));
    const r = await E.api('GET', APP.api + '/documents');
    const corps = $('#liste-documents tbody');
    vider(corps);
    if (r.data.length === 0) ligneVide(corps, 5, 'Aucun document reçu.');
    r.data.forEach((d) => corps.append(el('tr', {},
      el('td', {}, d.expediteur ? d.expediteur.nom : ''),
      el('td', {}, d.classification),
      el('td', {}, Math.ceil(d.taille / 1024) + ' Ko'),
      el('td', {}, quand(d.created_at)),
      el('td', {}, el('button', { type: 'button', class: 'btn btn-sm btn-primary', onclick: () => proteger(() => telechargerDocument(d.uuid)) }, 'Télécharger et déchiffrer')))));
  };

  $('#form-document').addEventListener('submit', (ev) => {
    ev.preventDefault();
    proteger(async () => {
      const f = ev.target;
      const fichier = f.fichier.files[0];
      if (!fichier) throw new Error('Choisissez un fichier.');
      const dests = destinatairesChoisis($('#choix-doc'));

      // Paquet = [longueur du nom (4 octets)] [nom] [contenu] : le nom est donc chiffré avec le fichier.
      const contenu = new Uint8Array(await fichier.arrayBuffer());
      const nom = E.encodeur.encode(fichier.name);
      const entete = new Uint8Array(4);
      new DataView(entete.buffer).setUint32(0, nom.length);
      const p = await E.preparer(E.concat(entete, nom, contenu), 'doc', dests);

      const fd = new FormData();
      fd.append('fichier', new Blob([p.chiffre]), 'document.chiffre');
      fd.append('classification', f.classification.value);
      fd.append('nom_chiffre', 'inclus-dans-le-contenu');
      fd.append('hash_contenu', E.sha256Hex(p.chiffre));
      fd.append('nonce', p.nonce);
      fd.append('signature', p.signature);
      p.destinataires.forEach((d, i) => {
        fd.append('destinataires[' + i + '][agent_id]', d.agent_id);
        fd.append('destinataires[' + i + '][cle_agent_id]', d.cle_agent_id);
        fd.append('destinataires[' + i + '][cle_enveloppee]', d.cle_enveloppee);
      });
      await E.api('POST', APP.api + '/documents', fd);
      f.reset();
      signaler('Document chiffré et envoyé.');
      await sections.documents();
    });
  });

  async function telechargerDocument(uuid) {
    const r = await E.api('GET', APP.api + '/documents/' + uuid);
    const doc = r.document;
    const octets = await E.telecharger(APP.api + '/documents/' + uuid + '/fichier');
    if (E.sha256Hex(octets) !== doc.hash_contenu) throw new Error('Intégrité compromise : le hash du fichier ne correspond pas.');

    const paquet = await E.ouvrir({
      chiffre: octets, nonce: doc.nonce, signature: doc.signature,
      enveloppe: r.cle_enveloppee, ad: 'doc',
      clesSignature: await E.clesSignatureDe(doc.expediteur_id),
    });
    const longueur = new DataView(paquet.buffer, paquet.byteOffset, 4).getUint32(0);
    if (longueur > paquet.length - 4) throw new Error('Format de document invalide.');
    const nom = E.decodeur.decode(paquet.subarray(4, 4 + longueur)).replace(/[\\/:*?"<>|]/g, '_') || 'document';
    const url = URL.createObjectURL(new Blob([paquet.subarray(4 + longueur)]));
    const a = el('a', { href: url, download: nom });
    document.body.append(a);
    a.click();
    a.remove();
    setTimeout(() => URL.revokeObjectURL(url), 10000);
    signaler('Document déchiffré : ' + nom);
  }

  /* ------------------------------ Échanges de données ------------------------------ */
  sections.echanges = async () => {
    rendreChoixAgents($('#choix-ech'));
    const r = await E.api('GET', APP.api + '/echanges');
    const corps = $('#liste-echanges tbody');
    vider(corps);
    if (r.data.length === 0) ligneVide(corps, 5, 'Aucun échange reçu.');
    r.data.forEach((x) => corps.append(el('tr', {},
      el('td', {}, x.type_schema),
      el('td', {}, x.expediteur ? x.expediteur.nom : ''),
      el('td', {}, x.statut),
      el('td', {}, quand(x.created_at)),
      el('td', {}, el('button', { type: 'button', class: 'btn btn-sm btn-primary', onclick: () => proteger(() => ouvrirEchange(x.uuid)) }, 'Ouvrir')))));
  };

  $('#form-echange').addEventListener('submit', (ev) => {
    ev.preventDefault();
    proteger(async () => {
      const f = ev.target;
      let json;
      try { json = JSON.parse(f.charge.value); } catch (e) { throw new Error('Le contenu doit être du JSON valide.'); }
      const dests = destinatairesChoisis($('#choix-ech'));
      const type = f.type_schema.value.trim();
      const p = await E.preparer(E.encodeur.encode(JSON.stringify(json)), 'echange:' + type, dests);
      await E.api('POST', APP.api + '/echanges', {
        type_schema: type, classification: f.classification.value,
        charge_chiffree: E.b64(p.chiffre), nonce: p.nonce, signature: p.signature, destinataires: p.destinataires,
      });
      f.reset();
      signaler('Données chiffrées et envoyées.');
      await sections.echanges();
    });
  });

  async function ouvrirEchange(uuid) {
    const r = await E.api('GET', APP.api + '/echanges/' + uuid);
    const x = r.echange;
    const clair = await E.ouvrir({
      chiffre: E.deb64(x.charge_chiffree), nonce: x.nonce, signature: x.signature,
      enveloppe: r.cle_enveloppee, ad: 'echange:' + x.type_schema,
      clesSignature: await E.clesSignatureDe(x.expediteur_id),
    });
    let affichage = E.decodeur.decode(clair);
    try { affichage = JSON.stringify(JSON.parse(affichage), null, 2); } catch (e) { /* texte brut */ }
    const d = $('#detail-echange');
    vider(d);
    d.classList.remove('cache');
    d.append(
      el('h3', {}, x.type_schema, ' — ', x.expediteur ? x.expediteur.nom : ''),
      el('pre', {}, affichage),
      el('button', { type: 'button', class: 'btn btn-sm btn-primary', onclick: () => proteger(async () => {
        await E.api('POST', APP.api + '/echanges/' + uuid + '/traiter');
        signaler('Marqué comme traité.');
        await sections.echanges();
      }) }, 'Marquer comme traité'));
  }

  /* ------------------------------ Administration ------------------------------ */
  if (APP.admin) {
    sections.admin = async () => {
      const select = $('#select-agent-cle');
      vider(select);
      const corps = $('#liste-agents tbody');
      vider(corps);
      annuaire.forEach((a) => {
        select.append(el('option', { value: a.id }, a.nom + ' (' + a.etat.code + ')'));
        corps.append(el('tr', {},
          el('td', {}, a.nom), el('td', {}, a.fonction), el('td', {}, a.etat.code),
          el('td', {}, a.cles.length + ' clé(s) de chiffrement'),
          el('td', {}, el('button', { type: 'button', class: 'btn btn-sm btn-danger', onclick: () => proteger(async () => {
            if (!confirm('Révoquer ' + a.nom + ' et toutes ses clés ? Action irréversible.')) return;
            await E.api('POST', APP.api + '/agents/' + a.id + '/revoquer');
            signaler('Agent révoqué.');
            await chargerAnnuaire();
            await sections.admin();
          }) }, 'Révoquer'))));
      });
      await chargerAudit();
    };

    $('#form-agent').addEventListener('submit', (ev) => {
      ev.preventDefault();
      proteger(async () => {
        const f = ev.target;
        const corps = { nom: f.nom.value, fonction: f.fonction.value, structure: f.structure.value || null,
          niveau_habilitation: f.niveau_habilitation.value };
        if (f.habilitation_expire_le.value) corps.habilitation_expire_le = f.habilitation_expire_le.value;
        const a = await E.api('POST', APP.api + '/agents', corps);
        f.reset();
        signaler('Agent créé (identifiant ' + a.id + '). Enregistrez maintenant ses clés publiques.');
        await chargerAnnuaire();
        await sections.admin();
      });
    });

    $('#form-cle').addEventListener('submit', (ev) => {
      ev.preventDefault();
      proteger(async () => {
        const f = ev.target;
        const corps = { usage: f.usage.value, cle_publique: f.cle_publique.value.trim() };
        if (f.expire_le.value) corps.expire_le = f.expire_le.value;
        const k = await E.api('POST', APP.api + '/agents/' + f.agent.value + '/cles', corps);
        f.reset();
        signaler('Clé enregistrée. Empreinte SHA-256 : ' + k.empreinte + ' — comparez-la avec celle affichée à l\'agent.');
        await chargerAnnuaire();
        await sections.admin();
      });
    });

    async function chargerAudit() {
      const r = await E.api('GET', APP.api + '/audit');
      const corps = $('#liste-audit tbody');
      vider(corps);
      if (r.data.length === 0) ligneVide(corps, 4, 'Journal vide.');
      r.data.forEach((l) => corps.append(el('tr', {},
        el('td', {}, l.id), el('td', {}, quand(l.horodatage)),
        el('td', {}, l.acteur ? l.acteur.nom : 'système'),
        el('td', {}, l.action + (l.objet_type ? ' (' + l.objet_type + ' #' + l.objet_id + ')' : '')))));
    }

    $('#btn-verifier').addEventListener('click', () => proteger(async () => {
      const r = await E.api('GET', APP.api + '/audit/verification');
      const z = $('#resultat-audit');
      z.className = r.valide ? 'alert alert-success' : 'alert alert-danger';
      z.textContent = r.valide
        ? 'Chaîne intacte : ' + r.lignes + ' ligne(s) vérifiée(s). Dernier hash : ' + r.dernier_hash
        : 'CHAÎNE ROMPUE à la ligne ' + r.ligne_en_defaut + ' (' + r.raison + ').';
    }));
  }

  /* ------------------------------ Démarrage ------------------------------ */
  $('#btn-verrouiller-haut').addEventListener('click', () => { E.verrouiller(); majTrousseau(); signaler('Trousseau verrouillé.'); });
  window.addEventListener('hashchange', aller);
  window.addEventListener('pagehide', () => E.verrouiller());
  aller();
})();