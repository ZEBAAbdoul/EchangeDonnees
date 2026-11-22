<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Destinataire extends Model
{
    protected $fillable = [
        'objet_type',
        'objet_id',
        'agent_id',
        'cle_agent_id',
        'cle_enveloppee',
        'lu_le',
    ];

    protected $casts = [
        'lu_le' => 'datetime',
    ];

    /** Le message, le document ou l'échange de données concerné. */
    public function objet(): MorphTo
    {
        return $this->morphTo();
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /** Clé publique utilisée pour envelopper la clé de contenu. */
    public function cleAgent(): BelongsTo
    {
        return $this->belongsTo(CleAgent::class, 'cle_agent_id');
    }

    public function marquerCommeLu(): void
    {
        if ($this->lu_le === null) {
            $this->forceFill(['lu_le' => now()])->save();
        }
    }
}