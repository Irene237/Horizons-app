<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log; // Ajoute cet import pour les logs

class DashboardController extends Controller
{
    public function getStats(Request $request)
    {
        try {
            $stats = [
                'ventes_du_jour' => 12,
                'chiffre_affaires_mois' => 450000,
                'stock_critique' => [
                    ['id' => 1, 'nom' => 'Papier Rame A4', 'quantite' => 3, 'seuil' => 10],
                    ['id' => 2, 'nom' => 'Encre Noire HP', 'quantite' => 1, 'seuil' => 5],
                ],
                'commandes_impression_attente' => 7,
                'apprenants_inscrits_mois' => 25,
                'graphique_ventes' => [
                    'labels' => ['Jour 1', 'Jour 5', 'Jour 10', 'Jour 15', 'Jour 20', 'Jour 25', 'Jour 30'],
                    'donnees' => [15000, 30000, 25000, 45000, 35000, 60000, 50000]
                ]
            ];

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ], 200);

        } catch (Exception $e) {
            // Log l'erreur réelle dans le fichier storage/logs/laravel.log
            Log::error('Dashboard Stats Error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des statistiques.'
            ], 500);
        }
    }
}