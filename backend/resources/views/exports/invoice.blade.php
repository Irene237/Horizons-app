<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture {{ $sale->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; line-height: 1.4; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; font-size: 14px; }
        .top-table { width: 100%; text-align: left; border-collapse: collapse; margin-bottom: 20px; }
        .title { font-size: 28px; font-weight: bold; color: #4F46E5; }
        .heading { background: #F3F4F6; font-weight: bold; }
        table.items-table { width: 100%; text-align: left; border-collapse: collapse; margin-top: 20px; }
        table.items-table td, table.items-table th { padding: 10px; border-bottom: 1px solid #E5E7EB; }
        .text-right { text-align: right; }
        .total-zone { margin-top: 20px; float: right; width: 300px; }
        .total-zone table { width: 100%; border-collapse: collapse; }
        .total-zone td { padding: 5px 0; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table class="top-table">
            <tr>
                <td class="title">HORIZON APP</td>
                <td class="text-right">
                    <strong>Facture :</strong> {{ $sale->invoice_number }}<br>
                    <strong>Date :</strong> {{ $sale->created_at->format('d/m/Y H:i') }}<br>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Émis par :</strong> Gérant Horizon<br>
                </td>
                <td class="text-right">
                    <strong>Client :</strong> {{ $sale->client ? $sale->client->name : 'Client Passager' }}<br>
                    @if($sale->client && $sale->client->phone)
                        <strong>Téléphone :</strong> {{ $sale->client->phone }}
                    @endif
                </td>
            </tr>
        </table>

        <table class="items-table">
            <tr class="heading">
                <th>Désignation</th>
                <th>P.U (FCFA)</th>
                <th class="text-right">Qté</th>
                <th class="text-right">Total (FCFA)</th>
            </tr>
            @foreach($items as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>{{ number_format($item->price, 0, ',', ' ') }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->price * $item->quantity, 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </table>

        <div class="total-zone">
            <table>
                <tr>
                    <td><strong>Sous-Total :</strong></td>
                    <td class="text-right">{{ number_format($sale->subtotal, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr>
                    <td><strong>Remise :</strong></td>
                    <td class="text-right">- {{ number_format($sale->discount, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr style="border-top: 2px solid #4F46E5; font-size: 16px; font-weight: bold;">
                    <td>Net à Payer :</td>
                    <td class="text-right">{{ number_format($sale->total, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr style="color: #2563EB;">
                    <td>Montant Versé :</td>
                    <td class="text-right">{{ number_format($sale->amount_paid, 0, ',', ' ') }} FCFA</td>
                </tr>
                @if($sale->total > $sale->amount_paid)
                <tr style="color: #DC2626; font-weight: bold;">
                    <td>Reste dû (Dette) :</td>
                    <td class="text-right">{{ number_format($sale->total - $sale->amount_paid, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endif
            </table>
        </div>
    </div>
</body>
</html>