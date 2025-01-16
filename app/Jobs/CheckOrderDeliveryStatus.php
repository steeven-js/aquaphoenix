<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Job pour vérifier et mettre à jour le statut de livraison des commandes
 */
class CheckOrderDeliveryStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Exécute le job
     * Vérifie les commandes dont la date de livraison est passée
     * et les marque comme livrées si nécessaire
     */
    public function handle(): void
    {
        Log::info('Démarrage du job de vérification des statuts de livraison');

        // Récupère toutes les commandes non livrées
        // dont la date de livraison est passée
        $orders = Order::query()
            ->where('status', '!=', 'livré')
            ->whereNotNull('delivered_date')
            ->where('delivered_date', '<=', now())
            ->get();

        Log::info('Nombre de commandes à traiter: ' . $orders->count());

        $orders->each(function (Order $order) {
            Log::info('Traitement de la commande #' . $order->number);

            try {
                // Marque la commande comme livrée
                $order->status = 'livré';
                $order->save();

                Log::info('Commande #' . $order->number . ' marquée comme livrée');

                // Envoie une notification de confirmation
                Notification::make()
                    ->title('Commande marquée comme livrée')
                    ->body("La commande {$order->number} a été automatiquement marquée comme livrée.")
                    ->warning()
                    ->send();

                Log::info('Notification envoyée pour la commande #' . $order->number);
            } catch (\Exception $e) {
                Log::error('Erreur lors du traitement de la commande #' . $order->number . ': ' . $e->getMessage());
            }
        });

        Log::info('Fin du job de vérification des statuts de livraison');
    }
}
