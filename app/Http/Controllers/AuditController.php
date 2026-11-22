<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResoutAgentCourant;
use App\Models\JournalAudit;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AuditController extends Controller
{
    use ResoutAgentCourant;

    public function __construct(private AuditService $audit)
    {
    }

    /** Consultation du journal du nœud local (administrateur uniquement). */
    public function index(Request $request): JsonResponse
    {
        $this->agentCourant($request);
        Gate::authorize('administrer');

        $filtres = $request->validate([
            'acteur_id' => ['nullable', 'integer'],
            'action'    => ['nullable', 'string', 'max:60'],
        ]);

        $lignes = JournalAudit::where('noeud_id', config('echange.noeud_id'))
            ->when($filtres['acteur_id'] ?? null, fn ($q, $v) => $q->where('acteur_id', $v))
            ->when($filtres['action'] ?? null, fn ($q, $v) => $q->where('action', $v))
            ->with('acteur:id,nom,fonction')
            ->orderByDesc('id')
            ->paginate(100);

        return response()->json($lignes);
    }

    /** Vérifie l'intégrité de toute la chaîne (hachage + signatures). */
    public function verifier(Request $request): JsonResponse
    {
        $this->agentCourant($request);
        Gate::authorize('administrer');

        return response()->json($this->audit->verifier(
            (int) config('echange.noeud_id'),
            (string) config('echange.cle_signature_publique')
        ));
    }
}