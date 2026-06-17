<?php

namespace App\Http\Controllers;

use App\Models\PrintOrder;
use App\Models\PrintRate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PrintOrderController extends Controller
{
    /**
     * 1. LISTER LES COMMANDES / DEVIS (Filtre par statut pour le Kanban ou type de document)
     */
    public function index(Request $request)
    {
        $query = PrintOrder::with(['client', 'user']);

        // Filtrer par statut (En attente, En production, etc.) pour ton tableau Kanban
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtrer pour n'avoir que les Devis (1) ou que les Commandes (0)
        if ($request->has('is_quotation')) {
            $query->where('is_quotation', $request->is_quotation);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();
        return response()->json($orders, 200);
    }

    /**
     * 2. CRÉER UN DEVIS OU UNE COMMANDE (Calcul automatique du prix au m² ou à l'unité)
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'support_type' => 'required|string',
            'width_cm' => 'nullable|numeric|min:0',
            'height_cm' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'file' => 'nullable|file|mimes:pdf,png,jpg,jpeg|max:10240', // Max 10Mo
            'is_quotation' => 'required|boolean', // true = Devis, false = Commande
        ]);

        // Trouver le tarif correspondant au support
        $rateCard = PrintRate::where('support_type', $request->support_type)->first();

        if (!$rateCard) {
            return response()->json(['message' => "Le type de support '{$request->support_type}' n'est pas configuré."], 404);
        }

        $unitPrice = $rateCard->rate;
        $totalPrice = 0;

        // Calcul automatique selon la formule (m² ou unité)
        if ($rateCard->calculation_type === 'm2') {
            if (!$request->width_cm || !$request->height_cm) {
                return response()->json(['message' => "Les dimensions (largeur et hauteur) sont requises pour ce type de support."], 400);
            }
            // Calcul de la surface en m² : (Largeur / 100) * (Hauteur / 100)
            $surfaceM2 = ($request->width_cm / 100) * ($request->height_cm / 100);
            $totalPrice = $surfaceM2 * $unitPrice * $request->quantity;
        } else {
            // Calcul classique à l'unité
            $totalPrice = $unitPrice * $request->quantity;
        }

        // Gestion de l'upload du fichier/maquette
        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('print_files', 'public');
        }

        // Génération d'un numéro de document unique
        $prefix = $request->is_quotation ? 'DEV-' : 'CMD-';
        $documentNumber = $prefix . strtoupper(Str::random(6));

        // Enregistrement
        $printOrder = PrintOrder::create([
            'client_id' => $request->client_id,
            'user_id' => $request->user()->id,
            'support_type' => $request->support_type,
            'width_cm' => $request->width_cm,
            'height_cm' => $request->height_cm,
            'quantity' => $request->quantity,
            'file_path' => $filePath,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'is_quotation' => $request->is_quotation,
            'document_number' => $documentNumber,
            'status' => 'En attente'
        ]);

        return response()->json([
            'message' => $request->is_quotation ? 'Devis créé avec succès !' : 'Commande d\'impression enregistrée !',
            'data' => $printOrder
        ], 201);
    }

    /**
     * 3. CONVERTIR UN DEVIS EN COMMANDE FERME
     */
    public function convertQuotation($id)
    {
        $order = PrintOrder::find($id);

        if (!$order || !$order->is_quotation) {
            return response()->json(['message' => 'Devis introuvable ou déjà converti.'], 404);
        }

        // Mutation du devis en commande
        $order->is_quotation = false;
        // On change le préfixe du numéro de document de DEV- à CMD-
        $order->document_number = str_replace('DEV-', 'CMD-', $order->document_number);
        $order->save();

        return response()->json([
            'message' => 'Le devis a été converti en commande avec succès ! Le travail passe en attente de production.',
            'data' => $order
        ], 200);
    }

    /**
     * 4. METTRE À JOUR LE STATUT (Sécurisé : interdiction d'avancer un devis non converti)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:En attente,En production,Prêt,Livré'
        ]);

        $order = PrintOrder::find($id);

        if (!$order) {
            return response()->json(['message' => 'Commande introuvable.'], 404);
        }

        // SÉCURITÉ : Empêcher de lancer la fabrication ou la livraison si c'est encore un devis
        if ($order->is_quotation && $request->status !== 'En attente') {
            return response()->json([
                'message' => 'Impossible de changer le statut de fabrication. Vous devez d\'abord convertir ce devis en commande ferme.'
            ], 400);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'message' => "Statut mis à jour : La commande est désormais '{$order->status}'.",
            'data' => $order
        ], 200);
    }
}