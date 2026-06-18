<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture {{ $enrollment->receipt_number }}</title>
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
                    <strong>Facture :</strong> {{ $enrollment->receipt_number }}<br>
                    <strong>Date :</strong> {{ $enrollment->created_at->format('d/m/Y H:i') }}<br>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Émis par :</strong> Gérant Horizon<br>
                </td>
                <td class="text-right">
                    <strong>Client :</strong> {{ $enrollment->client ? $enrollment->client->name : 'N/A' }}<br>
                    @if($enrollment->client && $enrollment->client->phone)
                        <strong>Téléphone :</strong> {{ $enrollment->client->phone }}
                    @endif
                </td>
            </tr>
        </table>

        <table class="items-table">
            <tr class="heading">
                <th>Désignation de la formation</th>
                <th class="text-right">Total (FCFA)</th>
            </tr>
            <tr>
                <td>{{ $enrollment->course->title }}</td>
                <td class="text-right">{{ number_format($enrollment->amount_paid, 0, ',', ' ') }}</td>
            </tr>
        </table>

        <div class="total-zone">
            <table>
                <tr style="border-top: 2px solid #4F46E5; font-size: 16px; font-weight: bold;">
                    <td>Net à Payer :</td>
                    <td class="text-right">{{ number_format($enrollment->amount_paid, 0, ',', ' ') }} FCFA</td>
                </tr>
                <tr style="color: #2563EB;">
                    <td>Montant Versé :</td>
                    <td class="text-right">{{ number_format($enrollment->amount_paid, 0, ',', ' ') }} FCFA</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>