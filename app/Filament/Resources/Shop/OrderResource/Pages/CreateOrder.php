<?php

namespace App\Filament\Resources\Shop\OrderResource\Pages;

use App\Models\Shop\Order;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use App\Http\Controllers\MonthController;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Shop\OrderResource;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;

class CreateOrder extends CreateRecord
{
    use HasWizard;

    protected static string $resource = OrderResource::class;

    protected ?string $heading = 'Créer un bon de livraison';

    protected function afterCreate(): void
    {
        // Récupération de la commande
        $order = $this->record;

        // Notification
        Notification::make()
            ->title('Nouvelle commande')
            ->icon('heroicon-o-shopping-bag')
            ->body("** Commande {$order->id} créée pour le client {$order->customer->name}**")
            ->actions([
                Action::make('View')
                    ->url(OrderResource::getUrl('edit', ['record' => $order])),
            ])
            ->sendToDatabase(auth()->user());

        // Mise à jour du numéro de commande
        // $order->number = 'CMD-' . str_pad($order->id, 4, '0', STR_PAD_LEFT);
        // $order->save();

        // Mise à jour du mois
        $updateMonth = new MonthController;
        $updateMonth->month();
    }

    // Redirection
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Order Details')
                ->label('Informations de la commande')
                ->schema([
                    Section::make()->schema(OrderResource::getFormSchema())->columns(),
                ]),

            Step::make('Order Items')
                ->label('Articles de la commande')
                ->schema([
                    Section::make()->schema(OrderResource::getFormSchema('items')),
                ]),
        ];
    }

        /**
     * Méthode pour actualiser le statut des commandes en fonction de la date de livraison
     */
    private function updateOrderStatus()
    {
        // Obtention de tous les orders
        $orders = Order::all();

        // Tableau pour stocker les IDs des commandes mises à jour
        $updatedOrderIds = [];

        foreach ($orders as $order) {
            // Vérifiez si la date de livraison est inférieure à la date actuelle
            if ($order->delivery_date < now()) {
                // La date de livraison est inférieure à la date actuelle, mettez à jour le statut de l'ordre
                $order->status = 'livré';
                $order->save();
                // Ajoutez l'ID de la commande au tableau des IDs des commandes mises à jour
                $updatedOrderIds[] = $order->id;
            }
        }

        // Si des commandes ont été mises à jour, créez une notification
        if (auth()->check()) {
            $recipient = auth()->user();

            Notification::make()
                ->title('Le statut des commandes (ID: ' . implode(', ', $updatedOrderIds) . ') a été mis à jour')
                ->sendToDatabase($recipient);
        }

        // Retournez le tableau des IDs des commandes mises à jour
        return $updatedOrderIds;
    }
}
