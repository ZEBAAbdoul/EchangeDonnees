<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClePublique extends Model
{
    use HasUuids;

    protected $table = 'cles_publiques';

    protected $fillable = ['agent_id', 'cle_chiffrement', 'cle_signature', 'revoquee_le'];

    protected $casts = ['revoquee_le' => 'datetime'];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}