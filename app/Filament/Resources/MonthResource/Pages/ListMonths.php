<?php

namespace App\Filament\Resources\MonthResource\Pages;

use App\Filament\Resources\MonthResource;
use Filament\Resources\Pages\ListRecords;

class ListMonths extends ListRecords
{
    protected static string $resource = MonthResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
