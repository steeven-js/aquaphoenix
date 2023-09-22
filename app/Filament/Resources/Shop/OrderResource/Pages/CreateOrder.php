<?php

namespace App\Filament\Resources\Shop\OrderResource\Pages;

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
            ->body("** Commande {$order->number} créée pour le client {$order->customer->name}**")
            ->actions([
                Action::make('View')
                    ->url(OrderResource::getUrl('edit', ['record' => $order])),
            ])
            ->sendToDatabase(auth()->user());

        // Mise à jour du numéro de commande
        $order->number = 'CMD-' . str_pad($order->id, 4, '0', STR_PAD_LEFT);
        $order->save();

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
}
