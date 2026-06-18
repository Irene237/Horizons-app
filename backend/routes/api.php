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

// --- ROUTES PROTÉGÉES PAR SANCTUM ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth & User
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Module A2 : Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    
    // Module B1 : Produits
    Route::apiResource('products', ProductController::class);
    
    // Module B2 : Ventes
    Route::post('/sales', [SaleController::class, 'store']);
    Route::get('/sales/{id}/invoice', [SaleController::class, 'downloadInvoice']);
    
    // Module B3 : Clients
    Route::apiResource('clients', ClientController::class);
    
    // Module C : Impressions & Devis
    Route::get('/print-orders', [PrintOrderController::class, 'index']);
    Route::post('/print-orders', [PrintOrderController::class, 'store']);
    Route::put('/print-orders/{id}/convert', [PrintOrderController::class, 'convertQuotation']);
    Route::patch('/print-orders/{id}/status', [PrintOrderController::class, 'updateStatus']);
    
    // Module D : Formations
    Route::get('/courses', [CourseController::class, 'index']);
    Route::post('/courses', [CourseController::class, 'storeCourse']);
    Route::post('/courses/enroll', [CourseController::class, 'enrollClient']);
    Route::post('/courses/attendance', [CourseController::class, 'saveAttendance']);
    
    // PDF et Certificats
    Route::get('/courses/enrollments/{id}/certificate', [CourseController::class, 'generateCertificate']);
    
    // NOTE : Ces routes sont maintenant accessibles par le navigateur via l'URL avec token
    Route::get('/courses/enrollments/{id}/receipt-pdf', [CourseController::class, 'downloadReceipt']);
    Route::get('/courses/enrollments/{id}/certificate-pdf', [CourseController::class, 'downloadCertificate']);

    // Module E : Rapports
    Route::get('/reports/sales', [ReportController::class, 'salesReport']);
    Route::get('/reports/print-orders', [ReportController::class, 'printReport']);
    Route::get('/reports/courses', [ReportController::class, 'coursesReport']);
});