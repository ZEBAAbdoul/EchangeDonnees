<?php

namespace App\View\Components;

use App\Models\Actualite;
use App\Models\Contact;
use App\Models\Document;
use App\Models\Photo;
use App\Models\Slide;
use App\Models\User;
use App\Models\Video;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class Dashboard extends Component
{
    

    /**
     * Compteurs affichés dans les cartes du tableau de bord.
     */
    // private function calculerStats(): array
    // {
    //     return [
    //         'actualites_publiees'   => Actualite::publiees()->count(),
    //         'actualites_brouillons' => Actualite::nonPubliees()->count(),
    //         'messages_total'        => Contact::count(),
    //         'messages_non_lus'      => Contact::nonLus()->count(),
    //         'documents_total'       => Document::count(),
    //         'telechargements_total' => (int) Document::sum('nombre_telechargements'),
    //         'photos_total'          => Photo::count(),
    //         'photos_visibles'       => Photo::where('est_visible', true)->count(),
    //         'videos_total'          => Video::count(),
    //         'videos_visibles'       => Video::where('est_visible', true)->count(),
    //         'slides_actifs'         => Slide::actifs()->count(),
    //     ];
    // }

    /**
     * Données du graphique : 6 mois calendaires (le mois courant + les 5 précédents).
     */
    

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.dashboard');
    }
}