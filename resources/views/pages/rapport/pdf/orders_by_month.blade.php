<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .title {
            font-size: 1.5rem;
            font-weight: 800;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container mx-auto py-4">
        <h1 class="title text-3xl font-semibold mb-4">Rapport des livraisons</h1>
        <h1 class="title text-3xl font-semibold mb-4">{{ $monthName }} {{ $year }}</h1>

        @if ($ordersData)
            <p class="mb-2">Poid total des livraisons ({{ $monthName }} {{ $year }}) : {{ $totalQuantity }} kg</p>

            <table class="w-full border-collapse border border-gray-400">
                <thead>
                    <tr>
                        <th class="py-2 px-4 bg-gray-200 border">Commande</th>
                        <th class="py-2 px-4 bg-gray-200 border">Nom du client</th>
                        <th class="py-2 px-4 bg-gray-200 border">Date de création</th>
                        <th class="py-2 px-4 bg-gray-200 border">Date de livraison</th>
                        <th class="py-2 px-4 bg-gray-200 border">Produits de la commande</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($ordersData as $order)
                        <tr>
                            <td class="py-2 px-4 border">{{ $order['Order Number'] }}</td>
                            <td class="py-2 px-4 border">{{ $order['Customer Name'] }}</td>
                            <td class="py-2 px-4 border">{{ $order['Creation Date'] }}</td>
                            <td class="py-2 px-4 border">{{ $order['Delivered Date'] }}</td>
                            <td class="py-2 px-4 border">
                                <ul>
                                    @foreach ($order['Order Items'] as $item)
                                        <li>
                                            {{ $item['Product Name'] }}<br>
                                            Quantité: {{ $item['Quantity'] }} kg
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Aucune commande trouvée pour {{ $monthName }} {{ $year }}.</p>
        @endif
    </div>
</body>

</html>
