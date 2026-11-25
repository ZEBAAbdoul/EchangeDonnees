<?php

namespace App\Services;

/**
 * Règles de validation des envois vers un autre État.
 * Doit rester identique à analyserEnvoi() / aSignerValidation() du client (echange.js).
 */
class ValidationEchange
{
    /** Délai laissé aux administrateurs pour valider (même valeur que DELAI_VALIDATION_JOURS dans app.js). */
    public const DELAI_JOURS = 7;

    /**
     * Qui reçoit tout de suite, qui valide, qui attend ?
     *
     * @param array $moi          ['id','etat_id','est_admin','actif','habilitation','nom']
     * @param array $destinataires même forme
     * @param array $admins       administrateurs actifs (tous États)
     */
    public static function planifier(array $moi, array $destinataires, array $admins, int $niveau): array
    {
        $plan = [
            'erreur'         => null,
            'validation'     => 'non_requise',
            'internes'       => [],
            'externes'       => [],
            'valideurs'      => [],
            'liberer_direct' => false,
        ];

        foreach ($destinataires as $d) {
            if (empty($d['actif'])) {
                $plan['erreur'] = 'Un destinataire est désactivé.';
                return $plan;
            }
            if ($d['habilitation'] < $niveau) {
                $plan['erreur'] = "Habilitation insuffisante pour {$d['nom']}.";
                return $plan;
            }
            if ($d['id'] === $moi['id']) {
                continue;
            }
            if ($d['etat_id'] === $moi['etat_id']) {
                $plan['internes'][] = $d['id'];
            } else {
                $plan['externes'][] = $d['id'];
            }
        }

        if (! $plan['externes']) {
            return $plan;               // envoi dans le même État : aucune validation
        }

        foreach ($admins as $a) {
            if (! empty($a['actif']) && $a['etat_id'] === $moi['etat_id'] && $a['id'] !== $moi['id']) {
                $plan['valideurs'][] = $a['id'];
            }
        }

        if (! $plan['valideurs'] && empty($moi['est_admin'])) {
            $plan['erreur'] = "Aucun administrateur actif dans votre État pour valider cet envoi vers un autre État.";
            return $plan;
        }

        // Seul administrateur de son État : son envoi vaut validation
        $plan['liberer_direct'] = ! $plan['valideurs'];
        $plan['validation'] = $plan['liberer_direct'] ? 'liberee' : 'en_attente';

        return $plan;
    }

    /** Enveloppes reçues conformes au plan ? Retourne un message d'erreur, ou null si tout est bon. */
    public static function verifierEnveloppes(array $plan, string $meId, array $enveloppes, array $valideurs): ?string
    {
        $parId = [];
        foreach ($enveloppes as $e) {
            if (isset($parId[$e['agent_id']])) {
                return 'Destinataire en double.';
            }
            $parId[$e['agent_id']] = $e['cle'] ?? null;
        }

        $avecCle = array_merge([$meId], $plan['internes'], $plan['liberer_direct'] ? $plan['externes'] : []);
        foreach ($avecCle as $id) {
            if (empty($parId[$id])) {
                return 'Enveloppe de clé manquante pour un destinataire.';
            }
        }

        if (! $plan['liberer_direct']) {
            foreach ($plan['externes'] as $id) {
                if (! empty($parId[$id])) {
                    return "Un destinataire d'un autre État ne doit pas recevoir de clé avant validation.";
                }
            }
        }

        $ids = array_column($valideurs, 'agent_id');
        $attendus = $plan['valideurs'];
        sort($ids);
        sort($attendus);
        if ($ids !== $attendus) {
            return 'La liste des administrateurs valideurs est incorrecte.';
        }
        foreach ($valideurs as $v) {
            if (empty($v['cle'])) {
                return 'Enveloppe manquante pour un administrateur.';
            }
        }

        return null;
    }

    /** Texte signé par un administrateur (identique à aSignerValidation du client). */
    public static function messageDecision(
        string $echangeId,
        string $decision,
        string $signatureExpediteur,
        ?string $motifNonce,
        ?string $motifChiffre
    ): string {
        return implode('|', ['valid', $echangeId, $decision, $signatureExpediteur, $motifNonce ?? '', $motifChiffre ?? '']);
    }

    public static function signatureValide(?string $signature, string $message, ?string $clePublique): bool
    {
        if (! $signature || ! $clePublique) {
            return false;
        }

        $sig = self::deB64($signature);
        $pk = self::deB64($clePublique);

        if ($sig === null || $pk === null
            || strlen($sig) !== SODIUM_CRYPTO_SIGN_BYTES
            || strlen($pk) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            return false;
        }

        return sodium_crypto_sign_verify_detached($sig, $message, $pk);
    }

    /** Règle stricte : tous les valideurs exigés comptent, même désactivés depuis l'envoi. Aucune dispense. */
    public static function statutApresDecision(array $decisions): string
    {
        if (in_array('refuse', $decisions, true)) {
            return 'refusee';
        }
        if ($decisions === []) {
            return 'en_attente';        // jamais validée sans valideur
        }

        return in_array('en_attente', $decisions, true) ? 'en_attente' : 'validee';
    }

    /** Les enveloppes fournies couvrent exactement les destinataires en attente. */
    public static function liberationConforme(array $enAttente, array $fournis): bool
    {
        if (count($fournis) !== count(array_unique($fournis))) {
            return false;
        }
        sort($enAttente);
        sort($fournis);

        return $enAttente !== [] && $enAttente === $fournis;
    }

    public static function transitionAutorisee(string $de, string $vers): bool
    {
        $ok = ['nouveau' => ['lu', 'traite', 'rejete'], 'lu' => ['traite', 'rejete']];

        return in_array($vers, $ok[$de] ?? [], true);
    }

    /** Accepte le base64 standard ou URL-safe, avec ou sans « = ». */
    private static function deB64(string $s): ?string
    {
        $s = strtr($s, '-_', '+/');
        $s .= str_repeat('=', (4 - strlen($s) % 4) % 4);
        $r = base64_decode($s, true);

        return $r === false ? null : $r;
    }
}