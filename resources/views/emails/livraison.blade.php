<x-mail::message>
# Bon de livraison

{{-- @dd($mailData['totalQuantity']) --}}
Ci-joint le bon de livraison pour la commande n° : {{ $mailData['number'] }}

Crée le : {{ $mailData['formattedCreationDate'] }}<br>
Livraison prévue le : {{ $mailData['formattedDeliveredDate'] }}

Quantité total : {{ $mailData['totalQuantity'] }}

Si vous éprouvez des difficultés à accéder au bon de livraison, veuillez cliquer sur le bouton ci-dessous.

<x-mail::button :url="$url">
Bon de livraison
</x-mail::button>

Bonne journée,<br>
{{ config('app.name') }}
</x-mail::message>
