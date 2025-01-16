<?php

namespace App\Filament\Resources\OrderResource\Pages;

use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\OrderResource;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Http\Controllers\MonthController;

/**
 * Classe pour gérer l'édition d'une commande existante
 */
class EditOrder extends EditRecord
{
    /**
     * Définit la classe de ressource associée
     */
    protected static string $resource = OrderResource::class;

    /**
     * Actions à effectuer après la sauvegarde
     * - Envoie une notification si le statut change
     * - Met à jour les statistiques mensuelles si la date de livraison change
     */
    protected function afterSave(): void
    {
        $order = $this->record;

        // Notification si le statut a changé
        if ($order->wasChanged('status')) {
            Notification::make()
                ->title('Statut mis à jour')
                ->icon('heroicon-o-check-circle')
                ->body("Le statut de la commande {$order->number} a été mis à jour vers '{$order->status}'.")
                ->actions([
                    Action::make('Voir')
                        ->url(OrderResource::getUrl('edit', ['record' => $order])),
                ])
                ->sendToDatabase(\Illuminate\Support\Facades\Auth::user());
            }

        // Mise à jour des stats si la date de livraison a changé
        if ($order->wasChanged('delivered_date')) {
            MonthController::updateMonthStats(
                \Carbon\Carbon::parse($order->delivered_date)->format('m'),
                \Carbon\Carbon::parse($order->delivered_date)->format('Y')
            );
        }
    }

    /**
     * Définit les actions disponibles dans l'en-tête
     *
     * @return array Les actions configurées
     */
    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make()
                ->after(function () {
                    Notification::make()
                        ->danger()
                        ->title('Commande supprimée')
                        ->body("La commande a été supprimée avec succès.")
                        ->send();
                }),
        ];
    }
}
