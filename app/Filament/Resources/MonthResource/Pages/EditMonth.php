<?php

namespace App\Filament\Resources\MonthResource\Pages;

use App\Filament\Resources\MonthResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMonth extends EditRecord
{
    protected static string $resource = MonthResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
