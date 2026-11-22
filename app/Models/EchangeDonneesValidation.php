<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EchangeDonneesValidation extends Model
{
    use HasUuids;

    protected $table = 'echanges_donnees_validations';

    protected $fillable = [
        'echange_id', 'valideur_id', 'decision', 'signature', 'motif_chiffre', 'motif_nonce', 'decide_le',
    ];

    protected $casts = ['decide_le' => 'datetime'];

    public function echange(): BelongsTo
    {
        return $this->belongsTo(EchangeDonnees::class, 'echange_id');
    }

    public function valideur(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'valideur_id');
    }
}