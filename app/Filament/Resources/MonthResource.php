<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Month;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\MonthResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MonthResource\RelationManagers;

class MonthResource extends Resource
{
    protected static ?string $model = Month::class;

    protected static ?string $slug = 'rapport/mois';

    protected static ?string $recordTitleAttribute = 'month';

    protected static ?string $modelLabel = 'Mois';

    protected static ?string $navigationGroup = 'Rapports';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('year')
                    ->disabled(),
                TextInput::make('month')
                    ->disabled(),
                TextInput::make('start_date')
                    ->disabled(),
                TextInput::make('end_date')
                    ->disabled(),
                TextInput::make('count')
                    ->disabled(),
                TextInput::make('report_status')
                    ->disabled(),
                TextInput::make('report_date')
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $table->query(
            fn (): Builder => Month::query()
                ->where('count', '>', '0')
        );

        return $table
            ->columns([
                TextColumn::make('month')
                    ->label('Mois')
                    ->description(fn (Month $record): string => $record->year),
                TextColumn::make('count')
                    ->label('Nombre de livraisons du mois')
                    ->icon('heroicon-s-document-minus'),
                IconColumn::make('report_status')
                    ->label('Statut du rapport')
                    ->boolean(),
                TextColumn::make('report_created_at')
                    ->label('Date de création du rapport')
                    ->dateTime('d/M/Y'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('Imprimer')
                        ->icon('heroicon-o-document-arrow-up')
                        ->url(fn (Month $record): string => route('order.month.print', [
                            'month' => $record->month_number,
                            'year' => $record->year,
                        ])),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrdersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonths::route('/'),
            'create' => Pages\CreateMonth::route('/create'),
            'edit' => Pages\EditMonth::route('/{record}/edit'),
        ];
    }
}
