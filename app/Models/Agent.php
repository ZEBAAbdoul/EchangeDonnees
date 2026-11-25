<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Agent extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'organisme_id',
        'nom',
        'fonction',
        'habilitation',
        'est_admin',
        'actif',
    ];

    protected $casts = [
        'est_admin' => 'boolean',
        'actif'     => 'boolean',
    ];

    /** L'agent est-il actif ? (utilisé par ResoutAgentCourant et le menu) */
    public function estActif(): bool
    {
        return (bool) $this->actif;
    }

    /** L'agent administre-t-il le module d'échanges ? */
    public function estAdmin(): bool
    {
        return (bool) $this->est_admin;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organisme(): BelongsTo
    {
        return $this->belongsTo(Organisme::class);
    }

    /** Historique des clés publiques (actives et révoquées). */
    public function cles(): HasMany
    {
        return $this->hasMany(ClePublique::class);
    }

    /**
     * Clé publique actuellement valable (la plus récente non révoquée).
     * Pas de latestOfMany() : il calcule MAX(id), impossible sur un UUID avec PostgreSQL.
     * hasOne + tri suffit : lors du chargement groupé, Laravel garde la première ligne par agent.
     */
    public function cleActive(): HasOne
    {
        return $this->hasOne(ClePublique::class)
            ->whereNull('revoquee_le')
            ->orderByDesc('created_at');
    }

    /** Conversations dont l'agent est membre. */
    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_membres', 'agent_id', 'conversation_id')
            ->withPivot('dernier_lu_le')
            ->withTimestamps();
    }
}
