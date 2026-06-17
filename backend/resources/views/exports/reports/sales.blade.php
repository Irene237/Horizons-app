<!DOCTYPE html>
<html>
<head>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Rapport des Ventes ({{ $period['start'] }} au {{ $period['end'] }})</h2>
    <p>Total Chiffre d'Affaires : <b>{{ number_format($total_revenue, 0, ',', ' ') }} FCFA</b></p>
    <table>
        <thead>
            <tr>
                <th>Facture</th>
                <th>Vendeur</th>
                <th>Montant</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->invoice_number }}</td>
                <td>{{ $sale->user->name ?? 'N/A' }}</td>
                <td>{{ number_format($sale->total_amount, 0, ',', ' ') }} FCFA</td>
                <td>{{ $sale->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>