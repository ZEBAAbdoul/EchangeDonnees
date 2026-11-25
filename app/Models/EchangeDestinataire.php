<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class EchangeDestinataire extends Model
{
    use HasUuids;

    protected $table = 'echanges_donnees_destinataires';
    protected $fillable = ['echange_id', 'agent_id', 'role', 'cle_chiffree', 'statut', 'lu_le', 'traite_le'];

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'agent_id');
    }
    public function echange()
    {
        return $this->belongsTo(EchangeDonnees::class, 'echange_id');
    }
}
