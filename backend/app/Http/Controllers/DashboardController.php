<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getStats(Request $request)
    {
        // Ces données seront dynamiques plus tard avec les vraies tables MySQL.
        // Pour l'instant, elles permettent de valider l'affichage du Dashboard immédiatement.
        $stats = [
            'ventes_du_jour' => 12, // Nombre de ventes aujourd'hui
            'chiffre_affaires_mois' => 450000, // En FCFA ou ta monnaie locale
            'stock_critique' => [
                ['id' => 1, 'nom' => 'Papier Rame A4', 'quantite' => 3, 'seuil' => 10],
                ['id' => 2, 'nom' => 'Encre Noire HP', 'quantite' => 1, 'seuil' => 5],
            ],
            'commandes_impression_attente' => 7,
            'apprenants_inscrits_mois' => 25,
            'graphique_ventes' => [
                'labels' => ['Jour 1', 'Jour 5', 'Jour 10', 'Jour 15', 'Jour 20', 'Jour 25', 'Jour 30'],
                'donnees' => [15000, 30000, 25000, 45000, 35000, 60000, 50000] // Évolution du CA sur 30 jours
            ]
        ];

        return response()->json([
            'status' => 'success',
            'data' => $stats
        ], 200);
    }
}