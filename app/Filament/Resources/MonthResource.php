<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Month;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\MonthResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\MonthResource\RelationManagers;

/**
 * Ressource Filament pour gérer les rapports mensuels
 */
class MonthResource extends Resource
{
    protected static ?string $model = Month::class;

    protected static ?string $slug = 'rapport/mois';

    protected static ?string $recordTitleAttribute = 'month';

    protected static ?string $modelLabel = 'Mensuel';

    protected static ?string $navigationGroup = 'Rapports';

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 2;

    /**
     * Définit le formulaire de création/édition des rapports mensuels
     *
     * @param Form $form Le formulaire à configurer
     * @return Form Le formulaire configuré
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('year')
                    ->disabled(),
                TextInput::make('month')
                    ->disabled(),
                TextInput::make('count')
                    ->disabled(),
            ]);
    }

    /**
     * Définit la table de liste des rapports mensuels
     *
     * @param Table $table La table à configurer
     * @return Table La table configurée
     */
    public static function table(Table $table): Table
    {
        // Filtre pour n'afficher que les mois avec des livraisons
        $table->query(
            fn (): Builder => Month::query()
                ->where('count', '>', '0')
                ->orderByDesc('year')
                ->orderByDesc('month_number')
        );

        return $table
            ->columns([
                // Colonne du mois avec l'année en description
                TextColumn::make('month')
                    ->label('Mois')
                    ->description(fn (Month $record): string => $record->year),
                // Colonne du nombre de livraisons
                TextColumn::make('count')
                    ->label('Nombre de livraisons du mois')
                    ->icon('heroicon-s-document-minus'),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Action pour imprimer le rapport mensuel
                Tables\Actions\Action::make('Imprimer')
                    ->url(fn (Month $record): string => route('order.month.print', [
                        'month' => $record->month_number,
                        'year' => $record->year,
                    ]))
                    ->button()
                    ->color('danger')
                    ->icon('heroicon-o-document-arrow-up')
                    ->iconPosition(IconPosition::After),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Définit les relations disponibles pour cette ressource
     *
     * @return array Les relations configurées
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Définit les pages disponibles pour cette ressource
     *
     * @return array Les pages configurées
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonths::route('/'),
            'create' => Pages\CreateMonth::route('/create'),
            'edit' => Pages\EditMonth::route('/{record}/edit'),
        ];
    }
}
