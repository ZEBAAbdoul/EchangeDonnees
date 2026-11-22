<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organisme extends Model
{
    use HasUuids;

    protected $fillable = ['etat_id', 'sigle', 'nom'];

    public function etat(): BelongsTo
    {
        return $this->belongsTo(Etat::class);
    }

    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class);
    }
}