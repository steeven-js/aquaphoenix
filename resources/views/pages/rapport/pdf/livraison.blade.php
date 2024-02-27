<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Bon de livraison #: {{ $order->number }} - Aquaphoenix</title>

    <style>
        body {
            border: 1px solid #92D050;
        }

        .titre {
            text-align: center;
            font-size: 30px;
            color: #92D050;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #92D050;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #92D050;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #92D050;
            font-weight: bold;
        }

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }

        /** RTL **/
        .invoice-box.rtl {
            direction: rtl;
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }

        .invoice-box.rtl table {
            text-align: right;
        }

        .invoice-box.rtl table tr td:nth-child(2) {
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <h1 class="titre">Bon de livraison #: {{ $order->number }}</h1>
                    <table>
                        <tr>
                            <td class="title">
                                <img src="{{ public_path('images/logo.png') }}" style="width: 100%; max-width: 100px" />
                            </td>

                            <td>
                                Créé le: {{ $formattedCreationDate }}<br />
                                Livrée le: {{ $formattedDeliveredDate }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                {{ $order->customer->name }}<br />
                                {{ $order->customer->address }}<br />
                                {{ $order->customer->code }}, {{ $order->customer->commune }}<br />
                                {{ $order->customer->phone1 }}<br />
                                @if ($order->customer->phone2)
                                    {{ $order->customer->phone2 }}<br />
                                @endif
                                @if ($order->customer->email)
                                    {{ $order->customer->email }}<br />
                                @endif
                            </td>

                            <td>
                                Aquaphoenix
                                <br />
                                35 rue Joseph Lagrosilliére
                                <br />
                                97220, Trinité, Martinique
                                <br />
                                +596 696 34 81 12
                                <br />
                                contact@aquaphoenix.fr
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="heading">
                <td>Description</td>

                <td>Quantité en (kg)</td>
            </tr>

            @forelse ($orderData as $item)
                <tr class="item">
                    <td>{{ $item['Product Name'] }}</td>
                    <td>{{ $item['Quantity'] }} kg</td>
                </tr>

                @if ($orderNotes)
                    <tr class="item">
                        <td>Notes: {{ $orderNotes }}</td>
                    </tr>
                @endif
            @empty
                <p>Aucun produit à livrer</p>
            @endforelse


            <tr class="total">
                <td></td>
                <td>Total: {{ $totalQuantity }} kg</td>
            </tr>
        </table>
    </div>
</body>

</html>
