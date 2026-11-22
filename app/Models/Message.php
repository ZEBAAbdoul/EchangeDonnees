<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasUuids;

    protected $fillable = [
        'conversation_id', 'expediteur_id', 'niveau', 'contenu_chiffre', 'nonce', 'signature',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function expediteur(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'expediteur_id');
    }

    /** Enveloppes de clé : une par destinataire. */
    public function cles(): HasMany
    {
        return $this->hasMany(MessageCle::class);
    }
}