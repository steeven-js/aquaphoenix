<?php

namespace App\Filament\Resources\OrderResource\Pages;

use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\OrderResource;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Http\Controllers\MonthController;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function afterSave(): void
    {
        $order = $this->record;

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

        if ($order->wasChanged('delivered_date')) {
            MonthController::updateMonthStats(
                \Carbon\Carbon::parse($order->delivered_date)->format('m'),
                \Carbon\Carbon::parse($order->delivered_date)->format('Y')
            );
        }
    }

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
