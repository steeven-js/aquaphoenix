<?php

namespace App\Filament\Resources\Shop\OrderResource\Pages;

use App\Filament\Resources\Shop\OrderResource;
use Filament\Pages\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = OrderResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return OrderResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            null => ListRecords\Tab::make('All'),
            'en progression' => ListRecords\Tab::make()->query(fn ($query) => $query->where('status', 'en progression')),
            'livré' => ListRecords\Tab::make()->query(fn ($query) => $query->where('status', 'livré')),
            'annulé' => ListRecords\Tab::make()->query(fn ($query) => $query->where('status', 'annulé')),
        ];
    }
}
