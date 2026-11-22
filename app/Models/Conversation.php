<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasUuids;

    protected $fillable = ['titre', 'est_groupe', 'cree_par'];

    protected $casts = ['est_groupe' => 'boolean'];

    public function membres(): BelongsToMany
    {
        return $this->belongsToMany(Agent::class, 'conversation_membres', 'conversation_id', 'agent_id')
            ->withPivot('dernier_lu_le')
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function aMembre(Agent $agent): bool
    {
        return $this->membres()->whereKey($agent->id)->exists();
    }
}