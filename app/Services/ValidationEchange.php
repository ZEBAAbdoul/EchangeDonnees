<?php

namespace App\Services;

/**
 * Règles de validation des échanges de données qui partent vers un autre État.
 *
 * Classe pure (aucune dépendance à Laravel ni à la base) : les contrôleurs lui passent des tableaux,
 * elle renvoie des décisions. Cela permet de la tester seule et de relire la règle en un seul endroit.
 *
 * Règle : un échange destiné à au moins un agent d'un AUTRE État est « sortant ». Il n'est visible de cet État
 * qu'après approbation de TOUS les administrateurs actifs de l'État émetteur (l'expéditeur, s'il est
 * administrateur, compte comme ayant approuvé son propre envoi). Tant que ce n'est pas fait, les destinataires
 * étrangers n'ont PAS d'enveloppe de clé : ils ne peuvent rien lire, même si le serveur était compromis.
 */
final class ValidationEchange
{
    public const NON_REQUISE = 'non_requise';
    public const EN_ATTENTE = 'en_attente';
    public const VALIDEE = 'validee';       // tous ont approuvé, clés pas encore remises aux autres États
    public const LIBEREE = 'liberee';       // clés remises : l'échange est visible des autres États
    public const REFUSEE = 'refusee';
    public const EXPIREE = 'expiree';

    /** Changements de statut autorisés pour un destinataire. */
    private const TRANSITIONS = [
        'nouveau' => ['lu', 'traite', 'rejete'],
        'lu'      => ['traite', 'rejete'],
    ];

    /**
     * Analyse un envoi.
     *
     * @param array{id:string, etat_id:string, est_admin:bool, habilitation:int} $expediteur
     * @param array<int, array{id:string, etat_id:string, actif:bool, habilitation:int, nom:string}> $destinataires
     * @param array<int, array{id:string, etat_id:string, actif:bool, habilitation:int, nom:string}> $adminsEmetteur
     *        agents administrateurs de l'État émetteur (l'expéditeur et des inactifs peuvent y figurer)
     * @return array{erreur:?string, besoin_validation:bool, liberer_direct:bool, internes:string[],
     *               externes:string[], valideurs:string[], validation:string}
     */
    public static function planifier(array $expediteur, array $destinataires, array $adminsEmetteur, int $niveau): array
    {
        $plan = [
            'erreur' => null, 'besoin_validation' => false, 'liberer_direct' => false,
            'internes' => [], 'externes' => [], 'valideurs' => [], 'validation' => self::NON_REQUISE,
        ];

        // Destinataires (hors expéditeur) : actifs et habilités pour ce niveau de classification
        foreach ($destinataires as $d) {
            if ($d['id'] === $expediteur['id']) {
                continue;
            }
            if (! $d['actif']) {
                $plan['erreur'] = $d['nom'] . ' est inactif.';
                return $plan;
            }
            if ($d['habilitation'] < $niveau) {
                $plan['erreur'] = $d['nom'] . " n'a pas l'habilitation requise pour ce niveau.";
                return $plan;
            }
            if ($d['etat_id'] === $expediteur['etat_id']) {
                $plan['internes'][] = $d['id'];
            } else {
                $plan['externes'][] = $d['id'];
            }
        }

        $plan['besoin_validation'] = $plan['externes'] !== [];
        if (! $plan['besoin_validation']) {
            return $plan;
        }

        // Valideurs : tous les administrateurs ACTIFS de l'État émetteur, sauf l'expéditeur
        $valideurs = array_values(array_filter(
            $adminsEmetteur,
            fn ($a) => $a['actif'] && $a['id'] !== $expediteur['id'] && $a['etat_id'] === $expediteur['etat_id']
        ));

        if ($valideurs === [] && ! $expediteur['est_admin']) {
            $plan['erreur'] = "Aucun administrateur actif dans votre État pour valider cet envoi vers un autre État.";
            return $plan;
        }

        // Un valideur doit pouvoir lire l'échange pour le valider : habilitation suffisante
        foreach ($valideurs as $v) {
            if ($v['habilitation'] < $niveau) {
                $plan['erreur'] = "L'administrateur " . $v['nom'] . " n'a pas l'habilitation requise pour valider ce niveau.";
                return $plan;
            }
        }

        $plan['valideurs'] = array_column($valideurs, 'id');
        // Expéditeur administrateur et seul de son État : son envoi vaut validation, clés remises tout de suite
        $plan['liberer_direct'] = $valideurs === [] && $expediteur['est_admin'];
        $plan['validation'] = $plan['liberer_direct'] ? self::LIBEREE : self::EN_ATTENTE;

        return $plan;
    }

