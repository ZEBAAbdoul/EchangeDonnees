<?php

namespace App\Models;

use App\Models\DocumentDestinataire;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    use HasUuids;

    protected $fillable = [
        'expediteur_id', 'niveau', 'chemin', 'taille',
        'header', 'meta_nonce', 'meta_chiffree', 'hash', 'signature',
    ];

    /** Le chemin du fichier sur le serveur ne sort jamais dans le JSON. */
    protected $hidden = ['chemin'];

    public function expediteur(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'expediteur_id');
    }

    public function destinataires(): HasMany
    {
        return $this->hasMany(DocumentDestinataire::class);
    }
}