<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Shop\Order;
use Filament\Tables\Table;
use App\Filament\Resources\Shop\OrderResource;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Dernières commandes';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(OrderResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('published_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('Numéro de commande')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Date de commande')
                    ->dateTime('d/M/Y')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->color(fn (string $state): string => match ($state) {
                        'en progression' => 'warning',
                        'livré' => 'success',
                        'annulé' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('url')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('report_delivered')
                    ->label('Rapport envoyé')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('report_delivered_date')
                    ->label('Rapport envoyé le')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->url(fn (Order $record): string => OrderResource::getUrl('edit', ['record' => $record]))
                    ->label('Ouvrir'),
            ]);
    }
}
