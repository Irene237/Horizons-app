<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PrintOrderController; // IMPORT DU CONTRÔLEUR D'IMPRESSION (MODULE C)

// --- ROUTES PUBLIQUES ---
Route::post('/login', [AuthController::class, 'login'])->name('login');


// --- ROUTES PROTÉGÉES PAR SANCTUM (Utilisateurs connectés) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Module A2 : Récupérer les statistiques du Tableau de bord
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    
    // Module B1 : CRUD complet des Produits
    Route::apiResource('products', ProductController::class);
    
    // Module B2 : Enregistrer une vente (valider le panier)
    Route::post('/sales', [SaleController::class, 'store']);
    
    // Route sécurisée pour générer et télécharger le PDF de la facture
    Route::get('/sales/{id}/invoice', [SaleController::class, 'downloadInvoice']);
    
    // Module B3 : CRUD complet des Clients
    Route::apiResource('clients', ClientController::class);
    
    // --- MODULE C : GESTION DES IMPRESSIONS, TARIFS & DEVIS ---
    // Récupérer la liste (avec filtres ?status= ou ?is_quotation=) et créer un devis/commande
    Route::get('/print-orders', [PrintOrderController::class, 'index']);
    Route::post('/print-orders', [PrintOrderController::class, 'store']);
    
    // Action spécifique : Convertir un devis (is_quotation = true) en commande ferme
    Route::put('/print-orders/{id}/convert', [PrintOrderController::class, 'convertQuotation']);
    
    // Action spécifique : Modifier le statut (Suivi Kanban : En attente, En production, Prêt, Livré)
    Route::patch('/print-orders/{id}/status', [PrintOrderController::class, 'updateStatus']);
    
    // Récupérer l'utilisateur actuellement connecté
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});