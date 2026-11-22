<?php

namespace App\Services;

use App\Models\Agent;
use App\Models\JournalAudit;
use App\Models\Noeud;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class AuditService
{
    /**
     * Ajoute une ligne chaînée et signée au journal du nœud local.
     * À appeler dans la même transaction que l'action auditée : si le journal
     * échoue, l'action est annulée.
     */
    public function enregistrer(string $action, ?Agent $acteur = null, ?Model $objet = null, ?string $ip = null): JournalAudit
    {
        $noeudId = (int) config('echange.noeud_id');
        $cleSecrete = base64_decode((string) config('echange.cle_signature_secrete'), true);

        if ($noeudId <= 0 || $cleSecrete === false || strlen($cleSecrete) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
            throw new RuntimeException("Configuration du journal d'audit invalide.");
        }

        return DB::transaction(function () use ($noeudId, $cleSecrete, $action, $acteur, $objet, $ip) {
            // Verrou sur le nœud : une seule écriture à la fois, même pour la toute première ligne.
            Noeud::whereKey($noeudId)->lockForUpdate()->firstOrFail();

            $precedent = JournalAudit::where('noeud_id', $noeudId)->orderByDesc('id')->value('hash');

            $ligne = [
                'noeud_id'   => $noeudId,
                'acteur_id'  => $acteur?->getKey(),
                'action'     => $action,
                'objet_type' => $objet ? Str::snake(class_basename($objet)) : null,
                'objet_id'   => $objet?->getKey(),
                'horodatage' => now()->format('Y-m-d H:i:s.u'),
            ];

            $hash = self::calculerHash($ligne, $precedent);

            return JournalAudit::create($ligne + [
                'adresse_ip'     => $ip,
                'hash_precedent' => $precedent,
                'hash'           => $hash,
                'signature'      => base64_encode(sodium_crypto_sign_detached($hash, $cleSecrete)),
            ]);
        });
    }

    /** Hash SHA-256 d'une ligne + du hash précédent (ordre et types figés). */
    public static function calculerHash(array $l, ?string $hashPrecedent): string
    {
        $texte = static fn ($v) => $v === null ? null : (string) $v;

        return hash('sha256', json_encode([
            $texte($l['noeud_id']),
            $texte($l['acteur_id']),
            $l['action'],
            $l['objet_type'],
            $texte($l['objet_id']),
            $l['horodatage'],
            $hashPrecedent,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Vérifie toute la chaîne d'un nœud : liens de hachage et signatures.
     * Ne détecte pas la suppression des DERNIÈRES lignes : comparer avec les ancrages externes.
     */
    public function verifier(int $noeudId, string $clePubliqueBase64): array
    {
        $clePublique = base64_decode($clePubliqueBase64, true);
        if ($clePublique === false || strlen($clePublique) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            throw new RuntimeException('Clé publique de vérification invalide.');
        }

        $precedent = null;
        $total = 0;

        foreach (JournalAudit::where('noeud_id', $noeudId)->lazyById(1000) as $l) {
            $total++;

            $attendu = self::calculerHash([
                'noeud_id'   => $l->noeud_id,
                'acteur_id'  => $l->acteur_id,
                'action'     => $l->action,
                'objet_type' => $l->objet_type,
                'objet_id'   => $l->objet_id,
                // PostgreSQL supprime les zéros finaux des microsecondes (« .12 » au lieu de « .120000 ») :
                // on remet la forme canonique à 6 chiffres avant de recalculer le hash.
                'horodatage' => Carbon::parse($l->getRawOriginal('horodatage'), 'UTC')->format('Y-m-d H:i:s.u'),
            ], $precedent);

            if ($l->hash_precedent !== $precedent || ! hash_equals($attendu, $l->hash)) {
                return ['valide' => false, 'ligne_en_defaut' => $l->id, 'raison' => 'chaîne de hachage rompue'];
            }

            $signature = base64_decode($l->signature, true);
            if ($signature === false || ! sodium_crypto_sign_verify_detached($signature, $l->hash, $clePublique)) {
                return ['valide' => false, 'ligne_en_defaut' => $l->id, 'raison' => 'signature invalide'];
            }

            $precedent = $l->hash;
        }

        return ['valide' => true, 'lignes' => $total, 'dernier_hash' => $precedent];
    }
}