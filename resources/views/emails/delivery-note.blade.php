@component('mail::message')
# Nouveau bon de livraison

Un nouveau bon de livraison a été généré.

**Numéro de commande :** {{ $order->number }} <br>
**Client :** {{ $order->customer->name }} <br>
**Date :** {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y') }} <br>

Le bon de livraison est joint à cet email.

@component('mail::button', ['url' => $url])
Voir le bon de livraison
@endcomponent

Cordialement,
{{ config('app.name') }}
@endcomponent
