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

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function afterCreate(): void
    {
        $order = $this->record;

        // Mettre à jour la commande précédente comme livrée
        $previousOrder = Order::where('id', '<', $order->id)
            ->where('status', '!=', 'livré')
            ->latest()
            ->first();

        if ($previousOrder) {
            $previousOrder->status = 'livré';
            $previousOrder->delivered_date = now();
            $previousOrder->save();

            // Notification pour la commande précédente
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

        // Générer le bon de livraison
        $orderController = new OrderController();
        $orderController->generateDeliveryNote($order);

        // Notification pour la nouvelle commande
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

        // Mise à jour des statistiques mensuelles
        MonthController::initializeAllMonths();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
