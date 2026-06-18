<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Fonction de Connexion (Login)
     */
    public function login(Request $request)
    {
        // 1. Validation stricte des entrées
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation échouée',
                'errors' => $e->errors()
            ], 422);
        }

        // 2. Chercher l'utilisateur avec son client lié
        // On charge la relation 'client' définie dans le modèle User
        $user = User::with('client')->where('email', $request->email)->first();

        // 3. Vérifier les identifiants
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Identifiants incorrects.'
            ], 401);
        }

        // 4. Générer le token
        $role = $user->role ?? 'user';
        $token = $user->createToken('auth_token', [$role])->plainTextToken;

        // 5. Réponse réussie
        return response()->json([
            'message' => 'Connexion réussie',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $role
            ],
            // Si la relation client existe, on renvoie son ID, sinon null
            'client' => $user->client ? [
                'id' => $user->client->id
            ] : null
        ], 200);
    }

    /**
     * Fonction de Déconnexion (Logout)
     */
    public function logout(Request $request)
    {
        // On supprime uniquement le token utilisé pour cette requête
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Déconnexion réussie.'
        ], 200);
    }
}