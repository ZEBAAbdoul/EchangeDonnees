<?php

namespace App\Http\Controllers;

use App\Models\ParametresSite;
use App\Models\Temoignage;
use App\Models\Filiere;
use App\Models\CategorieFormation;
use App\Models\Formation;
use App\Models\Actualite;
use App\Models\CategorieDocument;
use App\Models\Document;
use App\Models\Partenaire;
use App\Models\SessionFormation;
use App\Models\Slide;
use App\Models\UnitePedagogique;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $paramSite = Cache::remember('site.parametres', now()->addHours(6), function () {
            return ParametresSite::first();
        });

        $actualites = Actualite::publiees()->latest()->limit(10)->get();
        $derniereActualite = $actualites->first();

        $dernieresActualites = Actualite::where('is_publiee', true)->latest()->take(5)->get();

        $slides = Slide::actifs()->pluck('image');


        return view('welcome', [
            'param_site'          => $paramSite,

            'actualites'          => $actualites,
            'derniereActualite'   => $derniereActualite,

            'dernieresActualites' => $dernieresActualites,
            'slides' => $slides,
        ]);
    }

    public function mentionLegale()
    {
        $param_site = ParametresSite::first();

        return view('mentions-legales', ['param_site' => $param_site]);
    }

    public function organigramme()
    {
        $param_site = ParametresSite::first();

        return view('organigramme', ['param_site' => $param_site]);
    }

    public function mission()
    {
        $param_site = ParametresSite::first();

        return view('mission', ['param_site' => $param_site]);
    }

    public function document()
    {
        $param_site = ParametresSite::first();

        return view('documents', ['param_site' => $param_site]);
    }

    public function faq()
    {
        $param_site = ParametresSite::first();

        return view('faq', ['param_site' => $param_site]);
    }


    // Mot du Directeur
    public function motDuDirecteur()
    {
        $param_site = ParametresSite::first();

        return view('mot_directeur.index', [
            'param_site' => $param_site,
        ]);
    }

    // dans le contrôleur
    public function quiSommesNous()
    {
        $param_site = ParametresSite::first();

        return view('qui-sommes-nous', ['param_site' => $param_site]);
    }

    public function services()
    {
        $param_site = ParametresSite::first();

        return view('service', ['param_site' => $param_site]);
    }

    // Unites Pedagogiques
    public function unitesPedagogiques()
    {
        $unites = UnitePedagogique::publiees()
            ->orderBy('ordre')
            ->get();


        return view('unites-pedagogiques.index', compact('unites'));
    }

    public function conditionutilisation()
    {
        return view('condition-utilisation');
    }

    public function politiqueConfidentialite()
    {
        return view('politique-confidentialite');
    }
}
