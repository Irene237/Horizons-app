<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Gérer la requête entrante.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Vérifier si l'utilisateur est connecté
        if (!$request->user()) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }

        // 2. Vérifier si le rôle de l'utilisateur fait partie des rôles autorisés pour la route
        if (!in_array($request->user()->role, $roles)) {
            return response()->json([
                'message' => 'Accès interdit : Vous n\'avez pas les permissions nécessaires pour ce module.'
            ], 403);
        }

        return $next($request);
    }
}