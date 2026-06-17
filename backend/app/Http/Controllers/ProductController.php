<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * 1. LIRE (Afficher la liste avec filtres et recherche)
     */
    public function index(Request $request)
    {
        $query = Product::query();

        // Filtre par recherche (Nom ou Référence)
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        // Filtre par catégorie (Matériel ou Consommable)
        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        $products = $query->get()->map(function ($product) {
            // Ajout d'une alerte visuelle/booléenne demandée par le cahier des charges
            $product->is_stock_critical = $product->stock_quantity < $product->alert_threshold;
            return $product;
        });

        return response()->json($products, 200);
    }

    /**
     * 2. CRÉER (Enregistrer un produit avec upload d'image)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'reference' => 'required|string|unique:products,reference',
            'category' => 'required|in:Matériel,Consommable',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'alert_threshold' => 'required|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 2Mo max
            'supplier' => 'nullable|string|max:255',
        ]);

        // Gérer l'upload de la photo
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $validated['image_path'] = Storage::url($path);
        }

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Produit créé avec succès !',
            'product' => $product
        ], 201);
    }

    /**
     * 3. LIRE UN SEUL PRODUIT
     */
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Produit introuvable.'], 404);
        }

        $product->is_stock_critical = $product->stock_quantity < $product->alert_threshold;

        return response()->json($product, 200);
    }

    /**
     * 4. MODIFIER (Mettre à jour un produit)
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Produit introuvable.'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'reference' => 'sometimes|string|unique:products,reference,' . $id,
            'category' => 'sometimes|in:Matériel,Consommable',
            'purchase_price' => 'sometimes|numeric|min:0',
            'selling_price' => 'sometimes|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',
            'alert_threshold' => 'sometimes|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'supplier' => 'nullable|string|max:255',
        ]);

        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($product->image_path) {
                $oldPath = str_replace('/storage/', '', $product->image_path);
                Storage::disk('public')->delete($oldPath);
            }
            // Enregistrer la nouvelle
            $path = $request->file('image')->store('products', 'public');
            $validated['image_path'] = Storage::url($path);
        }

        $product->update($validated);

        return response()->json([
            'message' => 'Produit mis à jour avec succès !',
            'product' => $product
        ], 200);
    }

    /**
     * 5. SUPPRIMER
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Produit introuvable.'], 404);
        }

        if ($product->image_path) {
            $oldPath = str_replace('/storage/', '', $product->image_path);
            Storage::disk('public')->delete($oldPath);
        }

        $product->delete();

        return response()->json(['message' => 'Produit supprimé avec succès !'], 200);
    }
}