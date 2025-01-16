<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Models\Order;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\OrderResource;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Http\Controllers\MonthController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Auth;

/**
 * Classe pour gérer la création d'une nouvelle commande
 */
class CreateOrder extends CreateRecord
{
    /**
     * Définit la classe de ressource associée
     */
    protected static string $resource = OrderResource::class;

    /**
     * Actions à effectuer après la création d'une commande
     * - Met à jour la commande précédente comme livrée
     * - Génère le bon de livraison
     * - Envoie des notifications
     * - Met à jour les statistiques mensuelles
     */
    protected function afterCreate(): void
    {
        $order = $this->record;

        // Recherche de la dernière commande non livrée
        $previousOrder = Order::where('id', '<', $order->id)
            ->where('status', '!=', 'livré')
            ->latest()
            ->first();

        // Si une commande précédente existe, la marquer comme livrée
        if ($previousOrder) {
            $previousOrder->status = 'livré';
            $previousOrder->delivered_date = now();
            $previousOrder->save();

            // Envoi d'une notification pour la mise à jour de la commande précédente
            Notification::make()
                ->warning()
                ->title('Commande précédente mise à jour')
                ->icon('heroicon-o-check-circle')
                ->body("La commande {$previousOrder->number} a été automatiquement marquée comme livrée.")
                ->actions([
                    Action::make('Voir')
                        ->url(OrderResource::getUrl('edit', ['record' => $previousOrder])),
                ])
                ->sendToDatabase(Auth::user());
        }

        // Génération du bon de livraison pour la nouvelle commande
        $orderController = new OrderController();
        $orderController->generateDeliveryNote($order);

        // Envoi d'une notification pour la création de la nouvelle commande
        Notification::make()
            ->title('Nouvelle commande')
            ->icon('heroicon-o-shopping-cart')
            ->body("**Commande {$order->number} créée pour le client {$order->customer->name}**")
            ->actions([
                Action::make('Voir')
                    ->url(OrderResource::getUrl('edit', ['record' => $order])),
                Action::make('Bon de livraison')
                    ->url(route('order.delivery-note.download', $order))
                    ->openUrlInNewTab(),
            ])
            ->sendToDatabase(Auth::user());

        // Mise à jour des statistiques mensuelles globales
        MonthController::initializeAllMonths();
    }

    /**
     * Définit l'URL de redirection après la création
     *
     * @return string URL de la liste des commandes
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
