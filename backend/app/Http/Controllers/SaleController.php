<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf; // Import du générateur PDF

class SaleController extends Controller
{
    /**
     * 1. ENREGISTRER LA VENTE (Calcul stock, remise, et solde dû automatique)
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'payment_method' => 'required|in:Espèces,Mobile Money,Virement',
            'discount' => 'numeric|min:0',
            'amount_paid' => 'required|numeric|min:0', // Montant donné par le client
            'cart' => 'required|array|min:1',
            'cart.*.product_id' => 'required|exists:products,id',
            'cart.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $subtotal = 0;
            $cartItems = [];

            // Vérification de la disponibilité des stocks
            foreach ($request->cart as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);

                if ($product->stock_quantity < $item['quantity']) {
                    return response()->json([
                        'message' => "Stock insuffisant pour : {$product->name}. Restant : {$product->stock_quantity}"
                    ], 400);
                }

                $subtotal += $product->selling_price * $item['quantity'];

                $cartItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->selling_price,
                    'product_model' => $product
                ];
            }

            $discount = $request->input('discount', 0);
            $total = max(0, $subtotal - $discount);
            $amountPaid = $request->input('amount_paid', 0);

            // Gestion automatique du Solde Dû (Crédit) si un client est sélectionné
            if ($total > $amountPaid && $request->client_id) {
                $rest = $total - $amountPaid;
                $client = Client::find($request->client_id);
                $client->increment('balance_due', $rest); // On ajoute la dette sur sa fiche
            }

            // Enregistrement de la vente globale
            $sale = Sale::create([
                'client_id' => $request->client_id,
                'user_id' => $request->user()->id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'amount_paid' => $amountPaid,
                'payment_method' => $request->payment_method,
                'invoice_number' => 'FAC-' . strtoupper(Str::random(8)),
            ]);

            // Enregistrement des détails du panier et déduction des stocks
            foreach ($cartItems as $item) {
                DB::table('sale_details')->insert([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Soustraction du stock physique
                $item['product_model']->decrement('stock_quantity', $item['quantity']);
            }

            return response()->json([
                'message' => 'Vente enregistrée ! Stocks et compte client mis à jour.',
                'sale_id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'total' => $total,
                'rest_to_pay' => max(0, $total - $amountPaid)
            ], 201);
        });
    }

    /**
     * 2. GÉNÉRER ET TÉLÉCHARGER LA FACTURE PDF
     */
    public function downloadInvoice($id)
    {
        // Récupération de la vente avec le client associé
        $sale = Sale::with(['client'])->find($id);

        if (!$sale) {
            return response()->json(['message' => 'Facture introuvable.'], 404);
        }

        // LIGNE CORRIGÉE ICI : Récupération propre des éléments du panier
        $items = DB::table('sale_details')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->where('sale_details.sale_id', $id)
            ->select('products.name', 'sale_details.quantity', 'sale_details.price')
            ->get();

        $data = [
            'sale' => $sale,
            'items' => $items
        ];

        // Génération dynamique du PDF à partir du fichier HTML Blade
        $pdf = Pdf::loadView('exports.invoice', $data);

        return $pdf->download($sale->invoice_number . '.pdf');
    }
}