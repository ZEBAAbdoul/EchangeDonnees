<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EchangeDonneesDestinataire extends Model
{
    use HasUuids;

    protected $table = 'echanges_donnees_destinataires';

    protected $fillable = ['echange_id', 'agent_id', 'cle_chiffree', 'statut', 'lu_le', 'traite_le'];

    protected $casts = ['lu_le' => 'datetime', 'traite_le' => 'datetime'];

    public function echange(): BelongsTo
    {
        return $this->belongsTo(EchangeDonnees::class, 'echange_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}