<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ligne d'un échange pour un agent : destinataire (avec sa clé de lecture) ou administrateur valideur.
 * cle_chiffree est NULL tant qu'un destinataire d'un autre État n'a pas reçu la clé (statut « attente »).
 */
class EchangeDonneesDestinataire extends Model
{
    use HasUuids;

    protected $table = 'echanges_donnees_destinataires';

    public const ROLE_DESTINATAIRE = 'destinataire';
    public const ROLE_VALIDEUR = 'valideur';

    protected $fillable = ['echange_id', 'agent_id', 'role', 'cle_chiffree', 'statut', 'lu_le', 'traite_le'];

    protected $casts = ['lu_le' => 'datetime', 'traite_le' => 'datetime'];

    public function echange(): BelongsTo
    {
        return $this->belongsTo(EchangeDonnees::class, 'echange_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }
}