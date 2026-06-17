<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Route publique : accessible sans être connecté
Route::post('/login', [AuthController::class, 'login']);

// Routes protégées : il faut obligatoirement fournir un Token valide pour y accéder
Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Route de test pour récupérer l'utilisateur connecté
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});