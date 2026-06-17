<!DOCTYPE html>
<html>
<head>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; }
    </style>
</head>
<body>
    <h2>Rapport des Impressions</h2>
    <p>Nombre total de commandes : {{ $orders_count }}</p>
    <table>
        <thead>
            <tr>
                <th>Client</th>
                <th>Support</th>
                <th>Quantité</th>
                <th>Prix Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->client->name }}</td>
                <td>{{ $order->support_type }}</td>
                <td>{{ $order->quantity }}</td>
                <td>{{ number_format($order->total_price, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>