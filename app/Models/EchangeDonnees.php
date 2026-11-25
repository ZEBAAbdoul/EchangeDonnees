<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EchangeDonnees extends Model
{
    use HasUuids;

    protected $table = 'echanges_donnees';

    /**
     * Types d'échanges acceptés.
     * Le contenu reste chiffré de bout en bout.
     */
    public const TYPES = [
        'alerte',
        'personne',
        'vehicule',
        'incident',
        'demande',
    ];

    public const PRIORITES = [
        'normale',
        'haute',
        'urgente',
    ];

    /**
     * Types pouvant recevoir une photo chiffrée.
     */
    public const TYPES_AVEC_PHOTO = [
        'personne',
        'vehicule',
    ];

    /**
     * États possibles du processus de validation.
     */
    public const VALIDATIONS = [
        'non_requise',
        'en_attente',
        'validee',
        'liberee',
        'refusee',
        'expiree',
        'annulee',
    ];

    /**
     * États dans lesquels l'expéditeur peut encore annuler l'échange
     * (rien n'a encore été remis à l'autre État).
     */
    public const ANNULABLES = [
        'en_attente',
        'validee',
    ];

    protected $fillable = [
        'expediteur_id',
        'type',
        'niveau',
        'priorite',
        'contenu_chiffre',
        'nonce',
        'signature',
        'photo_chemin',
        'photo_header',
        'photo_hash',
        'photo_taille',

        // Validation interétatique
        'etat_emetteur_id',
        'validation',
        'expire_le',
        'valide_le',
        'libere_le',
        'libere_par',
    ];

    /**
     * Le chemin physique de la photo ne doit jamais
     * être exposé dans les réponses JSON.
     */
    protected $hidden = [
        'photo_chemin',
    ];

    /**
     * Conversions automatiques.
     */
    protected function casts(): array
    {
        return [
            'niveau'       => 'integer',
            'photo_taille' => 'integer',
            'expire_le'    => 'datetime',
            'valide_le'    => 'datetime',
            'libere_le'    => 'datetime',
        ];
    }

    /**
     * Agent ayant créé l'échange.
     */
    public function expediteur(): BelongsTo
    {
        return $this->belongsTo(
            Agent::class,
            'expediteur_id'
        );
    }

    /**
     * État d'origine de l'échange.
     */
    public function etatEmetteur(): BelongsTo
    {
        return $this->belongsTo(
            Etat::class,
            'etat_emetteur_id'
        );
    }

    /**
     * Agent ayant libéré l'échange.
     */
    public function liberateur(): BelongsTo
    {
        return $this->belongsTo(
            Agent::class,
            'libere_par'
        );
    }

    /**
     * Destinataires de l'échange (et administrateurs valideurs : colonne « role »).
     */
    public function destinataires(): HasMany
    {
        return $this->hasMany(
            EchangeDonneesDestinataire::class,
            'echange_id'
        );
    }

    /**
     * Décisions des administrateurs valideurs.
     */
    public function validations(): HasMany
    {
        return $this->hasMany(
            EchangeDonneesValidation::class,
            'echange_id'
        );
    }

    /**
     * Vérifie si l'échange nécessite une validation interétatique.
     */
    public function necessiteValidation(): bool
    {
        return $this->validation !== 'non_requise';
    }

    /**
     * Vérifie si l'échange est libéré.
     */
    public function estLibere(): bool
    {
        return $this->validation === 'liberee'
            && $this->libere_le !== null;
    }

    /**
     * Vérifie si l'expéditeur peut encore annuler l'échange.
     */
    public function estAnnulable(): bool
    {
        return in_array($this->validation, self::ANNULABLES, true);
    }

    /**
     * Expire les échanges dont le délai de validation est dépassé.
     *
     * Seuls les échanges en attente sont concernés.
     */
    public static function expirerEchus(): int
    {
        return static::query()
            ->where('validation', 'en_attente')
            ->whereNotNull('expire_le')
            ->where('expire_le', '<=', now())
            ->update([
                'validation' => 'expiree',
                'updated_at' => now(),
            ]);
    }
}