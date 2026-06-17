<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * 1. LIRE (Lister les clients avec barre de recherche)
     */
    public function index(Request $request)
    {
        $query = Client::query();

        // Recherche par nom ou par téléphone
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('name', 'asc')->get();

        return response()->json($clients, 200);
    }

    /**
     * 2. CRÉER (Ajouter un nouveau client)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:clients,email',
            'address' => 'nullable|string|max:255',
            'balance_due' => 'nullable|numeric|min:0', // Solde dû initial (ex: si dette existante)
        ]);

        $client = Client::create($validated);

        return response()->json([
            'message' => 'Client enregistré avec succès !',
            'client' => $client
        ], 201);
    }

    /**
     * 3. LIRE UN SEUL CLIENT (Avec son historique d'achats)
     */
    public function show($id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Client introuvable.'], 404);
        }

        return response()->json($client, 200);
    }

    /**
     * 4. MODIFIER (Mettre à jour les infos d'un client)
     */
    public function update(Request $request, $id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Client introuvable.'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|unique:clients,email,' . $id,
            'address' => 'nullable|string|max:255',
            'balance_due' => 'sometimes|numeric|min:0',
        ]);

        $client->update($validated);

        return response()->json([
            'message' => 'Fiche client mise à jour avec succès !',
            'client' => $client
        ], 200);
    }

    /**
     * 5. SUPPRIMER
     */
    public function destroy($id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json(['message' => 'Client introuvable.'], 404);
        }

        $client->delete();

        return response()->json(['message' => 'Client supprimé avec succès !'], 200);
    }
}