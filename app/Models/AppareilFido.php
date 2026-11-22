<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppareilFido extends Model
{
    protected $table = 'appareils_fido';

    protected $fillable = [
        'agent_id',
        'credential_id',
        'cle_publique',
        'compteur_signatures',
        'libelle',
        'derniere_utilisation_le',
    ];

    protected $casts = [
        'compteur_signatures'     => 'integer',
        'derniere_utilisation_le' => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}