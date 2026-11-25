<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ValidationDecision extends Model
{
    use HasUuids;

    protected $table = 'echanges_donnees_validations';
    protected $fillable = ['echange_id', 'valideur_id', 'decision', 'signature', 'motif_chiffre', 'motif_nonce', 'decide_le'];
    protected $casts = ['decide_le' => 'datetime'];

    public function valideur() { return $this->belongsTo(Agent::class, 'valideur_id'); }
    public function echange()  { return $this->belongsTo(EchangeDonnees::class, 'echange_id'); }
}