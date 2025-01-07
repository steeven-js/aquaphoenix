<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bon de livraison - {{ $order->number }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 14px;
            line-height: 1.4;
            padding: 20px;
        }
        .header {
            margin-bottom: 30px;
        }
        .company-info {
            float: left;
            width: 50%;
        }
        .company-logo {
            text-align: right;
            float: right;
            width: 50%;
        }
        .company-logo img {
            max-width: 200px;
            height: auto;
        }
        .document-title {
            clear: both;
            text-align: center;
            margin: 20px 0;
            padding-top: 20px;
        }
        .document-title h1 {
            margin: 0;
            font-size: 24px;
        }
        table {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
        }
        .info-box {
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 20px;
        }
        .info-box h2 {
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <strong>{{ $company->name }}</strong><br>
            {{ $company->address }}<br>
            {{ $company->zip_code }}, {{ $company->city }}<br>
            {{ $company->country }}<br>
            Tél: {{ $company->phone }}<br>
            Email: {{ $company->email }}
        </div>
        <div class="company-logo">
            <img src="{{ public_path($company->logo) }}" alt="Logo">
        </div>
        <div class="clear"></div>
    </div>

    <div class="document-title">
        <h1>BON DE LIVRAISON N° {{ $order->number }}</h1>
        <p>Date : {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }}</p>
    </div>

    <div class="info-box">
        <h2>CLIENT</h2>
        <strong>{{ $order->customer->name }}</strong><br>
        @if($order->customer->address)
            {{ $order->customer->address }}<br>
        @endif
        @if($order->customer->phone1)
            Tél: {{ $order->customer->phone1 }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th width="50%">PRODUIT</th>
                <th width="35%">NOTES</th>
                <th width="15%">POIDS (KG)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orderData as $item)
            <tr>
                <td>{{ $item['Product Name'] }}</td>
                <td>{{ $item['Notes'] ?? '-' }}</td>
                <td>{{ $item['Weight'] }}</td>
            </tr>
            @endforeach
            <tr>
                <td colspan="2" style="text-align: right"><strong>TOTAL</strong></td>
                <td><strong>{{ $totalWeight }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Document généré le {{ \Carbon\Carbon::now()->format('d/m/Y à H:i') }}
    </div>
</body>
</html>
