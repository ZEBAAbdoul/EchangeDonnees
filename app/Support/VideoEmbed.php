<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Prépare l'intégration d'une vidéo Facebook à partir d'un lien de partage.
 *
 * Fichier : app/Support/VideoEmbed.php
 *
 * 1. Les liens https://www.facebook.com/share/v/XXXX/ ne sont pas intégrables : on récupère côté serveur l'adresse
 *    réelle de la vidéo (Facebook renvoie ses balises Open Graph au robot « facebookexternalhit »).
 * 2. Cette adresse est nettoyée (sous-domaine web./m. → www., accents déjà encodés) pour éviter un double encodage.
 * 3. L'image d'aperçu de Facebook expire au bout de quelques jours : on la copie dans storage/app/public
 *    (nécessite « php artisan storage:link »).
 */
class VideoEmbed
{
    /**
     * @return array{embed:string, thumb:?string, canonical:string}
     */
    public static function facebook(string $url): array
    {
        $cle = 'fb_video_v2_' . md5($url);

        if ($en_cache = Cache::get($cle)) {
            return $en_cache;
        }

        $canonical = self::normaliser($url);
        $distante = null;
        $ok = false;

        try {
            $reponse = Http::withHeaders([
                'User-Agent' => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
                'Accept-Language' => 'fr-FR,fr;q=0.9,en;q=0.8',
            ])
                ->timeout(6)
                ->withOptions(['allow_redirects' => ['max' => 5, 'track_redirects' => true]])
                ->get($url);

            if ($reponse->successful()) {
                $html = $reponse->body();
                $og = self::meta($html, 'og:url');
                $distante = self::meta($html, 'og:image');

                $historique = $reponse->header('X-Guzzle-Redirect-History');
                $finale = $historique ? trim(last(explode(',', $historique))) : null;

                $candidate = $og ?: $finale;
                if ($candidate && !preg_match('~/(login|checkpoint)~i', $candidate)) {
                    $canonical = self::normaliser($candidate);
                    $ok = true;
                }
            }
        } catch (\Throwable $e) {
            // Facebook injoignable : on garde l'adresse d'origine
        }

        $resultat = [
            'embed' => 'https://www.facebook.com/plugins/video.php?href=' . rawurlencode($canonical)
                . '&show_text=false&autoplay=true&width=560',
            'thumb' => $distante ? (self::copierMiniature($distante, md5($canonical)) ?: $distante) : null,
            'canonical' => $canonical,
        ];

        // Succès : 30 jours. Échec : 10 min, pour réessayer vite sans ralentir chaque affichage de la page.
        Cache::put($cle, $resultat, $ok ? now()->addDays(30) : now()->addMinutes(10));

        return $resultat;
    }

    /** Adresse propre : https://www.facebook.com/{page}/videos/{id}/, /reel/{id}/ ou /watch/?v={id}. */
    private static function normaliser(string $url): string
    {
        $d = rawurldecode($url);

        if (preg_match('~facebook\.com/([^/?#]+)/videos/(?:[^/?#]+/)?(\d{6,})~', $d, $m) && $m[1] !== 'watch') {
            return 'https://www.facebook.com/' . $m[1] . '/videos/' . $m[2] . '/';
        }
        if (preg_match('~facebook\.com/reel/(\d+)~', $d, $m)) {
            return 'https://www.facebook.com/reel/' . $m[1] . '/';
        }
        if (preg_match('~facebook\.com/watch/?\?(?:[^#]*&)?v=(\d+)~', $d, $m)) {
            return 'https://www.facebook.com/watch/?v=' . $m[1];
        }

        return preg_replace('~//(?:web|m|mbasic|business)\.facebook\.com~i', '//www.facebook.com', $url);
    }

    /** Copie l'aperçu sur le disque « public » et renvoie son adresse, ou null en cas d'échec. */
    private static function copierMiniature(string $distante, string $id): ?string
    {
        $chemin = 'videos/miniatures/fb_' . $id . '.jpg';

        try {
            $disque = Storage::disk('public');
            if ($disque->exists($chemin)) {
                return asset('storage/' . $chemin);
            }
            $r = Http::timeout(8)->get($distante);
            if ($r->successful() && str_starts_with((string) $r->header('Content-Type'), 'image/')) {
                $disque->put($chemin, $r->body());
                return asset('storage/' . $chemin);
            }
        } catch (\Throwable $e) {
        }

        return null;
    }

    /** Lit une balise <meta property="..." content="..."> (attributs dans n'importe quel ordre). */
    private static function meta(string $html, string $propriete): ?string
    {
        $p = preg_quote($propriete, '~');
        $motifs = [
            '~<meta[^>]+property=["\']' . $p . '["\'][^>]+content=["\']([^"\']+)["\']~i',
            '~<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']' . $p . '["\']~i',
        ];
        foreach ($motifs as $motif) {
            if (preg_match($motif, $html, $m)) {
                return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }
        }
        return null;
    }
}