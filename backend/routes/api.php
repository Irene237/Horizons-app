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
use App\Http\Controllers\ReportController;

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
    Route::get('/print-orders', [PrintOrderController::class, 'index']);
    Route::post('/print-orders', [PrintOrderController::class, 'store']);
    Route::put('/print-orders/{id}/convert', [PrintOrderController::class, 'convertQuotation']);
    Route::patch('/print-orders/{id}/status', [PrintOrderController::class, 'updateStatus']);
    
    // --- MODULE D : GESTION DES FORMATIONS ---
    Route::get('/courses', [CourseController::class, 'index']);
    Route::post('/courses', [CourseController::class, 'storeCourse']);
    Route::post('/courses/enroll', [CourseController::class, 'enrollClient']);
    Route::post('/courses/attendance', [CourseController::class, 'saveAttendance']);
    Route::get('/courses/enrollments/{id}/certificate', [CourseController::class, 'generateCertificate']);
    
    // Routes PDF sécurisées (Déplacées à l'intérieur du groupe Sanctum)
    Route::get('/courses/enrollments/{id}/receipt-pdf', [CourseController::class, 'downloadReceipt']);
    Route::get('/courses/enrollments/{id}/certificate-pdf', [CourseController::class, 'downloadCertificate']);

    // --- MODULE E : RAPPORTS & EXPORTS STATISTIQUES ---
    Route::get('/reports/sales', [ReportController::class, 'salesReport']);
    Route::get('/reports/print-orders', [ReportController::class, 'printReport']);
    Route::get('/reports/courses', [ReportController::class, 'coursesReport']);
    
    // Récupérer l'utilisateur actuellement connecté
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});