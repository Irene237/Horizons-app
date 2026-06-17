<?php

namespace App\Exports;

use App\Models\PrintOrder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PrintOrdersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * Récupérer les commandes fermes de l'atelier d'impression
     */
    public function collection()
    {
        return PrintOrder::with('client')
            ->where('is_quotation', false)
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->get();
    }

    /**
     * Entêtes Excel
     */
    public function headings(): array
    {
        return [
            'ID Commande',
            'Client',
            'Type de Support',
            'Dimensions / Format',
            'Quantité',
            'Prix Total (FCFA)',
            'Statut Suivi (Kanban)',
            'Date de Commande'
        ];
    }

    /**
     * Correspondance des cellules
     */
    public function map($order): array
    {
        return [
            $order->id,
            $order->client->name ?? 'Client Anonyme',
            $order->support_type,
            $order->dimensions,
            $order->quantity,
            $order->total_price,
            $order->status,
            $order->created_at->format('d/m/Y H:i')
        ];
    }
}