<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EchangeDonnees extends Model
{
    use HasUuids;

    protected $table = 'echanges_donnees';

    /** Types acceptés (le contenu, lui, est chiffré : le serveur ne le valide pas). */
    public const TYPES = ['alerte', 'personne', 'vehicule', 'incident', 'demande'];
    public const PRIORITES = ['normale', 'haute', 'urgente'];

    /** Types qui peuvent recevoir une photo (chiffrée de bout en bout comme le reste). */
    public const TYPES_AVEC_PHOTO = ['personne', 'vehicule'];

    protected $fillable = [
        'expediteur_id', 'type', 'niveau', 'priorite', 'contenu_chiffre', 'nonce', 'signature',
        'photo_chemin', 'photo_header', 'photo_hash', 'photo_taille',
    ];

    /** Le chemin du fichier sur le serveur ne sort jamais dans le JSON. */
    protected $hidden = ['photo_chemin'];

    public function expediteur(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'expediteur_id');
    }

    public function destinataires(): HasMany
    {
        return $this->hasMany(EchangeDonneesDestinataire::class, 'echange_id');
    }

    protected function casts(): array // ou protected $casts = [...] avant Laravel 11
{
    return [
        'niveau'       => 'integer',
        'photo_taille' => 'integer',
    ];
}
}