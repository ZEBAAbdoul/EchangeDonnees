<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentDestinataire extends Model
{
    use HasUuids;

    protected $table = 'document_destinataires';

    protected $fillable = ['document_id', 'agent_id', 'cle_chiffree', 'telecharge_le'];

    protected $casts = ['telecharge_le' => 'datetime'];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}