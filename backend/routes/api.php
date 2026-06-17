<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController; // Ne pas oublier cet import

// Route publique
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées par Token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // NOUVELLE ROUTE : Récupérer les stats du Dashboard A2
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});