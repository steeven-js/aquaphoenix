<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Filament\Notifications\Notification as FilamentNotification;

class OrderCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        // Envoyer aussi une notification Filament
        FilamentNotification::make()
            ->success()
            ->title('Nouvelle commande créée')
            ->body("La commande {$this->order->number} a été créée avec succès.")
            ->send();

        return [
            'title' => 'Nouvelle commande créée',
            'message' => "La commande {$this->order->number} a été créée avec succès.",
            'order_id' => $this->order->id,
            'icon' => 'heroicon-o-shopping-cart',
            'color' => 'success',
        ];
    }
}
