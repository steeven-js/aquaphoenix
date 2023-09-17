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

    protected function afterCreate(): void
    {
        $order = $this->record;

        Notification::make()
            ->title('Nouvelle commande')
            ->icon('heroicon-o-shopping-bag')
            ->body("** Commande {$order->number} créée pour le client {$order->customer->name}**")
            ->actions([
                Action::make('View')
                    ->url(OrderResource::getUrl('edit', ['record' => $order])),
            ])
            ->sendToDatabase(auth()->user());

        $order->number = 'CMD-' . str_pad($order->id, 4, '0', STR_PAD_LEFT);
        $order->save();

        $updateMonth = new MonthController;
        $updateMonth->month();
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Order Details')
                ->schema([
                    Section::make()->schema(OrderResource::getFormSchema())->columns(),
                ]),

            Step::make('Order Items')
                ->schema([
                    Section::make()->schema(OrderResource::getFormSchema('items')),
                ]),
        ];
    }
}
