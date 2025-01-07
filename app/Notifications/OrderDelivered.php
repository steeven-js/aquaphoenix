<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Filament\Notifications\Notification as FilamentNotification;

class OrderDelivered extends Notification implements ShouldQueue
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
            ->warning()
            ->title('Commande marquée comme livrée')
            ->body("La commande {$this->order->number} a été automatiquement marquée comme livrée.")
            ->send();

        return [
            'title' => 'Commande marquée comme livrée',
            'message' => "La commande {$this->order->number} a été automatiquement marquée comme livrée.",
            'order_id' => $this->order->id,
            'icon' => 'heroicon-o-truck',
            'color' => 'warning',
        ];
    }
}
