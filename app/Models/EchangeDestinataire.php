<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ligne d'un document pour un agent : destinataire (avec sa clé de lecture) ou administrateur valideur.
 * cle_chiffree est NULL tant qu'un destinataire d'un autre État n'a pas reçu la clé (statut « attente »).
 */
class DocumentDestinataire extends Model
{
    use HasUuids;

    protected $table = 'document_destinataires';

    protected $fillable = ['document_id', 'agent_id', 'role', 'cle_chiffree', 'statut', 'telecharge_le'];

    protected $casts = ['telecharge_le' => 'datetime'];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }
}