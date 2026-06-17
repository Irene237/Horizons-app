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

        // 2. Chercher l'utilisateur
        $user = User::where('email', $request->email)->first();

        // 3. Vérifier les identifiants
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Identifiants incorrects.'
            ], 401);
        }

        // 4. Générer le token
        // On s'assure que $user->role existe, sinon on met 'user' par défaut
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
            ]
        ], 200);
    }

    /**
     * Fonction de Déconnexion (Logout)
     */
    public function logout(Request $request)
    {
        // Supprime le token de l'utilisateur qui fait la requête
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Déconnexion réussie.'
        ], 200);
    }
}