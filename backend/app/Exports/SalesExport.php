<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;
    protected $userId;

    public function __construct($startDate, $endDate, $userId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->userId = $userId;
    }

    /**
     * Récupérer la collection filtrée des ventes
     */
    public function collection()
    {
        $query = Sale::with(['user', 'products'])->whereBetween('created_at', [$this->startDate, $this->endDate]);

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        return $query->get();
    }

    /**
     * Définir les entêtes des colonnes
     */
    public function headings(): array
    {
        return [
            'ID Vente',
            'Facture N°',
            'Vendeur / Caissier',
            'Montant Total (FCFA)',
            'Date de Vente',
            'Produits achetés (Quantité)'
        ];
    }

    /**
     * Aligner et formater les données de chaque ligne
     */
    public function map($sale): array
    {
        // Rassembler les produits sous forme de chaîne textuelle lisible
        $productsSummary = $sale->products->map(function($product) {
            return $product->name . ' (x' . $product->pivot->quantity . ')';
        })->implode(', ');

        return [
            $sale->id,
            $sale->invoice_number ?? 'N/A',
            $sale->user->name ?? 'Inconnu',
            $sale->total_amount,
            $sale->created_at->format('d/m/Y H:i'),
            $productsSummary
        ];
    }
}