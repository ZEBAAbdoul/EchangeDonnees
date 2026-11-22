<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Noeud extends Model
{
    protected $fillable = [
        'etat_id',
        'url',
        'empreinte_certificat_tls',
        'statut',
    ];

    public function etat(): BelongsTo
    {
        return $this->belongsTo(Etat::class);
    }

    public function journal(): HasMany
    {
        return $this->hasMany(JournalAudit::class);
    }

    public function estActif(): bool
    {
        return $this->statut === 'actif';
    }
}