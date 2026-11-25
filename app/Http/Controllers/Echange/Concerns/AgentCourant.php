<?php

namespace App\Http\Controllers\Echange\Concerns;

use App\Models\Agent;
use Illuminate\Http\Request;

trait AgentCourant
{
    protected function agent(Request $r): Agent
    {
        return $r->user()->agent;          // ← adapte : utilisateur connecté → agent
    }

    protected function erreur(string $message, int $status = 422)
    {
        return response()->json(['message' => $message], $status);
    }
}