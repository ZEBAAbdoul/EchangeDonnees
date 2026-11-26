<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Décision (approbation ou refus) d'un administrateur de l'État émetteur, signée avec sa clé. */
class DocumentValidation extends Model
{
    use HasUuids;

    protected $table = 'document_validations';

    protected $fillable = ['document_id', 'valideur_id', 'decision', 'signature', 'motif_chiffre', 'motif_nonce', 'decide_le'];

    protected $casts = ['decide_le' => 'datetime'];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function valideur(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'valideur_id');
    }
}