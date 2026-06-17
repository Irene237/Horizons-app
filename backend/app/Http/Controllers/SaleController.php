<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleController extends Controller
{
    // ENREGISTRER UNE VENTE (PANIER)
    public function store(Request $request)
    {
        // Validation des données reçues du panier
        $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'payment_method' => 'required|in:Espèces,Mobile Money,Virement',
            'discount' => 'numeric|min:0',
            'cart' => 'required|array|min:1', // Le panier doit contenir au moins 1 produit
            'cart.*.product_id' => 'required|exists:products,id',
            'cart.*.quantity' => 'required|integer|min:1',
        ]);

        // Utilisation d'une transaction DB pour annuler tout en cas de bug au milieu de la commande
        return DB::transaction(function () use ($request) {
            $subtotal = 0;
            $cartItems = [];

            // 1. Vérifier la disponibilité des stocks et calculer le sous-total
            foreach ($request->cart as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if ($product->stock_quantity < $item['quantity']) {
                    return response()->json([
                        'message' => "Stock insuffisant pour le produit : {$product->name}. Restant : {$product->stock_quantity}"
                    ], 400);
                }

                $itemTotal = $product->selling_price * $item['quantity'];
                $subtotal += $itemTotal;

                // On prépare les données pour la table pivot
                $cartItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->selling_price,
                    'product_model' => $product // Gardé temporairement pour déduire le stock après
                ];
            }

            // Calcul du total net après remise
            $discount = $request->input('discount', 0);
            $total = max(0, $subtotal - $discount);

            // 2. Créer l'enregistrement de la vente globale
            $sale = Sale::create([
                'client_id' => $request->client_id,
                'user_id' => $request->user()->id, // ID du vendeur connecté
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $request->payment_method,
                'invoice_number' => 'FAC-' . strtoupper(Str::random(8)), // Génération numéro de facture unique
            ]);

            // 3. Enregistrer les détails du panier et déduire les stocks
            foreach ($cartItems as $item) {
                // Insertion dans la table sale_details
                DB::table('sale_details')->insert([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Déduction physique du stock du produit
                $item['product_model']->decrement('stock_quantity', $item['quantity']);
            }

            return response()->json([
                'message' => 'Vente enregistrée avec succès ! Stock mis à jour.',
                'invoice_number' => $sale->invoice_number,
                'total_paid' => $sale->total
            ], 201);
        });
    }
}