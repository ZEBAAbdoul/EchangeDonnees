<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Document chiffré de bout en bout (le serveur ne connaît ni son nom, ni son contenu).
 * Un document destiné à un autre État n'est lisible par cet État qu'après validation de tous les
 * administrateurs actifs de l'État émetteur (voir ValidationDocumentController).
 */
class Document extends Model
{
    use HasUuids;

    protected $table = 'documents';

    /** États possibles du processus de validation. */
    public const VALIDATIONS = ['non_requise', 'en_attente', 'validee', 'liberee', 'refusee', 'expiree', 'annulee'];

    /** États dans lesquels l'expéditeur peut encore annuler (rien n'a été remis à l'autre État). */
    public const ANNULABLES = ['en_attente', 'validee'];

    protected $fillable = [
        'expediteur_id', 'niveau', 'chemin', 'taille', 'header', 'meta_nonce', 'meta_chiffree', 'hash', 'signature',
        'etat_emetteur_id', 'validation', 'expire_le', 'valide_le', 'libere_le', 'libere_par',
    ];

    /** Le chemin physique du fichier ne doit jamais être exposé dans les réponses JSON. */
    protected $hidden = ['chemin'];

    protected function casts(): array
    {
        return [
            'niveau'    => 'integer',
            'taille'    => 'integer',
            'expire_le' => 'datetime',
            'valide_le' => 'datetime',
            'libere_le' => 'datetime',
        ];
    }

    public function expediteur(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'expediteur_id');
    }

    public function etatEmetteur(): BelongsTo
    {
        return $this->belongsTo(Etat::class, 'etat_emetteur_id');
    }

    public function destinataires(): HasMany
    {
        return $this->hasMany(DocumentDestinataire::class, 'document_id');
    }

    public function validations(): HasMany
    {
        return $this->hasMany(DocumentValidation::class, 'document_id');
    }

    public function estAnnulable(): bool
    {
        return in_array($this->validation, self::ANNULABLES, true);
    }

    /** Expire les documents dont le délai de validation est dépassé (seuls ceux « en attente » sont concernés). */
    public static function expirerEchus(): int
    {
        return static::query()
            ->where('validation', 'en_attente')
            ->whereNotNull('expire_le')
            ->where('expire_le', '<=', now())
            ->update(['validation' => 'expiree', 'updated_at' => now()]);
    }
}