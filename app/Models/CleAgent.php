<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CleAgent extends Model
{
    protected $table = 'cles_agents';

    protected $fillable = [
        'agent_id',
        'usage',
        'cle_publique',
        'empreinte',
        'expire_le',
        'revoquee_le',
        'motif_revocation',
    ];

    protected $casts = [
        'expire_le'   => 'datetime',
        'revoquee_le' => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function destinataires(): HasMany
    {
        return $this->hasMany(Destinataire::class, 'cle_agent_id');
    }

    /** Clés ni révoquées ni expirées. */
    public function scopeValides(Builder $query): Builder
    {
        return $query->whereNull('revoquee_le')
            ->where(fn (Builder $q) => $q->whereNull('expire_le')->orWhere('expire_le', '>', now()));
    }

    public function scopeUsage(Builder $query, string $usage): Builder
    {
        return $query->where('usage', $usage);
    }

    public function estRevoquee(): bool
    {
        return $this->revoquee_le !== null;
    }

    public function estValide(): bool
    {
        return ! $this->estRevoquee()
            && ($this->expire_le === null || $this->expire_le->isFuture());
    }
}