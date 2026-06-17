<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController; // Import indispensable pour le Point de Vente (POS)

// Route publique : accessible sans Token
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées par Token Sanctum (Utilisateurs connectés)
Route::middleware('auth:sanctum')->group(function () {
    
    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Module A2 : Récupérer les statistiques du Tableau de bord
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    
    // Module B1 : CRUD complet des Produits
    Route::apiResource('products', ProductController::class);
    
    // NOUVELLE ROUTE - Module B2 : Enregistrer une vente (valider le panier)
    Route::post('/sales', [SaleController::class, 'store']);
    
    // Récupérer l'utilisateur actuellement connecté
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});