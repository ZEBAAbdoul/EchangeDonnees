{{-- Composants de validation des envois vers un autre État --}}
<link rel="stylesheet" href="{{ asset('css/echange/validation.css') }}">

{{-- À placer dans le formulaire d'envoi, sous le choix des destinataires --}}
<div id="ech-apercu-envoi" class="ech-apercu" aria-live="polite"></div>

{{-- Onglet « À valider » : à ajouter à côté de « Reçus » et « Envoyés » --}}
<template id="ech-tpl-onglet-a-valider">
    <button type="button" class="ech-onglet" data-filtre="a_valider">
        À valider <span id="ech-nb-a-valider" class="ech-compteur" hidden>0</span>
    </button>
</template>

{{-- À placer dans le panneau de détail d'un échange --}}
<div id="ech-validation-detail" class="ech-validation"></div>

<script src="{{ asset('js/echange/echange-validation.js') }}" defer></script>