    /**
     * Contrôle les enveloppes de clés envoyées par le navigateur par rapport au plan.
     *
     * @param array<int, array{agent_id:string, cle:?string}> $destinataires
     * @param array<int, array{agent_id:string, cle:?string}> $valideurs
     * @return ?string message d'erreur, ou null si tout est conforme
     */
    public static function verifierEnveloppes(array $plan, string $expediteurId, array $destinataires, array $valideurs): ?string
    {
        $avecCle = array_merge([$expediteurId], $plan['internes'], $plan['liberer_direct'] ? $plan['externes'] : []);
        $sansCle = $plan['liberer_direct'] ? [] : $plan['externes'];

        $fournis = [];
        foreach ($destinataires as $d) {
            $fournis[$d['agent_id']] = ($d['cle'] ?? '') !== '' ? true : false;
        }

        $attendus = array_merge($avecCle, $sansCle);
        $ids = array_keys($fournis);
        sort($attendus);
        sort($ids);
        if ($attendus !== $ids) {
            return 'La liste des destinataires ne correspond pas : rechargez l\'annuaire et réessayez.';
        }

        foreach ($avecCle as $id) {
            if (! $fournis[$id]) {
                return 'Une enveloppe de clé est requise pour chaque destinataire de votre État.';
            }
        }
        foreach ($sansCle as $id) {
            if ($fournis[$id]) {
                return "Un destinataire d'un autre État ne peut pas recevoir de clé avant la validation des administrateurs.";
            }
        }

        $valideursFournis = array_column($valideurs, 'agent_id');
        $valideursAttendus = $plan['valideurs'];
        sort($valideursFournis);
        sort($valideursAttendus);
        if ($valideursFournis !== $valideursAttendus) {
            return 'La liste des administrateurs valideurs a changé : rechargez l\'annuaire et réessayez.';
        }
        foreach ($valideurs as $v) {
            if (($v['cle'] ?? '') === '') {
                return 'Une enveloppe de clé est requise pour chaque administrateur valideur.';
            }
        }

        return null;
    }

    /**
     * Statut de l'échange après une décision.
     *
     * @param string[] $decisions décisions des valideurs ENCORE ACTIFS (en_attente | approuve | refuse)
     */
    public static function statutApresDecision(array $decisions): string
    {
        if (in_array('refuse', $decisions, true)) {
            return self::REFUSEE;
        }
        if (in_array('en_attente', $decisions, true)) {
            return self::EN_ATTENTE;
        }

        return self::VALIDEE;   // tous les valideurs actifs ont approuvé (ou il n'en reste aucun)
    }

    /** Un destinataire peut-il passer de $actuel à $demande ? */
    public static function transitionAutorisee(string $actuel, string $demande): bool
    {
        return in_array($demande, self::TRANSITIONS[$actuel] ?? [], true);
    }

    /** Les enveloppes fournies pour la libération couvrent-elles exactement les destinataires en attente ? */
    public static function liberationConforme(array $enAttente, array $fournis): bool
    {
        sort($enAttente);
        sort($fournis);

        return $enAttente !== [] && $enAttente === $fournis;
    }

    /** Texte signé par un administrateur : lié à l'échange, à la signature de l'expéditeur et à la décision. */
    public static function messageDecision(string $echangeId, string $decision, string $signatureExpediteur, ?string $motifNonce, ?string $motifChiffre): string
    {
        return implode('|', ['valid', $echangeId, $decision, $signatureExpediteur, $motifNonce ?? '', $motifChiffre ?? '']);
    }

    /** Vérifie une signature Ed25519 (base64 « original », comme dans le navigateur). */
    public static function signatureValide(string $signatureB64, string $message, string $clePubliqueB64): bool
    {
        if (! function_exists('sodium_crypto_sign_verify_detached')) {
            return true;   // extension absente : le navigateur des lecteurs vérifie de toute façon
        }
        $signature = base64_decode($signatureB64, true);
        $cle = base64_decode($clePubliqueB64, true);
        if ($signature === false || $cle === false || strlen($signature) !== SODIUM_CRYPTO_SIGN_BYTES || strlen($cle) !== SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) {
            return false;
        }

        return sodium_crypto_sign_verify_detached($signature, $message, $cle);
    }
}