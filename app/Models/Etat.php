<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Etat extends Model
{
    use HasUuids;

    protected $fillable = ['code', 'nom'];

    public function organismes(): HasMany
    {
        return $this->hasMany(Organisme::class);
    }
}