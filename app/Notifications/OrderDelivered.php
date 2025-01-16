<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Filament\Notifications\Notification as FilamentNotification;

/**
 * Notification envoyée lorsqu'une commande est marquée comme livrée
 */
class OrderDelivered extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Crée une nouvelle instance de notification
     *
     * @param Order $order La commande marquée comme livrée
     */
    public function __construct(public Order $order)
    {
    }

    /**
     * Détermine les canaux de notification à utiliser
     *
     * @param mixed $notifiable L'entité à notifier
     * @return array Les canaux de notification
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Prépare les données de la notification pour la base de données
     *
     * @param mixed $notifiable L'entité à notifier
     * @return array Les données de la notification
     */
    public function toDatabase($notifiable): array
    {
        // Envoyer aussi une notification Filament
        FilamentNotification::make()
            ->warning()
            ->title('Commande marquée comme livrée')
            ->body("La commande {$this->order->number} a été automatiquement marquée comme livrée.")
            ->send();

        // Retourne les données à stocker en base
        return [
            'title' => 'Commande marquée comme livrée',
            'message' => "La commande {$this->order->number} a été automatiquement marquée comme livrée.",
            'order_id' => $this->order->id,
            'icon' => 'heroicon-o-truck',
            'color' => 'warning',
        ];
    }
}
