<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\PrintOrderController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ReportController; // IMPORT DU CONTRÔLEUR DE RAPPORTS & EXPORTS (MODULE E)

// --- ROUTES PUBLIQUES ---
Route::post('/login', [AuthController::class, 'login'])->name('login');

// --- ROUTES DE TÉLÉCHARGEMENT PDF TEMPORAIREMENT PUBLIQUES POUR TEST SUR NAVIGATEUR ---
// Route de téléchargement visuel du reçu d'inscription en PDF
Route::get('/courses/enrollments/{id}/receipt-pdf', [CourseController::class, 'downloadReceipt']);

// Route de téléchargement visuel de l'attestation de fin de formation en PDF
Route::get('/courses/enrollments/{id}/certificate-pdf', [CourseController::class, 'downloadCertificate']);


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
    
    // --- MODULE D : GESTION DES FORMATIONS ---
    // D1 : Catalogue des formations (Lister et Créer)
    Route::get('/courses', [CourseController::class, 'index']);
    Route::post('/courses', [CourseController::class, 'storeCourse']);

    // D2 : Inscription d'un apprenant (avec blocage si complet et génération reçu)
    Route::post('/courses/enroll', [CourseController::class, 'enrollClient']);

    // D3 : Émargement / Suivi des présences et absences
    Route::post('/courses/attendance', [CourseController::class, 'saveAttendance']);

    // D4 : Vérification textuelle d'éligibilité pour l'attestation (Seuil strict >= 70%)
    Route::get('/courses/enrollments/{id}/certificate', [CourseController::class, 'generateCertificate']);
    
    // --- MODULE E : RAPPORTS & EXPORTS STATISTIQUES ---
    // Récupération JSON, Excel (?format=excel) ou PDF (?format=pdf)
    Route::get('/reports/sales', [ReportController::class, 'salesReport']);
    Route::get('/reports/print-orders', [ReportController::class, 'printReport']);
    Route::get('/reports/courses', [ReportController::class, 'coursesReport']);
    
    // Récupérer l'utilisateur actuellement connecté
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});