<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalAudit extends Model
{
    use HasUuids;

    protected $table = 'journal_audit';

    public $timestamps = false;

    protected $fillable = ['agent_id', 'action', 'cible_type', 'cible_id', 'ip'];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /** Enregistre une action (jamais le contenu des messages). */
    public static function noter(?Agent $agent, string $action, ?Model $cible = null): void
    {
        static::create([
            'agent_id'   => $agent?->id,
            'action'     => $action,
            'cible_type' => $cible ? class_basename($cible) : null,
            'cible_id'   => $cible?->getKey(),
            'ip'         => request()->ip(),
        ]);
    }
}