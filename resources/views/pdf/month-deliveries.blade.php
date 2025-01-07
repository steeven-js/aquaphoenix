<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Livraisons {{ $month }} {{ $year }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
        .total { margin-top: 20px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport de Livraisons</h1>
        <h2>{{ $month }} {{ $year }}</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>N° Commande</th>
                <th>Client</th>
                <th>Produits</th>
                <th>Poids (kg)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($order->delivered_date)->format('d/m/Y') }}</td>
                    <td>{{ $order->number }}</td>
                    <td>{{ $order->customer?->name }}</td>
                    <td>
                        @foreach($order->items as $item)
                            {{ $item->product?->name }} ({{ $item->qty }}kg)<br>
                        @endforeach
                    </td>
                    <td>{{ $order->items->sum('qty') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        <p><strong>Poids Total : {{ number_format($totalWeight, 0) }} kg</strong></p>
        <p><strong>Nombre de livraisons : {{ $orders->count() }}</strong></p>
    </div>
</body>
</html>
